<?php namespace TippingCanoe\Phperclip\Processes;

use Illuminate\Support\Contracts\MessageProviderInterface;
use Illuminate\Support\MessageBag;
use Symfony\Component\HttpFoundation\File\File;
use TippingCanoe\Phperclip\Model\File as FileModel;

class FileProcessorAdapter implements FileProcessorInterface, MessageProviderInterface {

	protected static $messages = [];

	protected static $errors = [];

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

	/**
	 * Collect messages retrieved from file processors.
	 *
	 * @param $messages
	 * @param bool $error
	 */
	protected function addMessageResponse($messages, $error = false) {

		if (is_string($messages)) {
			$messages = [$messages];
		} elseif ($messages instanceof MessageProviderInterface) {
			$messages = $messages->getMessageBag()->getMessages();
		} else {
			return null; // We don't have anything to add in this case.
		}

		if ($error) {
			static::$errors = array_merge(static::$errors, $messages);
		} else {
			static::$messages = array_merge(static::$messages, $messages);
		}
	}

	/**
	 * The error messages.
	 *
	 * @return MessageBag
	 */
	public function errors() {

		return new MessageBag(static::$errors);
	}

	/**
	 * The non-error messages.
	 *
	 * @return MessageBag
	 */
	public function messages() {

		return new MessageBag(static::$messages);
	}

	/**
	 * Get the messages for the instance.
	 *
	 * @return \Illuminate\Support\MessageBag
	 */
	public function getMessageBag() {

		$messageBag = new MessageBag(static::$messages);

		return $messageBag->merge(static::$errors);
	}

	/**
	 * Check if the file processor has messages.
	 *
	 * @return bool
	 */
	public function hasMessages() {

		return !empty(static::$messages);
	}

	/**
	 * Check if the file processor has error messages.
	 *
	 * @return bool
	 */
	public function hasErrors() {

		return !empty(static::$errors);
	}
}