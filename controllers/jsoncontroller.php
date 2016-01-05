<?php namespace Controllers;

abstract class JSONController {

	public function __construct() {
		// Make sure file is not cached (as it happens for example on iOS devices)
		header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
		header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
		header("Cache-Control: no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header('Content-Type: application/json');
	}
	
	protected function returnJSON($data) {
		die(json_encode($data));
	}
	
	protected function jsonError($code, $message) {
		$this->returnJSON([
				'status' => 'error',
				'error' => [
						'code' => $code,
						'message' => $message
				]
		]);
	}
}