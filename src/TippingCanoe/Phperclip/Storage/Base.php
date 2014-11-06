<?php namespace TippingCanoe\Phperclip\Storage;


use TippingCanoe\Phperclip\Contracts\Driver;
use TippingCanoe\Phperclip\Contracts\FileNameGenerator;
use TippingCanoe\Phperclip\MimeResolver;

abstract class Base implements Driver{

	protected $nameGenerator;

	protected $mimeResolver;


	public function __construct(MimeResolver $mimeResolver, FileNameGenerator $nameGenerator) {

		$this->nameGenerator = $nameGenerator;
		$this->mimeResolver = $mimeResolver;
	}

	/**
	 * This is the name of the array key which to create file variations from its corresponding values.
	 *
	 * @return string
	 */
	public function getModificationKey() {
		return $this->nameGenerator->getFileModificationKey();
	}
}