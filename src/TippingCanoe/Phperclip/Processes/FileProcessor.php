<?php

namespace TippingCanoe\Phperclip\Processes;


use Symfony\Component\HttpFoundation\File\File;

abstract class FileProcessor implements FileProcessorInterface {

	protected $mimeTypes = [];

	public function registeredMimes() {

		return $this->mimeTypes;
	}

	/**
	 * A method to hook into the file save process. Allows intervention of the save operation by returning false from
	 * this method.
	 *
	 * @return bool
	 */
	public function onSave(File &$file) {
		// TODO: Implement onSave() method.
	}

	/**
	 * A method to hook into the file delete process. Allows intervention of the delete operation by returning false from
	 * this method.
	 *
	 * @return bool
	 */
	public function onDelete(\TippingCanoe\Phperclip\Model\File &$fileModel) {
		// TODO: Implement onDelete() method.
	}

	/**
	 * A method to hook into the file move process. Allows intervention of the move operation by returning false from
	 * this method.
	 *
	 * @return bool
	 */
	public function onMove(\TippingCanoe\Phperclip\Model\File &$fileModel) {
		// TODO: Implement onMove() method.
	}

}