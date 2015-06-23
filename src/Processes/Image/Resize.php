<?php
namespace Bmartel\Phperclip\Processes\Image;


use Intervention\Image\ImageManagerStatic;
use Symfony\Component\HttpFoundation\File\File;
use Bmartel\Phperclip\Contracts\Filter;

class Resize implements Filter {

	/**
	 * @var int
	 */
	protected $width;

	/**
	 * @var int
	 */
	protected $height;

	/**
	 * @var bool
	 */
	protected $preserveRatio;

	/**
	 * @var ImageManagerStatic
	 */
	protected $intervention;

	/**
	 * @param ImageManagerStatic $intervention
	 */
	public function __construct(ImageManagerStatic $intervention) {

		$this->intervention = $intervention;
	}

	public function setHeight($height) {

		$this->height = $height;
	}

	public function setWidth($width) {

		$this->width = $width;
	}

	public function setPreserveRatio($preserveRatio) {

		$this->preserveRatio = $preserveRatio;
	}

	public function run(File $file) {

		$image = $this->intervention->make($file->getRealPath());
		$preserveRatio = $this->preserveRatio;

		$image->resize($this->width, $this->height, function ($constraint) use ($preserveRatio) {

			if ($preserveRatio) {
				$constraint->aspectRatio();
			}
		});

		$image->save(null, 100);
		$image->destroy();
	}
}