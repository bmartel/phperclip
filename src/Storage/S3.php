<?php namespace Bmartel\Phperclip\Storage;

use Symfony\Component\HttpFoundation\File\File;
use Bmartel\Phperclip\Contracts\Driver;
use Bmartel\Phperclip\FileNameGenerator;
use Bmartel\Phperclip\Model\File as FileModel;
use Bmartel\Phperclip\MimeResolver;
use Aws\S3\S3Client;
use Aws\S3\Enum\CannedAcl;

class S3 extends Base {


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
		parent::__construct($mimeResolver, $nameGenerator);
		$this->s3 = $s3Client;
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
	 *
	 * @param File $file
	 * @param FileModel $fileModel
	 * @param array $options
	 */
	public function saveFile(File $file, FileModel $fileModel, array $options = []) {

		// Upload a file.
		$this->s3->putObject(array(
			'Bucket' => $this->awsBucket,
			'Key' => $this->nameGenerator->fileName($fileModel, $options),
			'SourceFile' => $file->getRealPath(),
			'ACL' => CannedAcl::PRIVATE_ACCESS,
		));
	}

	/**
	 * Returns the public URI for a file.
	 *
	 * @param FileModel $fileModel
	 * @param array $options
	 * @return string
	 */
	public function getPublicUri(FileModel $fileModel, array $options = []) {

		// Get a timed url
		return $this->s3->getObjectUrl($this->awsBucket, $this->nameGenerator->fileName($fileModel, $options), '+10 minutes');
	}

	/**
	 * Asks the driver if it has a particular file.
	 *
	 * @param FileModel $fileModel
	 * @param array $options
	 * @return bool
	 */
	public function has(FileModel $fileModel, array $options = []) {

		// Check if file exists
		return $this->s3->doesObjectExist(
			$this->awsBucket,
			$this->nameGenerator->fileName($fileModel, $options));
	}

	/**
	 * Tells the driver to delete a file.
	 *
	 * Deleting must at least ensure that afterwards, any call to has() returns false.
	 *
	 * @param FileModel $fileModel
	 * @param array $options
	 */
	public function delete(FileModel $fileModel, array $options = []) {

		// Delete a file.
		$this->s3->deleteObject(array(
			'Bucket' => $this->awsBucket,
			'Key' => $this->nameGenerator->fileName($fileModel, $options),
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