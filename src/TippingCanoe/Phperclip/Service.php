<?php

namespace TippingCanoe\Phperclip;

use Illuminate\Foundation\Application;
use TippingCanoe\Phperclip\Model\File as FileModel;
use TippingCanoe\Phperclip\Model\Clippable;
use TippingCanoe\Phperclip\Repository\File as FileRepository;
use Symfony\Component\HttpFoundation\File\File;

class Service {

	/**
	 * @var \TippingCanoe\Phperclip\Repository\File
	 */
	protected $fileRepository;

	/**
	 * @var MimeResolver
	 */
	protected $mimeResolver;

	/**
	 * @var \TippingCanoe\Phperclip\Storage\Driver[]
	 */
	protected $storageDrivers;

	/**
	 * @var \TippingCanoe\Phperclip\Storage\Driver
	 */
	protected $currentDriver;

	/**
	 * @var \Illuminate\Foundation\Application
	 */
	protected $app;

	/**
	 * @param FileRepository $fileRepository
	 * @param Application $app
	 * @param \TippingCanoe\Phperclip\Storage\Driver[] $storageDrivers
	 * @throws Exception
	 */
	public function __construct(
		FileRepository $fileRepository,
		MimeResolver $mimeResolver,
		Application $app,
		array $storageDrivers
	) {

		$this->fileRepository = $fileRepository;
		$this->mimeResolver = $mimeResolver;
		$this->app = $app;

		if(empty($storageDrivers))
			throw new Exception('You must configure at least one file storage driver for Phperclip to use.');

		$this->storageDrivers = $storageDrivers;
		$this->currentDriver = current($storageDrivers);

	}

	//
	// General Methods
	//

	/**
	 * Select which driver Phperclip uses by default.
	 *
	 * @param $abstract
	 */
	public function useDriver($abstract) {
		$this->currentDriver = $this->storageDrivers[$abstract];
	}

	/**
	 * Simply retrieves a file by id.
	 *
	 * @param int $id
	 * @return Model\File
	 */
	public function getById($id) {
		return $this->fileRepository->getById($id);
	}

	/**
	 * @param $slot
	 * @param Clippable $clippable
	 * @return Model\File
	 */
	public function getBySlot($slot, Clippable $clippable = null) {
		return $this->fileRepository->getBySlot($slot, $clippable);
	}

	/**
	 * @param FileModel $fileModel
	 * @return string
	 */
	public function getPublicUri(FileModel $fileModel) {

		if(!$this->getDriver()->has($fileModel)) {
			$tempOriginal = $this->getDriver()->tempOriginal($fileModel);
			$this->saveFile($tempOriginal, $fileModel);
		}

		return $this->getDriver()->getPublicUri($fileModel);

	}

	/**
	 * Returns an image URI based on the id of the original.
	 *
	 * @param int $id
	 * @param array $filters
	 * @return string
	 */
	public function getPublicUriById($id) {
		return $this->getPublicUri($this->getById($id));
	}

	/**
	 * Returns a File URI based on the slot and clippable.
	 *
	 * @param string $slot
	 * @param Imageable $imageable
	 * @param array $filters
	 * @return string
	 */
	public function getPublicUriBySlot($slot, Clippable $clippable = null) {
		return $this->getPublicUri($clippable->phperclip_files()->inSlot($slot));
	}

	/**
	 * Saves a new file from a file found on the server's filesystem.
	 *
	 * @param File $file
	 * @param Clippable $clippable
	 * @return Image
	 * @throws Exception
	 */
	public function saveFromFile(File $file, Clippable $clippable = null) {


		//Determine if there are any registered processors which know of this file type

		if(!array_key_exists($file->getMimeType(), MimeResolver::getTypes()))
			throw new Exception(sprintf('File type %s not supported', $file->getMimeType()));

		$newFile = $this->createFileRecord($file);

		// Believe it or not, imageables are optional!
		if($clippable)
			$clippable->phperclip_files()->save($newFile);

		$this->saveFile($file, $newFile);

		return $newFile;

	}

	/**
	 * Saves a new local file from a file available via any of the standard PHP supported schemes.
	 *
	 * @param string $uri
	 * @param Clippable $clippable
	 * @return Image
	 */
	public function saveFromUri($uri, Clippable $clippable = null) {

		// Download the file.
		// Use sys_get_temp_dir so that systems-level configs can apply.
		$tempFilePath = tempnam(sys_get_temp_dir(), null);
		file_put_contents($tempFilePath, fopen($uri, 'r'));

		$tempFile = new File($tempFilePath);

		return $this->saveFromFile($tempFile, $clippable);

	}

	/**
	 *
	 * @param FileModel $fileModel
	 */
	public function delete(FileModel $fileModel) {

		$this->getDriver()->delete($fileModel);

		$image->delete();

	}

	/**
	 * @param $id
	 * @param array $filters
	 */
	public function deleteById($id) {
		$this->delete($this->getById($id));
	}

	public function deleteBySlot($slot, Imageable $imageable = null) {
		$this->delete($this->getBySlot($slot, $imageable));
	}

	//
	// Slot Methods
	//

	/**
	 * @param array $operations
	 * @param \Symfony\Component\HttpFoundation\File\File[] $files
	 * @param Imageable $imageable
	 */
	public function batch(array $operations, array $files = null, Imageable $imageable = null) {

		// Perform any operations first so that images can move out of the way for new ones.
		foreach($operations as $slot => $operation) {

			// Do deletes first.
			if(empty($operation))
				$this->deleteBySlot($slot, $imageable);

			// Then move/swaps.
			elseif(is_int($operation))
				$this->moveToSlot($this->getById($operation), $slot);

			// Then remote images.
			elseif(filter_input($operation, FILTER_VALIDATE_URL)) {

				try {
					$this->saveFromUri($operation, $imageable, ['slot' => $slot]);
				}
				catch(Exception $ex) {
					// Displace whatever is in the slot.
					$this->moveToSlot($this->getBySlot($slot), null);
					$this->saveFromUri($operation, $imageable, ['slot' => $slot]);
				}

			}

		}

		// Handle file uploads.
		foreach($files as $file) {
			try {
				$this->saveFromFile($file, $imageable, ['slot' => $slot]);
			}
			catch(Exception $ex) {
				// Displace whatever is in the slot.
				$this->moveToSlot($this->getBySlot($slot), null);
				$this->saveFromFile($file, $imageable, ['slot' => $slot]);
			}
		}

	}

	public function moveToSlot(Image $image, $slot) {

		try {
			// Assign the new slot to our image.
			$image->slot = $slot;
			$image->save();
		}
			// Something is already in our slot.
		catch(Exception $ex) {

			// Move the previous image out temporarily, we'll perform a swap.
			$previousSlotImage = $this->getBySlot($slot, $image->imageble);
			$previousSlotImage->slot = null;
			$previousSlotImage->save();

			// Save the slot our image is in.
			$previousSlot = $image->slot;
			// NOW save!
			$image->slot = $slot;
			$image->save();

			// If our file had a non-null slot, move the previous occupant of the target slot into it.
			if($previousSlot !== null) {
				$previousSlotImage->slot = $previousSlot;
				$previousSlotImage->save;
			}

		}

	}

	//
	// Utility Methods
	//

	/**
	 * Gets the current or specified driver.
	 *
	 * @param null $abstract
	 * @return \TippingCanoe\Phperclip\Storage\Driver
	 */
	protected function getDriver($abstract = null) {
		return $abstract ? $this->storageDrivers[$abstract] : $this->currentDriver;
	}

	/**
	 * Create the database entry for a file.
	 *
	 * @param File $file
	 * @return FileModel
	 */
	protected function createFileRecord(File $file) {

		// Obtain file metadata and save the record to the database.

		$attributes = [];

		return $this->fileRepository->create($attributes);

	}

	/**
	 * Pass a file save into the current Driver.
	 *
	 * @param File $file
	 * @param Model\Image $image
	 * @param array $filters
	 * @throws \Exception
	 */
	protected function saveFile(File $file, Image $image, array $filters = []) {


		$this->getDriver()->saveFile($file, $image, $filters);
	}

}