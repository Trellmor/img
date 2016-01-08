<?php namespace Models;

use Application\Session;

class Message {
	const LEVEL_ERROR = 'error';
	const LEVEL_INFO = 'info';
	const LEVEL_WARNING = 'warning';
	const LEVEL_SUCCESS = 'success';
	
	private $level;
	private $message;
	
	public function __construct($message, $level = Message::LEVEL_ERROR) {
		$this->message = $message;
		$this->level = $level;
	}
	/*
	public static function save($message, $level = Message::LEVEL_ERROR) {
		$c = __CLASS__;
		$message = new $c($message, $level);
		
		Session::start();
		$_SESSION['messages'][] = $message;
		
		return $message;
	}
	
	public static function getSavedMessages() {
		if (isset($_SESSION['messages'])) {
			$messages = $_SESSION['messages'];
			unset($_SESSION['messages']);
			return $messages;
		} else {
			return array();	
		}
	}
	*/
	public function getLevel() {
		return $this->level;
	}
	
	public function getCSSLevel() {
		switch ($this->level) {
			case static::LEVEL_SUCCESS:
				return 'alert-success';
			case static::LEVEL_INFO:
				return 'alert-info';
			case static::LEVEL_WARNING:
				return 'alert-warning';
			case static::LEVEL_ERROR:
				return 'alert-danger';
		}
	}
	
	public function getMessage() {
		return $this->message;
	}
}

?>