<?php namespace TippingCanoe\Phperclip\Model;

interface Clippable {

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\MorphMany
	 */
	public function clippedFiles();
} 