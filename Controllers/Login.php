<?php namespace Controllers;

use Application\Input;
use Application\Registry;
use Models\User;
use Application\Session;

class Login extends JSONController {
	public function login() {
		try {
			$input = new Input('POST');
			$input->filter('id_token', FILTER_UNSAFE_RAW);
			$id_token = $input->id_token;
			
			$curl = curl_init('https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=' . $id_token);
			curl_setopt_array($curl, [
					CURLOPT_RETURNTRANSFER => true,
					CURLOPT_SSL_VERIFYPEER => true,
					CURLOPT_TIMEOUT => 10
			]);
			
			if (($data = curl_exec($curl)) !== false) {
				$data = json_decode($data);
				
				if (isset($data->aud) && $data->aud == Registry::getInstance()->config['google-signin-client_id']) {
					$user = User::loadUser($data->sub);
					if ($user === false) {
						$user = new User();
						$user->setUser($data->sub);
						$user->setMail($data->email);
						$user->save();
					}
					Session::start();
					$_SESSION['user_id'] = $user->getId();
					$this->returnJSON([
							'status' => 'ok'
					]);
				} else {
					$this->jsonError(202, 'Invalid token');
				}
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