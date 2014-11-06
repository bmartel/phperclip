<?php namespace TippingCanoe\Phperclip\Contracts;

use TippingCanoe\Phperclip\Model\File;

interface FileNameGenerator {

	/**
	 * Generate the file name based on the file and any options passed in.
	 *
	 * @param File $file
	 * @param array $options
	 * @return mixed
	 */
	public function fileName(File $file, array $options = []);

	/**
	 * This is the name of the array key which to create file variations from its corresponding values.
	 *
	 * @return string
	 */
	public function getFileModificationKey();
} 