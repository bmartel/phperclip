<?php namespace TippingCanoe\Phperclip\Processes;

use Illuminate\Session\SessionManager;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use Symfony\Component\HttpFoundation\File\File;
use TippingCanoe\Phperclip\Model\File as FileModel;

class ProcessManager {

	/**
	 * @var \TippingCanoe\Phperclip\Processes\FileProcessorAdapter[]
	 */
	protected $processors;

	/**
	 * @var \Illuminate\Session\Store
	 */
	private $session;

	public function __construct(SessionManager $session, array $processors = null) {

		$this->processors = $processors;
		$this->session = $session->driver();
	}

	/**
	 * @param \Symfony\Component\HttpFoundation\File\File|\TippingCanoe\Phperclip\Model\File $file
	 * @param $action
	 * @param array $options
	 * @return bool
	 */
	public function dispatch($file, $action, array $options = []) {

		// Do not process if the file is not an expected file type object.
		if (!($this->validFileObject($file))) {
			return null;
		}

		$mimeType = $file->getMimeType();

		if ($processors = $this->getProcessorsFor($mimeType)) {
			foreach ($processors as $processor) {

				// Call the processor method
				if (method_exists($processor, $action)) {
					$file = $processor->$action($file, $options);
				}

				// If we return anything but the file here, stop the processing.
				if (!($this->validFileObject($file))) {
					$this->dispatchMessagesForFile();
					return null;
				}
			}
		}

		$this->dispatchMessagesForFile();

		return $file;
	}

	/**
	 * Retrieve all processors which are registered to act on the mimetype.
	 *
	 * @param $mimeType
	 * @return null|array|\TippingCanoe\Phperclip\Processes\FileProcessorAdapter[]
	 */
	protected function getProcessorsFor($mimeType) {
		if(empty($this->processors)) return null;

		return array_filter($this->processors, function($processor) use($mimeType){
			return in_array($mimeType, $processor->registeredMimes());
		});
	}

	/**
	 * @param $file
	 * @return bool
	 */
	private function validFileObject($file) {

		return $file instanceof File || $file instanceof FileModel;
	}

	/**
	 * Flashes the messages to the session.
	 */
	private function dispatchMessagesForFile() {

		$processor = new FileProcessorAdapter();

		if($processor->hasErrors()){
			$this->addErrors($processor->errors());
		}

		if($processor->hasMessages()) {
			$this->addMessages($processor->messages());
		}
	}

	/**
	 * Add flash error messages
	 *
	 * @param MessageBag $errors
	 */
	private function addErrors(MessageBag $errors) {
		$this->session->flash(
			'errors',
			$this->session->get('errors', new ViewErrorBag)->put('file',$errors)
		);
	}

	/**
	 * Add flash messages
	 *
	 * @param MessageBag $messages
	 */
	private function addMessages(MessageBag $messages) {
		$this->session->flash(
			'messages',
			$this->session->get('messages', new MessageBag)->merge('file',$messages)
		);
	}
}