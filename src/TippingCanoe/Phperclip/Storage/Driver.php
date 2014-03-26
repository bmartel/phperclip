<?php

namespace TippingCanoe\Phperclip\Storage;


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
	 * @param \Symfony\Component\HttpFoundation\File\File $file
	 * @param \TippingCanoe\Phperclip\Model\File $fileModel
	 */
	public function saveFile(File $file, FileModel $fileModel);

	/**
	 * Returns the public URI for a file by a specific configuration.
	 *
	 * @param \TippingCanoe\Phperclip\Model\File $fileModel
	 * @return string
	 */
	public function getPublicUri(FileModel $fileModel);

	/**
	 * Asks the driver if it has a particular file.
	 *
	 * @param \TippingCanoe\Phperclip\Model\File $file
	 * @return bool
	 */
	public function has(FileModel $fileModel);

	/**
	 * Tells the driver to delete a file.
	 *
	 * Deleting must at least ensure that afterwards, any call to has() returns false.
	 *
	 * @param \TippingCanoe\Phperclip\Model\File $file
	 */
	public function delete(FileModel $fileModel);

	/**
	 * Tells the driver to prepare a copy of the original file locally.
	 *
	 * @param \TippingCanoe\Phperclip\Model\File $fileModel
	 * @return \Symfony\Component\HttpFoundation\File\File
	 */
	public function tempOriginal(FileModel $fileModel);

}