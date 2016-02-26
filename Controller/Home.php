<?php

namespace Controller;

use Application\CSRF;
use Application\Crypto\SecureRandom;

class Home extends ImgController {

	public function home() {
		$csrf = new CSRF();

		$uuid = $this->uuid();
		$uploadid = $csrf->hashHMAC($uuid);

		$this->view->assignVar('csrf', $csrf);
		$this->view->assignVar('uploadid', $uploadid);
		$this->view->load('home');
	}

	protected function uuid() {
		$sr = new SecureRandom();
		$data = $sr->getBytes(16);
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);

		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	}
}
