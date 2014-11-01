<?php namespace TippingCanoe\Phperclip\Storage;

use Symfony\Component\HttpFoundation\File\File;
use TippingCanoe\Phperclip\FileNameGenerator;
use TippingCanoe\Phperclip\Model\File as FileModel;
use TippingCanoe\Phperclip\MimeResolver;
use Aws\S3\S3Client;
use Aws\S3\Enum\CannedAcl;

class S3 extends BaseDriver {


	/**
	 * @var \Aws\S3\S3Client
	 */
	protected $s3;

	/**
	 * @var string
	 */
	protected $awsBucket;

	/**
	 * @param MimeResolver $mimeResolver
	 * @param FileNameGenerator $nameGenerator
	 * @param S3Client $s3Client
	 */

	public function __construct(MimeResolver $mimeResolver, FileNameGenerator $nameGenerator, S3Client $s3Client) {
		$this->s3 = $s3Client;
		$this->nameGenerator = $nameGenerator;
		$this->mimeResolver = $mimeResolver;
	}

	/**
	 * @param string $bucket
	 */
	public function setBucket($bucket) {

		$this->awsBucket = $bucket;
	}

	/**
	 * Saves a file.
	 *
	 * Exceptions can provide extended error information and will abort the save process.
	 * @param File $file
	 * @param FileModel $fileModel
	 */
	public function saveFile(File $file, FileModel $fileModel) {

		// Upload a file.
		$this->s3->putObject(array(
			'Bucket' => $this->awsBucket,
			'Key' => $this->nameGenerator->fileName($fileModel),
			'SourceFile' => $file->getRealPath(),
			'ACL' => CannedAcl::PRIVATE_ACCESS,
		));
	}

	/**
	 * Returns the public URI for a file.
	 *
	 * @param FileModel $fileModel
	 * @return string
	 */
	public function getPublicUri(FileModel $fileModel) {

		// Get a timed url
		return $this->s3->getObjectUrl($this->awsBucket, $this->nameGenerator->fileName($fileModel), '+10 minutes');
	}

	/**
	 * Asks the driver if it has a particular file.
	 *
	 * @param FileModel $fileModel
	 * @return bool
	 */
	public function has(FileModel $fileModel) {

		// Check if file exists
		return $this->s3->doesObjectExist(
			$this->awsBucket,
			$this->nameGenerator->fileName($fileModel));
	}

	/**
	 * Tells the driver to delete a file.
	 *
	 * Deleting must at least ensure that afterwards, any call to has() returns false.
	 *
	 * @param FileModel $fileModel
	 */
	public function delete(FileModel $fileModel) {

		// Delete a file.
		$this->s3->deleteObject(array(
			'Bucket' => $this->awsBucket,
			'Key' => $this->nameGenerator->fileName($fileModel),
		));
	}

	/**
	 * Tells the driver to prepare a copy of the original file locally.
	 *
	 * @param FileModel $fileModel
	 * @return File
	 */
	public function tempOriginal(FileModel $fileModel) {

		// Recreate original filename
		$tempOriginalPath = tempnam(sys_get_temp_dir(), null);

		$originalPath = $this->nameGenerator->fileName($fileModel);

		// Download file
		$this->s3->getObject(array(
			'Bucket' => $this->awsBucket,
			'Key' => $originalPath,
			'SaveAs' => $tempOriginalPath
		));

		return new File($tempOriginalPath);
	}

} 