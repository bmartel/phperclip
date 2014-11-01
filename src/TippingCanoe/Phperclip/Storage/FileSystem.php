<?php namespace TippingCanoe\Phperclip\Storage;

use TippingCanoe\Phperclip\Model\File as FileModel;
use Symfony\Component\HttpFoundation\File\File;

class Filesystem extends BaseDriver {

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
	 * @param FileModel $fileModel
	 * @internal param FileModel $file
	 * @return string
	 */
	public function getPublicUri(FileModel $fileModel) {

		return sprintf('%s/%s',
			$this->getPublicPrefix(),
			$this->nameGenerator->fileName($fileModel)
		);
	}

	/**
	 * Saves a File.
	 *
	 * Exceptions can provide extended error information and will abort the save process.
	 * @param File $file
	 * @param FileModel $fileModel
	 */
	public function saveFile(File $file, FileModel $fileModel) {

		$file->move($this->root, $this->nameGenerator->fileName($fileModel));
	}

	/**
	 * @param FileModel $fileModel
	 * @return bool
	 */
	public function has(FileModel $fileModel) {

		return file_exists($this->generateFilePath($fileModel));
	}

	/**
	 * Deletes a file.
	 *
	 * @param FileModel $fileModel
	 */
	public function delete(FileModel $fileModel) {

		$pattern = sprintf('%s/%s-*.%s',
			$this->root,
			$fileModel->getKey(),
			$this->mimeResolver->getExtension($fileModel->getMimeType())
		);

		foreach (glob($pattern) as $filePath) {
			unlink($filePath);
		}

	}

	/**
	 * Tells the driver to prepare a copy of the original image locally.
	 *
	 * @param FileModel $fileModel
	 * @return File
	 */
	public function tempOriginal(FileModel $fileModel) {

		$originalPath = $this->generateFilePath($fileModel);

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
	 * @param FileModel $fileModel
	 * @return string
	 */
	protected function generateFilePath(FileModel $fileModel) {

		return sprintf('%s/%s', $this->root, $this->nameGenerator->fileName($fileModel));
	}

}