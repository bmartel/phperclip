<?php namespace TippingCanoe\Phperclip\Processes;

use Symfony\Component\HttpFoundation\File\File;
use TippingCanoe\Phperclip\Model\File as FileModel;

class ProcessManager {

	/**
	 * @var \TippingCanoe\Phperclip\Processes\FileProcessor[]
	 */
	protected $processors;


	public function __construct(array $processors = null) {

		$this->processors = $processors;
	}

	/**
	 * Dispatches the correct processors for the requesting mimetype
	 *
	 * @param $mimeType
	 * @return \TippingCanoe\Phperclip\Processes\FileProcessor[]
	 */
	public function dispatch($file, $action) {

		$result = true;

		if (empty($this->processors) === false) {
			foreach ($this->processors as $processor) {

				$mimeType = null;

				if ($file instanceof File || $file instanceof FileModel) {
					$mimeType = $file->getMimeType();
				}

				if (!$mimeType) {
					return false;
				} // If the file passed in is not one of the expected types, bail.

				if ($this->hasProcessFor($mimeType, $processor->registeredMimes())) {

					// Call the processor method
					if (method_exists($processor, $action)) {
						$result &= $processor->$action($file);
					}

					if (!$result) {
						return (bool) $result;
					}
				}
			}
		}

		return (bool) $result;
	}

	/**
	 * Check if there are processors available for the mimetypes request.
	 *
	 * @param $mimeType
	 * @return bool
	 */
	protected function hasProcessFor($mimeType, $processorMimes) {

		return in_array($mimeType, $processorMimes);
	}
}