<?php return [

	// Register File Processors
	'processors' => [

		//      Implement your own file specific processing by adding a processor here.
		//
		//		'TippingCanoe/Phperclip/Processes/ImageProcessor',
		//		'TippingCanoe/Phperclip/Processes/PdfProcessor',
	],

	// Multiple storage options.
	'storage' => [

		'TippingCanoe\Phperclip\Storage\Filesystem' => [

			// Directory that Phperclip can manage everything under.
			'root' => public_path() . '/uploaded/files',

			// Public, client-accessible prefix pointing to wherever the root is hosted, including scheme.
			'public_prefix' => sprintf('%s/uploaded/files', Request::getSchemeAndHttpHost()),

		],

		// Amazon S3 Storage Driver
		/*
		'TippingCanoe\Phperclip\Storage\S3' => [
			'bucket' => 'clipped_files'
		],
		*/

		//    ],

		//
		// Amazon S3 Client
		//
		// Uncommenting these lines tells Phperclip to take care
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