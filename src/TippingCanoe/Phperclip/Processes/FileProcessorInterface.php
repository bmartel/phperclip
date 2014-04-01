<?php namespace TippingCanoe\Phperclip\Processes;

use Symfony\Component\HttpFoundation\File\File;

interface FileProcessorInterface {

	/**
	 * Registers this Processor for use with the file mime types specified.
	 * Must return an array of string mime types.
	 *
	 * @return array $mimeTypes
	 */
	public function registeredMimes();

	/**
	 * A method to hook into the file save process. Allows intervention of the save operation by returning false from
	 * this method.
	 *
	 * @return bool
	 */
	public function onSave(File &$file);

	/**
	 * A method to hook into the file delete process. Allows intervention of the delete operation by returning false from
	 * this method.
	 *
	 * @return bool
	 */
	public function onDelete(\TippingCanoe\Phperclip\Model\File &$fileModel);

	/**
	 * A method to hook into the file move process. Allows intervention of the move operation by returning false from
	 * this method.
	 *
	 * @return bool
	 */
	public function onMove(\TippingCanoe\Phperclip\Model\File &$fileModel);

}