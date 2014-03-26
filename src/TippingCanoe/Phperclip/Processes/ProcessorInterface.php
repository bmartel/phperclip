<?php

namespace TippingCanoe\Phperclip\Processes;


interface ProcessorInterface {

	public function register();

	public function process();
} 