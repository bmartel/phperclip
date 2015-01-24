<?php

namespace TippingCanoe\Phperclip;

use TippingCanoe\Phperclip\Contracts\FileNameGenerator;
use Illuminate\Contracts\Filesystem\Factory as FileDriver;
use Illuminate\Contracts\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use TippingCanoe\Phperclip\Contracts\StorageDriver;
use TippingCanoe\Phperclip\Model\File as FileModel;

class Storage implements StorageDriver {

	/**
	 * @var FileDriver
	 */
	protected $fileDriver;

	/**
	 * @var Filesystem
	 */
	protected $filesystem;

	/**
	 * @var FileNameGenerator
	 */
	protected $nameGenerator;

	/**
	 * @var MimeResolver
	 */
	protected $mimeResolver;

	/**
	 * @var string
	 */
	protected $publicPrefix;

	/**
	 * @var string
	 */
	protected $root;

	/**
	 * @param FileDriver $fileDriver
	 * @param Filesystem $filesystem
	 * @param MimeResolver $mimeResolver
	 * @param FileNameGenerator $nameGenerator
	 */
	public function __construct(
		FileDriver $fileDriver,
		Filesystem $filesystem,
		MimeResolver $mimeResolver,
		FileNameGenerator $nameGenerator
	) {

		$this->fileDriver = $fileDriver;
		$this->filesystem = $filesystem;
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
	 * @return string
	 */
	public function getRoot() {

		return $this->root;
	}

	/**
	 * @param string $prefix
	 */
	public function setPublicPrefix($prefix) {

		$this->publicPrefix = $prefix;
	}

	/**
	 * @return string
	 */
	public function getPublicPrefix() {

		return $this->publicPrefix;
	}

	/**
	 * This is the name of the array key which to create file variations from its corresponding values.
	 *
	 * @return string
	 */
	public function getModificationKey() {

		return $this->nameGenerator->getFileModificationKey();
	}

	/**
	 * Saves a file.
	 *
	 * Exceptions can provide extended error information and will abort the save process.
	 *
	 * @param File $file
	 * @param FileModel $fileModel
	 * @param array $options
	 * @return
	 */
	public function saveFile(File $file, FileModel $fileModel, array $options = []) {

		$this->filesystem->move($this->getRoot(), $this->nameGenerator->fileName($fileModel, $options));
	}

	/**
	 * Returns the public URI for a file by a specific configuration.
	 *
	 * @param FileModel $fileModel
	 * @param array $options
	 * @return string
	 */
	public function getPublicUri(FileModel $fileModel, array $options = []) {

		return sprintf('%s/%s',
			$this->getPublicPrefix(),
			$this->nameGenerator->fileName($fileModel, $options)
		);
	}

	/**
	 * Asks the driver if it has a particular file.
	 *
	 * @param FileModel $fileModel
	 * @param array $options
	 * @return bool
	 */
	public function has(FileModel $fileModel, array $options = []) {

		return $this->filesystem->exists($this->generateFilePath($fileModel, $options));
	}

	/**
	 * Tells the driver to delete a file.
	 *
	 * Deleting must at least ensure that afterwards, any call to has() returns false.
	 *
	 * @param FileModel $fileModel
	 * @param array $options
	 * @return
	 */
	public function delete(FileModel $fileModel, array $options = []) {

		// If we're deleting a derived file.
		if ($options) {

			$this->filesystem->delete($this->generateFilePath($fileModel, $options));
		} // This is the original image, so delete any derivations that may exist as well.
		else {

			 $directory = sprintf('%s/%s',
				$this->root,
				$fileModel->getKey()
			);

			$files = $this->filesystem->files($directory);

			$this->filesystem->delete($files);
		}
	}

	/**
	 * Tells the driver to prepare a copy of the original file locally.
	 *
	 * @param FileModel $fileModel
	 * @return File
	 */
	public function tempOriginal(FileModel $fileModel) {

		$originalPath = $this->generateFilePath($fileModel);

		$tempOriginalPath = tempnam(sys_get_temp_dir(), null);

		$this->filesystem->copy($originalPath, $tempOriginalPath);

		return new File($tempOriginalPath);
	}

	/**
	 * Allows switching of active filesystem drivers.
	 *
	 * @param $driver
	 * @return mixed
	 */
	public function useDriver($driver = 'local') {

		$this->filesystem = $this->fileDriver->disk($driver);

		return $this;
	}

	/**
	 * @param $fileModel
	 * @param $options
	 * @return string
	 */
	protected function generateFilePath($fileModel, $options = []) {

		return sprintf('%s/%s', $this->root, $this->nameGenerator->fileName($fileModel, $options));
	}

}