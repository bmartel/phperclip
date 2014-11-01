<?php namespace TippingCanoe\Phperclip\Processes;

use Symfony\Component\HttpFoundation\File\File;
use TippingCanoe\Phperclip\Model\File as FileModel;

class ProcessManager {

	/**
	 * @var \TippingCanoe\Phperclip\Processes\FileProcessorAdapter[]
	 */
	protected $processors;

	public function __construct(array $processors = null) {

		$this->processors = $processors;
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
					return null;
				}
			}
		}

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
}