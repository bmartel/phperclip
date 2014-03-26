<?php

namespace TippingCanoe\Phperclip\Storage;


use TippingCanoe\Phperclip\Model\File as FileModel;
use TippingCanoe\Phperclip\MimeResolver;
use Symfony\Component\HttpFoundation\File\File;

class Filesystem implements Driver {

	/** @var string */
	protected $publicPrefix;

	/** @var string */
	protected $root;

	/**
	 * @param $path
	 */
	public function setRoot($path) {
		$this->root = $path;
	}

	/**
	 * @param string $prefix
	 */
	public function setPublicPrefix($prefix) {
		$this->publicPrefix = $prefix;
	}

	//
	// Public Interface Implementation
	//

	/**
	 * @param \TippingCanoe\Phperclip\Model\File $file
	 * @param array $filters
	 * @return string
	 */
	public function getPublicUri(FileModel $fileModel) {
		return sprintf('%s/%s',
			$this->getPublicPrefix(),
			$this->generateFileName($fileModel)
		);
	}

	/**
	 * Saves a File.
	 *
	 * Exceptions can provide extended error information and will abort the save process.
	 *
	 * @param File $file
	 * @param File $image
	 * @param array $filters
	 */
	public function saveFile(File $file, FileModel $fileModel) {
		$file->move($this->root, $this->generateFileName($fileModel));
	}

	/**
	 * @param File $image
	 * @param array $filters
	 * @return bool|mixed
	 */
	public function has(FileModel $fileModel) {
		return file_exists($this->generateFilePath($fileModel));
	}

	/**
	 * Deletes an image.
	 *
	 * @param File $image
	 * @param array $filters
	 */
	public function delete(FileModel $fileModel) {

			$pattern = sprintf('%s/%s-*.%s',
				$this->root,
				$fileModel->getKey(),
				MimeResolver::getExtensionForMimeType($fileModel->mime_type)
			);

			foreach(glob($pattern) as $filePath) {
				unlink($filePath);
			}


	}

	/**
	 * Tells the driver to prepare a copy of the original image locally.
	 *
	 * @param File $image
	 * @return File
	 */
	public function tempOriginal(FileModel $fileModel) {

		$originalPath = sprintf('%s/%s-%s.%s',
			$this->root,
			$fileModel->getKey(),
			$this->generateHash($fileModel),
			MimeResolver::getExtensionForMimeType($fileModel->mime_type)
		);

		$tempOriginalPath = tempnam(sys_get_temp_dir(), null);

		copy($originalPath, $tempOriginalPath);

		return new File($tempOriginalPath);

	}

	//
	// Utility Methods
	//

	/**
	 * @return string
	 */
	protected function getPublicPrefix() {
		return $this->publicPrefix;
	}

	/**
	 * Generates a hash based on an image and it's filters.
	 *
	 * @param File $fileModel
	 * @return string
	 */
	protected function generateHash(FileModel $fileModel) {

		$state = [
			'id' => (string)$fileModel->getKey()
		];

		return md5(json_encode($state));

	}

	/**
	 * @param FileModel $fileModel
	 * @return string
	 */
	protected function generateFileName(FileModel $fileModel) {
		return sprintf('%s-%s.%s',
			$fileModel->getKey(),
			$this->generateHash($fileModel),
			MimeResolver::getExtensionForMimeType($fileModel->mime_type)
		);
	}

	/**
	 * @param FileModel $fileModel
	 * @return string
	 */
	protected function generateFilePath(FileModel $fileModel) {
		return sprintf('%s/%s', $this->root, $this->generateFileName($fileModel));
	}

	/**
	 * Utility method to ensure that key signatures always appear in the same order.
	 *
	 * @param array $array
	 * @return array
	 */
	protected function recursiveKeySort(array $array) {

		ksort($array);

		foreach($array as $key => $value)
			if(is_array($value))
				$array[$key] = $this->recursiveKeySort($value);

		return $array;

	}

}