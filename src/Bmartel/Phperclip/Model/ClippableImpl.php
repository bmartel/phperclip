<?php namespace Bmartel\Phperclip\Model;

trait ClippableImpl {

	public function clippedFiles() {

		return $this->morphMany('Bmartel\Phperclip\Model\File', 'clippable');
	}
} 