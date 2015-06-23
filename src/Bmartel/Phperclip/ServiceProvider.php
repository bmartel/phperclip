<?php

namespace Bmartel\Phperclip;


use Bmartel\Phperclip\Processes\ProcessManager;
use Bmartel\Phperclip\Service as PhperclipService;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{

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
	public function boot()
	{
		$this->package('tippingcanoe/phperclip');

		// Register the filename generator ahead of the other registrations
		$this->registerFileNameGenerator();
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		/** @var \Illuminate\Config\Repository $config */
		$config = $this->app->make('config');

		$this->app->bind('Bmartel\Phperclip\Contracts\FileRepository', 'Bmartel\Phperclip\Repository\FileRepository');

		$this->app->bindShared('Bmartel\Phperclip\Service', function ($app) use ($config) {

			// Register the amazon aws client
			$this->registerAwsClient($config);

			// Configure the storage drivers
			$storageDrivers = $this->configureStorageDrivers($app, $config);

			// Register File processors
			$this->registerFileProcessors($app, $config);

			// Register the Phperclip service
			return new PhperclipService(
				$app->make('Bmartel\Phperclip\Contracts\FileRepository'),
				$app->make('Bmartel\Phperclip\Processes\ProcessManager'),
				$storageDrivers
			);

		});

	}

	private function registerAwsClient($config){
		//
		// Amazon S3
		//
		if ($s3Config = $config->get('phperclip::s3')) {
			$this->app->bind('Aws\S3\S3Client', function ($app) use ($s3Config) {
				return \Aws\S3\S3Client::factory($s3Config);
			});
		}
	}

	private function registerFileNameGenerator()
	{
		$this->app->bind('Bmartel\Phperclip\Contracts\FileNameGenerator', $this->app->make('config')->get('phperclip::filename_generator'));
	}

	private function configureStorageDrivers($app, $config)
	{
		// Run through and call each config option as a setter on the storage method.
		$storageDrivers = [];
		foreach ($config->get('phperclip::storage', []) as $abstract => $driverConfig) {

			$driver = $app->make($abstract);

			foreach ($driverConfig as $property => $value) {
				$setter = studly_case('set_' . $property);
				$driver->$setter($value);
			}

			$storageDrivers[$abstract] = $driver;
		}

		return $storageDrivers;
	}

	private function registerFileProcessors($app, $config)
	{

		if ($processorConfig = $config->get('phperclip::processors')) {

			$processors = [];

			foreach ($processorConfig as $processor) {
				$processors[] = $app->make($processor);
			}

			$this->app->bindShared('Bmartel\Phperclip\Processes\ProcessManager', function () use ($app, $processors) {
				return new ProcessManager($app['session'], $processors);
			});
		}
	}
}