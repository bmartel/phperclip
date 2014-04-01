<?php namespace TippingCanoe\Phperclip\Repository;

use TippingCanoe\Phperclip\Model\Clippable;

interface FileInterface {

	/**
	 * Creates a new File object in the database.
	 *
	 * @param $attributes
	 * @return \TippingCanoe\Phperclip\Model\File
	 */
	public function create($attributes);

	/**
	 * Gets a file object by it's id.
	 *
	 * @param int $id
	 * @return \TippingCanoe\Phperclip\Model\File
	 */
	public function getById($id);

	/**
	 * @param $slot
	 * @param Clippable $clippable
	 * @return \TippingCanoe\Phperclip\Model\File
	 */
	public function getBySlot($slot, Clippable $clippable = null);

}