<?php

return [
	// Sample Image Filter

	// Example usage:
	//
	// Phperclip::saveFromFile($file, ['filters' => Config::get('phperclip::filters.shrink')]);
	//
	'shrink' => [

		'TippingCanoe\Phperclip\Processes\Image\FixRotation',
		[
			'TippingCanoe\Phperclip\Processes\Image\Resize',
			[
				'width' => 100,
				'height' => 100,
				'preserve_ratio' => true,
			]
		]
	]
];