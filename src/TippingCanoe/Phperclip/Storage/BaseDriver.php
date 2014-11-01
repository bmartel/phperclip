<?php namespace TippingCanoe\Phperclip\Storage;

use TippingCanoe\Phperclip\Contracts\FileNameGenerator;
use TippingCanoe\Phperclip\MimeResolver;

abstract class BaseDriver implements Driver {

	protected $nameGenerator;

	/**
	 * @var \TippingCanoe\Phperclip\MimeResolver
	 */
	protected $mimeResolver;


	public function __construct(MimeResolver $mimeResolver, FileNameGenerator $nameGenerator) {

		$this->nameGenerator = $nameGenerator;
		$this->mimeResolver = $mimeResolver;
	}

}