<?php namespace TippingCanoe\Phperclip\Contracts;

use TippingCanoe\Phperclip\Model\File;

interface FileNameGenerator {

	public function fileName(File $file, array $options = []);
} 