<?php

namespace spec\TippingCanoe\Phperclip\Processes;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\File\File;
use TippingCanoe\Phperclip\Processes\FileProcessor;
use Mockery;

class ProcessManagerSpec extends ObjectBehavior
{
	function let()
	{
		$processors = [];

		$processors[]= new MockProcessor();

		$this->beConstructedWith($processors);
	}

	public function letGo()
	{
		Mockery::close();
	}

    function it_is_initializable()
    {
        $this->shouldHaveType('TippingCanoe\Phperclip\Processes\ProcessManager');
    }

	function it_can_dispatch_processors_for_file_actions(File $file)
	{

		$file->getMimeType()->willReturn('image/png');

		$this->dispatch($file, 'onSave')->shouldReturn(false);

	}

	function it_can_modify_files_through_processors(\TippingCanoe\Phperclip\Model\File $fileModel) {
		$fileModel->getMimeType()->willReturn('text/plain');

		$fileModel->setAttribute('mime_type' , 'image/jpeg')->shouldBeCalled();

		$this->dispatch($fileModel, 'onDelete')->shouldReturn(true);

	}
}

class MockProcessor extends FileProcessor{
	protected $mimeTypes = ['image/png', 'text/plain'];

	public function onSave(File &$file) {
		return false;
	}

	public function onDelete(\TippingCanoe\Phperclip\Model\File &$fileModel) {
		$fileModel->setAttribute('mime_type' , 'image/jpeg');
		return true;
	}


}