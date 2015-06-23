<?php

namespace spec\Bmartel\Phperclip\Processes;

use Illuminate\Session\SessionManager;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\File;
use Bmartel\Phperclip\Model\File as FileModel;
use Bmartel\Phperclip\Processes\FileProcessorAdapter;

class ProcessManagerSpec extends ObjectBehavior {

	function let(SessionManager $session) {

		$processors = [];

		$processors[] = new MockProcessorAdapter();

		$this->beConstructedWith($session, $processors);
	}

	function it_is_initializable() {

		$this->shouldHaveType('Bmartel\Phperclip\Processes\ProcessManager');
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

	public function onSave(File $file, array $options = []) {

		return false;
	}

	public function onDelete(FileModel $fileModel, array $options = []) {

		$fileModel->setAttribute('mime_type', 'image/jpeg');

		return $fileModel;
	}

}