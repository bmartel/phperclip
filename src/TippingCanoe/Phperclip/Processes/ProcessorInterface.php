<?php

namespace TippingCanoe\Phperclip\Processes;


use Symfony\Component\HttpFoundation\File\File;

interface ProcessorInterface {

	/**
	 * Registers this Processor for use with the file mime types specified.
	 * Must return an array of string mime types.
	 *
	 * @return array $mimeTypes
	 */
	public function register();

	/**
	 * Performs the main action which should take place on the File.
	 * Return false to have the Service abort the operation.
	 *
	 * @return bool
	 */
	public function process(File &$file, $currentServiceMethod = null);

} 