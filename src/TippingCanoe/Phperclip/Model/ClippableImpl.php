<?php


namespace TippingCanoe\Phperclip\Model;


trait ClippableImpl {

	public function phperclip_files() {

		return $this->morphMany('TippingCanoe\Phperclip\Model\File', 'clippable');
	}
} 