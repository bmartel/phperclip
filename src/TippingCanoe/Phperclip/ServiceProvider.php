<?php

namespace TippingCanoe\Phperclip;


use TippingCanoe\Phperclip\Processes\ProcessManager;
use TippingCanoe\Phperclip\Service as PhperclipService;

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

		// Register File processors
		$this->registerFileProcessors($this->app, $config);

		$this->app->bind('TippingCanoe\Phperclip\Contracts\FileRepository', 'TippingCanoe\Phperclip\Repository\FileRepository');
		$this->app->bind('TippingCanoe\Phperclip\Contracts\StorageDriver', 'TippingCanoe\Phperclip\Storage');

		$this->app->singleton('TippingCanoe\Phperclip\Service', function ($app) use ($config) {

			// Register the Phperclip service
			return new PhperclipService(
				$app->make('TippingCanoe\Phperclip\Contracts\FileRepository'),
				$app->make('TippingCanoe\Phperclip\Processes\ProcessManager'),
				$app->make('TippingCanoe\Phperclip\Contracts\StorageDriver')
			);
		});

	}

	private function registerFileNameGenerator()
	{
		$this->app->bind('TippingCanoe\Phperclip\Contracts\FileNameGenerator', $this->app->make('config')->get('phperclip::filename_generator'));
	}

	private function registerFileProcessors($app, $config)
	{

		if ($processorConfig = $config->get('phperclip::processors')) {


			$this->app->singleton('TippingCanoe\Phperclip\Processes\ProcessManager', function() use($app, $processorConfig) {

				$processors = [];

				foreach ($processorConfig as $processor) {
					$processors[] = $app->make($processor);
				}

				return new ProcessManager($app['session'], $processors);
			});

		}
	}
}