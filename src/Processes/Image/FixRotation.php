<?php namespace TippingCanoe\Phperclip\Processes\Image;


use Intervention\Image\ImageManagerStatic;
use Symfony\Component\HttpFoundation\File\File;
use TippingCanoe\Phperclip\Contracts\Filter;

class FixRotation implements Filter {

	/**
	 * @var ImageManagerStatic
	 */
	private $intervention;

	public function __construct(ImageManagerStatic $intervention) {

		$this->intervention = $intervention;
	}

	public function run(File $file) {
		$image = $this->intervention->make($file->getRealPath());

		$image->orientate();
		$image->save(null, 100);
		$image->destroy();
	}
}