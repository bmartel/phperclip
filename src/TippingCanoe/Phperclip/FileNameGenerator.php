<?php namespace TippingCanoe\Phperclip;

use TippingCanoe\Phperclip\Model\File;

class FileNameGenerator implements Contracts\FileNameGenerator {

	protected $mimeResolver;

	public function __construct(MimeResolver $mimeResolver) {

		$this->mimeResolver = $mimeResolver;
	}

	public function fileName(File $file, array $options = null) {

		return sprintf('%d-%s.%s',
			$file->getKey(),
			$this->generateHash($file, $options),
			$this->mimeResolver->getExtension($file->getMimeType())
		);
	}

	protected function generateHash(File $file, array $options = null) {

		$fileSignature = [
			'id' => $file->getKey(),
			'file_type' => $file->getMimeType(),
			'slot' => $file->slot
		];

		if($options) $fileSignature = array_merge($fileSignature, $options);

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

		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$array[$key] = $this->recursiveKeySort($value);
			}
		}

		return $array;
	}

}