<?php namespace TippingCanoe\Phperclip\Storage;

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
	 */
	public function saveFile(File $file, FileModel $fileModel);

	/**
	 * Returns the public URI for a file by a specific configuration.
	 *
	 * @param FileModel $fileModel
	 * @return string
	 */
	public function getPublicUri(FileModel $fileModel);

	/**
	 * Asks the driver if it has a particular file.
	 *
	 * @param FileModel $fileModel
	 * @return bool
	 */
	public function has(FileModel $fileModel);

	/**
	 * Tells the driver to delete a file.
	 *
	 * Deleting must at least ensure that afterwards, any call to has() returns false.
	 *
	 * @param FileModel $fileModel
	 * @return
	 */
	public function delete(FileModel $fileModel);

	/**
	 * Tells the driver to prepare a copy of the original file locally.
	 *
	 * @param FileModel $fileModel
	 * @return File
	 */
	public function tempOriginal(FileModel $fileModel);

}