<?php

namespace TippingCanoe\Phperclip\Processes;


use Symfony\Component\HttpFoundation\File\File;

class ProcessManager {

	/**
	 * @var \TippingCanoe\Phperclip\Processes\Processor[]
	 */
	protected $processors;


	public function __construct(array $processors) {

		$this->processors = $processors;
	}

	/**
	 * Dispatches the correct processors for the incoming mimetype
	 *
	 * @param $mimeType
	 * @return \TippingCanoe\Phperclip\Processes\Processor[]
	 */
	public function dispatch(File &$file, $action = null) {

		$result = true;

		foreach ($this->processors as $processor) {

			if ($this->hasProcessFor($file->getMimeType(), $processor)) {

				// Allow additional pre/post methods to be called
				if (method_exists($processor, $action)) {
					$result &= call_user_func($processor->$action, $file);
				} else {
					// Call main process method
					$result &= $processor->process($file);
				}

				if (!$result) {
					return $result;
				}
			}

		}

		return $result;
	}

	/**
	 * Check if there are processors available for the mimetypes incoming.
	 *
	 * @param $mimeType
	 * @return bool
	 */
	protected function hasProcessFor($mimeType, $processor) {

		$mimeType = is_array($mimeType) ? $mimeType : func_get_args();

		return count(array_intersect_key(array_flip($mimeType), $processor)) === count($mimeType);
	}
} 