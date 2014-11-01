<?php

namespace spec\TippingCanoe\Phperclip\Processes;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\File;
use TippingCanoe\Phperclip\Model\File as FileModel;
use TippingCanoe\Phperclip\Processes\FileProcessorAdapter;

class ProcessManagerSpec extends ObjectBehavior {

	function let() {

		$processors = [];

		$processors[] = new MockProcessorAdapter();

		$this->beConstructedWith($processors);
	}

	function it_is_initializable() {

		$this->shouldHaveType('TippingCanoe\Phperclip\Processes\ProcessManager');
	}

	function it_can_dispatch_processors_for_file_actions(File $file) {

		$file->getMimeType()->willReturn('image/png');

		$this->dispatch($file, 'onSave')->shouldReturn(null);

	}

	function it_can_modify_files_through_processors(FileModel $fileModel) {

		$fileModel->getMimeType()->willReturn('text/plain');

		$fileModel->setAttribute('mime_type', 'image/jpeg')->shouldBeCalled();

		$this->dispatch($fileModel, 'onDelete')->shouldReturn($fileModel);

	}
}

class MockProcessorAdapter extends FileProcessorAdapter {

	protected $mimeTypes = ['image/png', 'text/plain'];

	public function onSave(File $file) {

		return false;
	}

	public function onDelete(FileModel $fileModel) {

		$fileModel->setAttribute('mime_type', 'image/jpeg');

		return $fileModel;
	}


}