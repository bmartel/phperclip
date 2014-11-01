<?php namespace TippingCanoe\Phperclip\Repository;

use TippingCanoe\Phperclip\Model\Clippable;
use TippingCanoe\Phperclip\Model\File as FileModel;

interface FileInterface {

	/**
	 * Creates a new file object in the database.
	 *
	 * @param $attributes
	 * @return FileModel
	 */
	public function create($attributes);

	/**
	 * Gets a file object by it's id.
	 *
	 * @param int $id
	 * @return null|FileModel
	 */
	public function getById($id);

	/**
	 * @param $slot
	 * @param Clippable $clippable
	 * @return null|FileModel
	 */
	public function getBySlot($slot, Clippable $clippable = null);

}