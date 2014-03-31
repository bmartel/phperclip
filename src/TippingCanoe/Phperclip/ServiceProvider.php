<?php

namespace TippingCanoe\Phperclip;


use TippingCanoe\Phperclip\Processes\ProcessManager;
use TippingCanoe\Phperclip\Service as PhperclipService;

class ServiceProvider extends \Illuminate\Support\ServiceProvider{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot() {
		$this->package('tippingcanoe/phperclip');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register() {

		/** @var \Illuminate\Config\Repository $config */
		$config = $this->app->make('config');

//		// I'm not binding this as a singleton so that sloppy state management doesn't get a chance to ruin things.
//		$this->app->bind('Intervention\Image\Image', function (Application $app) {
//			return new Image();
//		});

		$this->app->bind('TippingCanoe\Phperclip\Repository\FileInterface', 'TippingCanoe\Phperclip\Repository\File');



		$this->app->singleton('TippingCanoe\Phperclip\Service', function (Application $app) use ($config) {

			//
			// Amazon S3
			//
			if($s3Config = $config->get('phperclip::s3')) {
				$this->app->bind('Aws\S3\S3Client', function (Application $app) use ($s3Config) {
					return \Aws\S3\S3Client::factory($s3Config);
				});
			}

			// Run through and call each config option as a setter on the storage method.
			$storageDrivers = [];
			foreach($config->get('phperclip::storage', []) as $abstract => $driverConfig) {

				$driver = $app->make($abstract);

				foreach($driverConfig as $property => $value) {
					$setter = studly_case('set_' . $property);
					$driver->$setter($value);
				}

				$storageDrivers[$abstract] = $driver;

			}

			// Register File processors
			if($processorConfig = $config->get('phperclip::processors')){

				$processors = [];

				foreach($processorConfig as $processor) {
					$processors[] = $app->make($processor);
				}

				$this->app->bind('TippingCanoe\Phperclip\Processes\ProcessManager', new ProcessManager($processors));
			}


			return new PhperclipService(
				$app->make('TippingCanoe\Phperclip\Model\File'),
				$app->make('TippingCanoe\Phperclip\Processes\ProcessManager'),
				$app,
				$storageDrivers
			);

		});


	}

} 