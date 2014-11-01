<?php namespace TippingCanoe\Phperclip\Storage;

use TippingCanoe\Phperclip\Model\File as FileModel;
use Symfony\Component\HttpFoundation\File\File;
use TippingCanoe\Phperclip\Contracts\FileNameGenerator;
use TippingCanoe\Phperclip\MimeResolver;

class Filesystem implements Driver {

	/** @var string */
	protected $publicPrefix;

	/** @var string */
	protected $root;

	protected $nameGenerator;

	protected $mimeResolver;


	public function __construct(MimeResolver $mimeResolver, FileNameGenerator $nameGenerator) {

		$this->nameGenerator = $nameGenerator;
		$this->mimeResolver = $mimeResolver;
	}

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
	 * @param array $options
	 * @internal param FileModel $file
	 * @return string
	 */
	public function getPublicUri(FileModel $fileModel, array $options = []) {

		return sprintf('%s/%s',
			$this->getPublicPrefix(),
			$this->nameGenerator->fileName($fileModel, $options)
		);
	}

	/**
	 * Saves a File.
	 *
	 * Exceptions can provide extended error information and will abort the save process.
	 *
	 * @param File $file
	 * @param FileModel $fileModel
	 * @param array $options
	 */
	public function saveFile(File $file, FileModel $fileModel, array $options = []) {

		$file->move($this->root, $this->nameGenerator->fileName($fileModel, $options));
	}

	/**
	 * @param FileModel $fileModel
	 * @param array $options
	 * @return bool
	 */
	public function has(FileModel $fileModel, array $options = []) {

		return file_exists($this->generateFilePath($fileModel, $options));
	}

	/**
	 * Deletes a file.
	 *
	 * @param FileModel $fileModel
	 * @param array $options
	 */
	public function delete(FileModel $fileModel, array $options = []) {

		// If we're deleting a derived file.
		if($options) {
			unlink($this->generateFilePath($fileModel, $options));
		}

		// This is the original image, so delete any derivations that may exist as well.
		else {
			$pattern = sprintf('%s/%s-*.%s',
				$this->root,
				$fileModel->getKey(),
				$this->mimeResolver->getExtension($fileModel->getMimeType())
			);

			foreach (glob($pattern) as $filePath) {
				unlink($filePath);
			}
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
	 * @param array $options
	 * @return string
	 */
	protected function generateFilePath(FileModel $fileModel, array $options = []) {

		return sprintf('%s/%s', $this->root, $this->nameGenerator->fileName($fileModel, $options));
	}

}