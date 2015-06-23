<?php namespace Bmartel\Phperclip\Processes;

use App;
use Symfony\Component\HttpFoundation\File\File;
use Bmartel\Phperclip\Contracts\Filter;
use Validator;

class ImageProcessor extends FileProcessorAdapter {

	protected $mimeTypes = ['image/png', 'image/jpeg'];

	public function onBeforeSave(File $file, array $options = []) {

		// Run validation on the image
		if (!$this->runValidation($file, $options)) {
			// If validation fails, stop processing immediately.
			return false;
		}

		return $file;
	}

	public function onSave(File $file, array $options = []) {

		// Run filters on the image
		$this->runFilters($file, $options);

		// If everything was ok, pass the file onto the next processor
		// and perform the save.
		return $file;
	}

	/**
	 * Run Image processing filters on the current image.
	 *
	 * @param File $file
	 * @param array $options
	 */
	private function runFilters(File $file, array $options = []) {

		// Need the filters from the options array to exist.
		if (empty($options) || !array_key_exists('modifications', $options) || !is_array($options['modifications'])) {
			return null;
		}

		foreach ($options['modifications'] as $filter) {

			/**
			 * @var \Bmartel\Phperclip\Contracts\Filter $abstractFilterClass
			 */
			if (!empty($filter) && is_array($filter)) {
				// Set any class property configs
				$abstractFilterClass = $this->setFilterClassProperties(App::make($filter[0]), $filter[1]);

			} elseif (!empty($filter) && is_string($filter)) {
				$abstractFilterClass = App::make($filter);
			}

			// Run the filter on the image
			$abstractFilterClass->run($file);
		}
	}

	/**
	 * Sets public properties on the filter class.
	 *
	 * @param Filter $abstractFilterClass
	 * @param $filterParams
	 * @return Filter
	 */
	private function setFilterClassProperties(Filter $abstractFilterClass, $filterParams) {

		if (!empty($filterParams) && is_array($filterParams)) {
			foreach ($filterParams as $property => $value) {
				$setProperty = sprintf('set%s', studly_case($property));
				$abstractFilterClass->$setProperty($value);
			}
		}

		return $abstractFilterClass;
	}

}