<?php

namespace spec\TippingCanoe\Phperclip;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use TippingCanoe\Phperclip\MimeResolver;
use TippingCanoe\Phperclip\Model\File as FileModel;

class FileNameGeneratorSpec extends ObjectBehavior {

	public function let(MimeResolver $resolver) {

		$this->beConstructedWith($resolver);
	}

	public function it_is_initializable() {

		$this->shouldHaveType('TippingCanoe\Phperclip\FileNameGenerator');
	}

	public function it_generates_a_name_for_a_file_according_to_its_attributes_and_options(FileModel $fileModel) {

		$fileModel->getMimeType()->willReturn('image/png');
		$fileModel->getKey()->willReturn(999);
		$fileModel->setAttribute('slot', 'profile');

		$options = [
			'modifications' => [
				'shrink' => 'run shrink filter here',
				'blur' => 'run blur filter here'
			],
			'validators' => [
				'image_size' => 'run image size validator'
			]
		];

		$fileName = $this->fileName($fileModel, $options);

		$mixedOptions = [
			'validators' => [
				'file_size' => 'run file size validator'
			],
			'modifications' => [
				'blur' => 'run blur filter here',
				'shrink' => 'run shrink filter here'
			]
		];

		$this->fileName($fileModel, $mixedOptions)->shouldBeEqualTo($fileName);
	}
}
