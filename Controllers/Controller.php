<?php namespace Controllers;

use Application\Registry;
use Models\Message;
use View\View;

abstract class Controller {
	protected $view;

	public function __construct() {
		$this->view = new View();
		$this->view->assignVar('config', Registry::getInstance()->config);
		$this->view->assignVar('page_title', Registry::getInstance()->config['page_title']);
		$this->view->assignVar('user', isset(Registry::getInstance()->user) ? Registry::getInstance()->user : null);
	}

	protected function error($code, $message) {
		http_response_code($code);

		$this->message(new Message($message, Message::LEVEL_ERROR));
	}

	protected function info($message) {
		$this->message(new Message($message, Message::LEVEL_INFO));
	}

	protected function success($message) {
		$this->message(new Message($message, Message::LEVEL_SUCCESS));
	}

	protected function warn($message) {
		$this->message(new Message($message, Message::LEVEL_WARNING));
	}

	protected function message($message) {
		$this->view->assignVar('message', $message);
		$this->view->load('message');
	}

	protected function redirect($uri) {
		header('Location: ' . $uri);
	}
}

?>