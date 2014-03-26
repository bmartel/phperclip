<?php
/**
 * Created by PhpStorm.
 * User: admin
 * Date: 2014-03-25
 * Time: 11:05 AM
 */

namespace TippingCanoe\Phperclip\Model;


interface Clippable {

	/**
	 * @return \Illuminate\Database\Eloquent\Relations\MorphMany
	 */
	public function phperclip_files();
} 