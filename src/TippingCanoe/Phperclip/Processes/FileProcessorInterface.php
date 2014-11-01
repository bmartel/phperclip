<?php namespace TippingCanoe\Phperclip\Processes;

use Symfony\Component\HttpFoundation\File\File;
use \TippingCanoe\Phperclip\Model\File as FileModel;

interface FileProcessorInterface {

	/**
	 * Registers this Processor for use with the file mime types specified.
	 * Must return an array of string mime types.
	 *
	 * @return array $mimeTypes
	 */
	public function registeredMimes();

	/**
	 * A method to hook into the file save process. Allows intervention of the save operation by returning a false-type from
	 * this method.
	 *
	 * @param File $file
	 * @return null|bool|\Symfony\Component\HttpFoundation\File\File
	 */
	public function onSave(File $file);

	/**
	 * A method to hook into the file delete process. Allows intervention of the delete operation by returning a false-type from
	 * this method.
	 *
	 * @param FileModel $fileModel
	 * @return null|bool|\TippingCanoe\Phperclip\Model\File
	 */
	public function onDelete(FileModel $fileModel);

	/**
	 * A method to hook into the file move process. Allows intervention of the move operation by returning a false-type from
	 * this method.
	 *
	 * @param FileModel $fileModel
	 * @return null|bool|\TippingCanoe\Phperclip\Model\File
	 */
	public function onMove(FileModel $fileModel);

}