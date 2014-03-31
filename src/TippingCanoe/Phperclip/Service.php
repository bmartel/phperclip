<?php

namespace TippingCanoe\Phperclip;

use TippingCanoe\Phperclip\Model\File as FileModel;
use TippingCanoe\Phperclip\Model\Clippable;
use TippingCanoe\Phperclip\Processes\ProcessManager;
use TippingCanoe\Phperclip\Repository\File as FileRepository;
use Symfony\Component\HttpFoundation\File\File;

class Service {

	/**
	 * @var \TippingCanoe\Phperclip\Repository\File
	 */
	protected $fileRepository;

	/**
	 * @var \TippingCanoe\Phperclip\Storage\Driver[]
	 */
	protected $storageDrivers;

	/**
	 * @var \TippingCanoe\Phperclip\Storage\Driver
	 */
	protected $currentDriver;

	/**
	 * @var \TippingCanoe\Phperclip\Processes\ProcessManager
	 */
	protected $processManager;

	/**
	 * @param FileRepository $fileRepository
	 * @param \TippingCanoe\Phperclip\Storage\Driver[] $storageDrivers
	 * @throws \Exception
	 */
	public function __construct(
		FileRepository $fileRepository,
		ProcessManager $processManager,
		array $storageDrivers
	) {

		$this->fileRepository = $fileRepository;
		$this->processManager = $processManager;

		if (empty($storageDrivers)) {
			throw new \Exception('You must configure at least one file storage driver for Phperclip to use.');
		}

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

		if (!$this->getDriver()->has($fileModel)) {
			$tempOriginal = $this->getDriver()->tempOriginal($fileModel);
			$this->saveFile($tempOriginal, $fileModel);
		}

		return $this->getDriver()->getPublicUri($fileModel);

	}

	/**
	 * Returns a file URI based on the id of the original.
	 *
	 * @param int $id
	 * @param array $filters
	 * @return string
	 */
	public function getPublicUriById($id) {

		return $this->getPublicUri($this->getById($id));
	}

	/**
	 * Returns a File URI based on the slot and clippable
	 *
	 * @param $slot
	 * @param Clippable $clippable
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
	 * @return null|FileModel
	 */
	public function saveFromFile(File $file, Clippable $clippable = null) {

		// Determine if there are any registered processors which know of this file type
		// and the current action scope.

		if (!$this->processManager->dispatch($file, 'onSave')) {
			return null;
		} //Bail if for whatever reason one of the processors returns false

		$newFile = $this->createFileRecord($file);

		// Clippables are optional
		if ($clippable) {
			$clippable->phperclip_files()->save($newFile);
		}

		$this->saveFile($file, $newFile);

		return $newFile;

	}

	/**
	 * Saves a new local file from a file available via any of the standard PHP supported schemes.
	 *
	 * @param $uri
	 * @param Clippable $clippable
	 * @return null|FileModel
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
	 * Delete the file
	 *
	 * @param FileModel $fileModel
	 */
	public function delete(FileModel $fileModel) {

		// Determine if there are any registered processors which know of this file type
		// and the current action scope.

		if (!$this->processManager->dispatch($fileModel, 'onDelete')) {
			return null;
		} //Bail if for whatever reason one of the processors returns false

		$this->getDriver()->delete($fileModel);

		$fileModel->delete();

	}

	/**
	 * @param $id
	 * @param array $filters
	 */
	public function deleteById($id) {

		$this->delete($this->getById($id));
	}

	public function deleteBySlot($slot, Clippable $clippable = null) {

		$this->delete($this->getBySlot($slot, $clippable));
	}

	//
	// Slot Methods
	//

	/**
	 * Perform batch operations on files.
	 *
	 * @param array $operations
	 * @param array $files
	 * @param Clippable $clippable
	 */
	public function batch(array $operations, array $files = null, Clippable $clippable = null) {

		// Perform any operations first so that files can move out of the way for new ones.
		foreach ($operations as $slot => $operation) {

			// Do deletes first.
			if (empty($operation)) {
				$this->deleteBySlot($slot, $clippable);
			} // Then move/swaps.
			elseif (is_int($operation)) {
				$this->moveToSlot($this->getById($operation), $slot);
			} // Then remote files.
			elseif (filter_input($operation, FILTER_VALIDATE_URL)) {

				try {
					$this->saveFromUri($operation, $clippable, ['slot' => $slot]);
				} catch (\Exception $ex) {
					// Displace whatever is in the slot.
					$this->moveToSlot($this->getBySlot($slot), null);
					$this->saveFromUri($operation, $clippable, ['slot' => $slot]);
				}

			}

		}

		// Handle file uploads.
		foreach ($files as $file) {
			try {
				$this->saveFromFile($file, $clippable, ['slot' => $slot]);
			} catch (\Exception $ex) {
				// Displace whatever is in the slot.
				$this->moveToSlot($this->getBySlot($slot), null);
				$this->saveFromFile($file, $clippable, ['slot' => $slot]);
			}
		}

	}

	/**
	 * Move a file from one logical slot to another.
	 *
	 * @param FileModel $fileModel
	 * @param $slot
	 * @return null
	 */
	public function moveToSlot(FileModel $fileModel, $slot) {

		// Determine if there are any registered processors which know of this file type
		// and the current action scope.

		if (!$this->processManager->dispatch($fileModel, 'onMove')) {
			return null;
		} //Bail if for whatever reason one of the processors returns false

		try {
			// Assign the new slot to our file.
			$fileModel->slot = $slot;
			$fileModel->save();
		} // Something is already in our slot.
		catch (\Exception $ex) {

			// Move the previous file out temporarily, we'll perform a swap.
			$previousSlotImage = $this->getBySlot($slot, $fileModel->clippable);
			$previousSlotImage->slot = null;
			$previousSlotImage->save();

			// Save the slot our file is in.
			$previousSlot = $fileModel->slot;
			// NOW save!
			$fileModel->slot = $slot;
			$fileModel->save();

			// If our file had a non-null slot, move the previous occupant of the target slot into it.
			if ($previousSlot !== null) {
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
	 * @param FileModel $fileModel
	 */
	protected function saveFile(File $file, FileModel $fileModel) {


		$this->getDriver()->saveFile($file, $fileModel);
	}

}