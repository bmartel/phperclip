<?php namespace TippingCanoe\Phperclip;

use TippingCanoe\Phperclip\Model\File;

class FileNameGenerator implements Contracts\FileNameGenerator {

	protected $mimeResolver;

	public function __construct(MimeResolver $mimeResolver) {

		$this->mimeResolver = $mimeResolver;
	}

	public function fileName(File $file, array $options = []) {

		return sprintf('%d-%s.%s',
			$file->getKey(),
			$this->generateHash($file, $options),
			$this->mimeResolver->getExtension($file->getMimeType())
		);
	}

	/**
	 * Generates an MD5 hash of the file attributes and options.
	 *
	 * @param File $file
	 * @param array $options
	 * @return string
	 */
	protected function generateHash(File $file, array $options = []) {

		$fileSignature = [
			'id' => (string) $file->getKey(),
		];

		// Add any modifications that may have run on the file.
		if(array_key_exists($this->getFileModificationKey(), $options)) {
			$fileSignature[$this->getFileModificationKey()] = $options[$this->getFileModificationKey()];
		}

		return md5(json_encode($this->recursiveKeySort($fileSignature)));
	}

	/**
	 * Utility method to ensure that key signatures always appear in the same order.
	 *
	 * @param array $array
	 * @return array
	 */
	protected function recursiveKeySort(array $array) {

		ksort($array);

		foreach($array as $key => $value)
			if(is_array($value))
				$array[$key] = $this->recursiveKeySort($value);

		return $array;
	}

	/**
	 * This is the name of the array key which to create file variations from its corresponding values.
	 *
	 * @return string
	 */
	public function getFileModificationKey() {
		return 'modifications';
	}
}