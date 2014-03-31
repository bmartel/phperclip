<?php

namespace TippingCanoe\Phperclip\Processes;


abstract class FileProcessor implements FileProcessorInterface {

	protected $mimeTypes = [];

	public function registeredMimes() {

		return $this->mimeTypes;
	}

	 /**
	  * A method to hook into the file save process. Allows intervention of the save by returning false from
	  * this method.
	  *
	  * @return bool
	  */
	 public function onSave() {
		 // TODO: Implement onSave() method.
	 }

	 /**
	  * A method to hook into the file delete process. Allows intervention of the save by returning false from
	  * this method.
	  *
	  * @return bool
	  */
	 public function onDelete() {
		 // TODO: Implement onDelete() method.
	 }

	 /**
	  * A method to hook into the file move process. Allows intervention of the save by returning false from
	  * this method.
	  *
	  * @return bool
	  */
	 public function onMove() {
		 // TODO: Implement onMove() method.
	 }

 }