<?php namespace TippingCanoe\Phperclip\Model;

trait ClippableImpl {

	public function clippedFiles() {

		return $this->morphMany('TippingCanoe\Phperclip\Model\File', 'clippable');
	}
} 