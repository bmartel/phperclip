<?php

namespace TippingCanoe\Phperclip\Processes;


abstract class Processor implements ProcessorInterface {

	protected $mimetypes = [];

	public function register(){
		return $this->mimetypes;
	}

}