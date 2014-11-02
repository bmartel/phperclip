<?php namespace TippingCanoe\Phperclip\Contracts;

use TippingCanoe\Phperclip\Model\File as FileModel;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Interface Driver
 *
 * Represents a class that can be used as a storage driver for files.
 *
 * @package TippingCanoe\Phperclip\Storage
 */
interface Driver {

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
	public function saveFile(File $file, FileModel $fileModel, array $options = []);

	/**
	 * Returns the public URI for a file by a specific configuration.
	 *
	 * @param FileModel $fileModel
	 * @param array $options
	 * @return string
	 */
	public function getPublicUri(FileModel $fileModel, array $options = []);

	/**
	 * Asks the driver if it has a particular file.
	 *
	 * @param FileModel $fileModel
	 * @param array $options
	 * @return bool
	 */
	public function has(FileModel $fileModel, array $options = []);

	/**
	 * Tells the driver to delete a file.
	 *
	 * Deleting must at least ensure that afterwards, any call to has() returns false.
	 *
	 * @param FileModel $fileModel
	 * @param array $options
	 * @return
	 */
	public function delete(FileModel $fileModel, array $options = []);

	/**
	 * Tells the driver to prepare a copy of the original file locally.
	 *
	 * @param FileModel $fileModel
	 * @return File
	 */
	public function tempOriginal(FileModel $fileModel);

}