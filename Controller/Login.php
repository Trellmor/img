<?php

namespace Controller;

use Application\Input;
use Application\Registry;
use Model\User;
use Application\Session;

class Login extends JSONController {

	public function login() {
		try {
			$input = new Input('POST');
			$input->filter('id_token', FILTER_UNSAFE_RAW);

			$gc = new \Google_Client();
			$gc->setClientId(Registry::getInstance()->config['google-signin']['client_id']);
			$gc->setClientSecret(Registry::getInstance()->config['google-signin']['client_secret']);

			if (($data = $gc->verifyIdToken($input->id_token)) !== false) {
				$user = User::loadUser($data['sub']);
				if ($user === false) {
					$user = new User();
					$user->setUser($data['sub']);
					$user->setMail($data['email']);
					$user->save();
				}
				Session::start();
				$_SESSION['user_id'] = $user->getId();
				$this->returnJSON([
						'status' => 'ok'
				]);
			} else {
				$this->jsonError(201, 'Token validation failed');
			}
		} catch (\Exception $e) {
			$this->jsonError(200, $e->getMessage());
		}
	}

	public function logout() {
		Session::destroy();
		$this->returnJSON([
				'status' => 'ok'
		]);
	}
}
