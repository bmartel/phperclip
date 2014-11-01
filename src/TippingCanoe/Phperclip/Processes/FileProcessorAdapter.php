<?php namespace TippingCanoe\Phperclip\Processes;

use Symfony\Component\HttpFoundation\File\File;
use TippingCanoe\Phperclip\Model\File as FileModel;

abstract class FileProcessorAdapter implements FileProcessorInterface {

	protected $mimeTypes = [];

	public function registeredMimes() {

		return $this->mimeTypes;
	}

	/**
	 * A method to hook into the file save process. Allows intervention of the save operation by returning a false-type from
	 * this method.
	 *
	 * @param File $file
	 * @param array $options
	 * @return null|bool|\Symfony\Component\HttpFoundation\File\File
	 */
	public function onSave(File $file, array $options = []) {
		// TODO: Implement onSave() method.
		return $file;
	}

	/**
	 * A method to hook into the file delete process. Allows intervention of the delete operation by returning a false-type from
	 * this method.
	 *
	 * @param FileModel $fileModel
	 * @param array $options
	 * @return null|bool|\TippingCanoe\Phperclip\Model\File
	 */
	public function onDelete(FileModel $fileModel, array $options = []) {
		// TODO: Implement onDelete() method.
		return $fileModel;
	}

	/**
	 * A method to hook into the file move process. Allows intervention of the move operation by returning a false-type from
	 * this method.
	 *
	 * @param FileModel $fileModel
	 * @param array $options
	 * @return null|bool|\TippingCanoe\Phperclip\Model\File
	 */
	public function onMove(FileModel $fileModel, array $options = []) {
		// TODO: Implement onMove() method.
		return $fileModel;
	}

}