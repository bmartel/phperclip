<?php
namespace TippingCanoe\Phperclip\Contracts;

use Symfony\Component\HttpFoundation\File\File;

interface Filter {

	public function run(File $file);
} 