<?php return [

	// Register File Processors
	'processors' => [
		'TippingCanoe/Phperclip/Processes/ImageProcessor',
		'TippingCanoe/Phperclip/Processes/PDFProcessor',
	],


	// Multiple storage options.
	'storage' => [

		'TippingCanoe\Phperclip\Storage\Filesystem' => [

			// Directory that Phperclip can manage everything under.
			'root' => public_path() . '/phperclip_files',

			// Public, client-accessible prefix pointing to wherever the root is hosted, including scheme.
			'public_prefix' => sprintf('%s/phperclip_files', Request::getSchemeAndHttpHost()),

		],

		// Amazon S3 Storage Driver
		/*
		'TippingCanoe\Phperclip\Storage\S3' => [
			'bucket' => 'phperclip_files'
		],
		*/

		//    ],

		//
		// Amazon S3 Client
		//
		// Uncommenting these lines tells Imager to take care
		// of the Amazon S3 binding in the IoC.
		//
		// It may be that this binding is accomplished elsewhere in your
		// project and if so, you don't need to duplicate it here.
		//
		//'s3' => [
		//	'key' => 'YOUR_KEY_HERE',
		//	'secret' => 'YOUR_SECRET_HERE',
		//]
	]
];