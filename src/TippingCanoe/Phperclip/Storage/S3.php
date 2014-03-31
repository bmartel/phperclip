<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2014-03-26
 * Time: 10:32 AM
 */

namespace TippingCanoe\Phperclip\Storage;

use Symfony\Component\HttpFoundation\File\File;
use TippingCanoe\Phperclip\Model\File as FileModel;
use TippingCanoe\Phperclip\MimeResolver;
use Aws\S3\S3Client;
use Aws\S3\Enum\CannedAcl;

class S3 implements Driver {


	/**
	 * @var \Aws\S3\S3Client
	 */
	protected $s3;

	/**
	 * @var string
	 */
	protected $awsBucket;

	/**
	 * @var \TippingCanoe\Phperclip\MimeResolver
	 */
	protected $mimeResolver;

	/**
	 * @param S3Client $s3Client
	 */
	public function __construct(
		S3Client $s3Client,
		MimeResolver $mimeResolver
	) {

		$this->s3 = $s3Client;
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
	 *
	 * @param File $file
	 * @param FileModel $fileModel
	 * @param array $filters
	 */
	public function saveFile(File $file, FileModel $fileModel) {

		// Upload a file.
		$this->s3->putObject(array(
			'Bucket' => $this->awsBucket,
			'Key' => $this->generateFileName($fileModel),
			'SourceFile' => $file->getRealPath(),
			'ACL' => CannedAcl::PRIVATE_ACCESS,
		));

	}

	/**
	 * Returns the public URI for a file.
	 *
	 * @param FileModel $fileModel
	 * @param array $filters
	 * @return string
	 */
	public function getPublicUri(FileModel $fileModel) {

		// Get a timed url
		return $this->s3->getObjectUrl($this->awsBucket, $this->generateFileName($fileModel), '+10 minutes');

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
			$this->generateFileName($fileModel));

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
			'Key' => $this->generateFileName($fileModel),
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

		$originalPath = sprintf('%s-%s.%s',
			$fileModel->getKey(),
			$this->generateHash($fileModel),
			$this->mimeResolver->getExtension($fileModel->mime_type)
		);

		// Download file
		$this->s3->getObject(array(
			'Bucket' => $this->awsBucket,
			'Key' => $originalPath,
			'SaveAs' => $tempOriginalPath
		));

		return new File($tempOriginalPath);

	}

	//
	// Utility Methods
	//

	/**
	 * @param FileModel $fileModel
	 * @param array $filters
	 * @return string
	 */
	protected function generateFileName(FileModel $fileModel, array $filters = []) {

		return sprintf('%s-%s.%s',
			$fileModel->getKey(),
			$this->generateHash($fileModel, $filters),
			$this->mimeResolver->getExtension($fileModel->mime_type)
		);

	}

	/**
	 * Generates a hash based on an image and it's filters.
	 *
	 * @param FileModel $fileModel
	 * @param array $filters
	 * @return string
	 */
	protected function generateHash(FileModel $fileModel) {

		$state = [
			'id' => (string) $fileModel->getKey()
		];

		return md5(json_encode($state));

	}

	/**
	 * Utility method to ensure that key signatures always appear in the same order.
	 *
	 * @param array $array
	 * @return array
	 */
	protected function recursiveKeySort(array $array) {

		ksort($array);

		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$array[$key] = $this->recursiveKeySort($value);
			}
		}

		return $array;

	}
} 