<?php

namespace spec\TippingCanoe\Phperclip;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use TippingCanoe\Phperclip\MimeResolver;
use TippingCanoe\Phperclip\Model\File as FileModel;

class FileNameGeneratorSpec extends ObjectBehavior
{
	function let(MimeResolver $resolver) {

		$this->beConstructedWith($resolver);
	}

    function it_is_initializable()
    {
        $this->shouldHaveType('TippingCanoe\Phperclip\FileNameGenerator');
    }

	function it_generates_a_name_for_a_file_according_to_its_attributes_and_options(FileModel $fileModel) {

		$fileModel->getMimeType()->willReturn('image/png');
		$fileModel->getKey()->willReturn(999);
		$fileModel->setAttribute('slot', 'profile');
		$fileModel->getAttribute('slot')->shouldBeCalled();

		$options = [
			'filters' => [
				'shrink'=>'run shrink filter here',
				'blur' => 'run blur filter here'
			],
			'validators' => [
				'size' => 'run size validator'
			]
		];

		$fileName = $this->fileName($fileModel, $options);

		$mixedOptions = [
			'validators' => [
				'size' => 'run size validator'
			],
			'filters' => [
				'blur' => 'run blur filter here',
				'shrink'=>'run shrink filter here'
			]
		];

		$this->fileName($fileModel, $mixedOptions)->shouldBeEqualTo($fileName);
	}
}
