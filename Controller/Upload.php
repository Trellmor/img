<?php

namespace Controller;

use Application\Exception\ValidationException;
use Application\Registry;
use Application\File;
use Application\Input;
use Model\Image;
use Application\CSRF;
use Application\Uri;

class Upload extends JSONController {

	public function upload() {
		$file = null;
		try {
			if (!isset($_FILES['file'])) {
				throw new ValidationException(_('Upload failed.'));
			}

			$file = $_FILES['file'];

			$csrf = new CSRF();
			if (!$csrf->verifyToken()) {
				throw new ValidationException(_('Upload failed.'));
			}

			$input = new Input(Input::POST);
			$input->filter('tags', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_REQUIRE_ARRAY);
			$input->filter('name', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);

			$input->filter('uploadid', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
			$uploadid = null;
			if ($input->uploadid !== false) {
				if (($uploadid = $csrf->verifyHMAC($input->uploadid)) === false) {
					throw new ValidationException(_('Upload failed.'));
				}
			}

			if ($file['error'] !== UPLOAD_ERR_OK) {
				throw new ValidationException(_('Upload failed.'));
			}

			// The image is to big
			if ($file['size'] > Registry::getInstance()->config['maxsize']) {
				throw new ValidationException(_('Image too big.'));
			}

			if ($input->name !== false) {
				$file['name'] = $input->name;
			}

			$image = new Image();
			$image->upload($file, $input->tags, $uploadid);

			$this->returnJSON([
					'status' => 'ok',
					'redirect' => (string) Uri::to('album/' . urlencode($uploadid) . '/')
			]);
		} catch (\Exception $e) {
			if ($file != null) File::unlink($file['tmp_name']);
			$this->jsonError(100, $e->getMessage());
		}
	}
}
