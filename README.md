# Phperclip

File upload management boilerplate got you down?  Do you want to simplify the process of modifying and caching originally uploaded files?  Phperclip is a package designed to ease management of file storage, order and manipulation.

Features:

 * Attach files to any eloquent model via an interface and an optionally supplied trait
 * Configurable and customizable storage drivers
 * Generate URIs for files based on which storage driver is in use
 * Pluggable file processing through FileProcessors.
 * Image Processing via an included FileProcessor.
 * A powerful and agnostic batch processor


## Setup

To get Phperclip ready for use in your project, take the usual steps for setting up a Laravel 4 pacakge.

 * Add `tippingcanoe/phperclip` to your `composer.json` file.
 * Run `composer update` at the root of your project.
 * Edit your `app/config/app.php` file and add: 
   * `'TippingCanoe\Phperclip\ServiceProvider',` into the `providers` array 
   * `'Phperclip' => 'TippingCanoe\Phperclip\Facade',` into the `aliases` array
 * Run the migration `./artisan migrate --package="tippingcanoe/phperclip"`
 * Take a project-level copy of the configuration `./artisan config:publish tippingcanoe/phperclip`

```
Note: If you are using type-hinted dependency injection in your project, as a convenience Phperclip binds the type `TippingCanoe\Phperclip\Service` in the container.
```

## Configuration

### File Name Generation
File names are generated using an injected class which implements `TippingCanoe\Phperclip\Contracts\FileNameGenerator`. By default, Phperclip uses its own implementation of the file name generator `TippingCanoe\Phperclip\FileNameGenerator`. It is set in the config as:

```
	'filename_generator' => 'TippingCanoe\Phperclip\FileNameGenerator'
```

This implementation takes an md5 hash of the json result of the files options and attributes. If you wish to generate the filename using other means, create your own implementation, and include the class as the value of filename_generator in Phperclip's `config.php` file.

### Storage
If you open the copy of `config.php` that was created during setup, you will see it is already populated with configuration options for the most typical of setups.  The `TippingCanoe\Phperclip\Storage\Filesystem` driver is the most basic which simply stores image files in your site's public directory.

#### Amazon S3 Driver
Replace the filesystem driver configuration in the 'config.php' file with the Amazon AWS configuration below.
```
[
	'storage' =>
	[
		'TippingCanoe\Phperclip\Storage\S3' =>
		[
			'aws_key' => 'YOUR_KEY_HERE',
			'aws_secret' => 'YOUR_SECRET_HERE',
            'aws_bucket' => 'phperclip-bucket'
		]
	]
]
```
### Processors
Phperclip doesn't dictate what you want to do with your files. Instead it provides hooks into the service lifecycle methods via
FileProcessors. FileProcessors allow you to specify the file types it will be responsible for, and the lifecycle methods: onBeforeSave, onSave, onDelete, and onMove allow you to perform any processing on the file you wish. 

The following lifecycle methods will abort the current operation if a false type is returned from them. The normal run condition of the methods expect you to return the file which was passed in.

* onBeforeSave: Executes before any files are persisted. Great for validation or other pre-save processing in which you do not want the file to be created on failure.
* onSave: Executes immediately after the original, unmodified file is persisted. Allows for modifications to take place on the file.
* onDelete: Executes whenever a file is about to be deleted. Great place to start cleaning up pesky file relationships, perform validation, or authorization on the user requesting the file deletion.
* onMove: Executes whenever a file is going to move between slots. 

An included ImageProcessor has been provided as both a showcase the power of the FileProcessor components, as well as to provide flexibile and chainable Image filtering!

### Image Filters
Phperclip's filtering chains are a powerful feature that allow you to orchestrate arbitrary combinations of manipulations when saving or retrieving images.  When processing a chain, Phperclip does the following for each filter in the chain:

 * Attempts to instantiate the indicated class which must implement `TippingCanoe\Phperclip\Contracts\Filter`
 * If the filter's configuration was an array, each key in the second index will be called as setter methods on the subclass
 * Calls the method `run` on the subclass passing in the original image and the database entry for the image
 
You will most likely want to pre-configure filter chains for your project so that you don't have to repeat them over the course of retrieving variations.  Phperclip uses a simple array schema to define filtering chains.  Here's a sample of one:

```
[

	'TippingCanoe\Phperclip\Processes\Image\FixRotation',
	
	[
		'TippingCanoe\Phperclip\Processes\Image\Resize',
		[
			'width' => 300,
			'height' => 300,
			'preserve_ratio' => true
		]
	],
	
	[
		'TippingCanoe\Phperclip\Processes\Image\Watermark',
		[
			'source_path' => sprintf('%s/logo.png', __DIR__),
			'anchor' => 'bottom-right'
		]
	]

]
```

 * The array must not be keyed.
 * An entry that is a string will be instantiated and run without parameters.
 * An entry that is a sub-array will have the first index `[0]` of that array instantiated with the second index `[1]` converted to setters on the instance:
   * setWidth(300)
   * setHeight(300)
   * setPreserveRatio(true)
   * etc...

If you're unsure as to where you should store your filter profiles, it's suggested that you place them in the `filters.php` file that has also been created for you when you published Phperclip's configuration earlier.  This will allow you to vary the filter configurations along with your environments and will make retrieval as simple as `Config::get('phperclip::filters.filter_name')`


## Usage

Depending on the nature of your implementation, the means by which you will receive files will vary.  Phperclip makes no assumptions about your request lifecycle (or that there's even a request at all!) and only concerns itself with recieving instances of `Symfony\Component\HttpFoundation\File\File`.

The two optional, secondary pieces of information that Phperclip makes use of are `Clippable` to scope to a specific model and an options which allow for processing to take place on the file.


### Trait

If you plan on attaching files to a model (User, Item, ImageGallery), that model must implement the interface `TippingCanoe\Phperclip\Model\Clippable`.  This will mandate a method that you can either implement yourself or conveniently keep in sync with Phperclip by using the trait `TippingCanoe\Phperclip\Model\ClippableImpl`.


### Saving

Saving images is done via the Phperclip service which can either be accessed via the facade or through dependency injection.

```
	/** @var \Symfony\Component\HttpFoundation\File\File $file */
	/** @var \TippingCanoe\Phperclip\Model\Clippable $clippable */

	$options = [
		'attributes' => ['slot' => 1]
	];

	/** @var \TippingCanoe\Phperclip\Model\File $file */
	$file = Phperclip::saveFromFile($file, $clippable, $options);
```

Phperclip will return an instance of `TippingCanoe\Phperclip\Model\File` upon a successful save.  If you supplied one, the image record will be associated with a clippable.  Any additional attributes will be passed through to the save as well via the attributes key of the options array.

### Retrieval

When retrieving an individual image, you will need a way to identify it:

 * The file's `id`
 * The file's clippable and a slot
 * A file's `clippedFiles()` relation

Most of the time you will have at least one of these three pieces of information which will then allow you to obtain a URI to the physical file of the image.

```
	Phperclip::getPublicUri($image, $options);
	Phperclip::getPublicUriBySlot($slot, $clippable, $options);
	Phperclip::getPublicUriById($id, $options);
```

You can also retrieve a collection of files, optionally by the clippable it belongs to, file mimetype, or slot.
Phperclip will return a collection of `TippingCanoe\Phperclip\Model\File`.

```
  Phperclip::getFilesFor($clippable, $mimetypes, $slot);
```

When retrieving files from Phperclip, it's helpful to remember that anywhere you see "clippable" is optional and omitting it or providing null means _"global"_.  Similarly, "options" is also optional and omitting this value, providing null or an empty array will mean _"the original file"_.


### Slots

Phperclip features a concept known as slots which at it's very core is just a string value.  Slots are used to order and/or key files by their clippable.  There are helper scopes on the `TippingCanoe\Phperclip\Model\File` class to help with retrieving images based on their slot values.

```
Note: When storing files without a clippable (globally), keep in mind that they are all sharing the same slot scope and cannot have duplicates.
```

A sample use case for slots would be an "Item" class that can have an image gallery as well as a "primary" image.  Images belonging to the gallery would have slots that are numeric so that they can be kept in a specific order while the primary image is in a named slot that can be queried directly.


#### Batches

It's common for implementations to require a way to submit multiple changes to a clippable's files in a single pass.  These changes can sometimes present conflicts and be challenging to resolve.

As a convenience, Phperclip supplies a batch method off the service that allows these bulk operations to be performed.  The operations are scoped by clippable and performed slot-by-slot in a safe order.

The structure of the schema is caller agnostic and in the unavoidable case of a conflict will null-out the slot of any files being displaced.

Here's a sample of the schema used when performing batch operations:

```
	$operations = [
		1 => 2,
		3 => 'thumbnail',
		4 => null,
		5 => 'http://placehold.it/200x200&text=Phperclip'
	];

	/** @var \Symfony\Component\HttpFoundation\File\File[] $newFiles */
	/** @var \TippingCanoe\Phperclip\Model\Clippable $clippable */

	Phperclip::batch($operations, $newFiles, $clippable);

```
In this example, the following actions would be taken - in order:

 * The files in slot 1 and 2 are swapped
 * The file in slot 3 is moved to the 'thumbnail' slot
 * The file in slot 4 will be deleted
 * The file found at the URI will be downloaded and inserted into slot 5

The file array `$newFiles` will be keyed by slot and could in theory contain new a new file for slot 4.

When a file is told to move to a new slot, if there is one aready in the target slot, they are swapped.  If an uploaded file attempts to go into an already-occupied slot, the file currently in the slot will have it's slot nulled out.

It's important to note that slot keys cannot be duplicated in this schema, so it's in your best interest to submit **the simplest** batch list possible.


## Drivers

Creating a driver is as simple as extending the class `TippingCanoe\Phperclip\Storage\Base`.  You can also use `TippingCanoe\Phperclip\Storage\Filesystem` as a reference.

## Processors

To make the magic happen with files, you will want to implement a dedicated FileProcessor to handle tasks which may need to happen during the lifecycle of the file service. You specify in the class which file mimetypes the processor will execute for, and provide the processing in the life cycle methods as appropriate for your own requirements.

To begin, implement the interface `TippingCanoe\Phperclip\Contracts\FileProcessor`. For some free extras like a built in file validation and flash messaging, instead extend the class `TippingCanoe\Phperclip\Processes\FileProcessorAdapter`.

The Image Filters are actually handled via an included FileProcessor `TippingCanoe\Phperclip\Processes\ImageProcessor`! You can use that as a guideline when creating your own FileProcessors.

## Image Filters

It's very easy to create your own image filters within your own project or packages. Just implement the interface `TippingCanoe\Phperclip\Contracts\Filter`.

The only rule is that filter subclasses must perform their manipulations to the file provided without moving, renaming or deleting it - overwriting is fine.  The `run` method is not expected to return a value.

See `TippingCanoe\Phperclip\Processes\Image\Resize` and `TippingCanoe\Phperclip\Processes\Image\FixRotation` as guidelines when creating your own image filters.


## Issues

If you encounter any issues, find a bug or have any questions, feel free to open a ticket in the issue tracker.


### Credits
Brandon Martel - Maintainer
Alex Trauzzi - created original implementation of tippingcanoe/imager, of which this package based on.
