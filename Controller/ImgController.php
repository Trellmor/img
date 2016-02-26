<?php

namespace Controller;

use Application\Registry;

abstract class ImgController extends HTMLController {

	public function __construct() {
		parent::__construct();
		$this->view->assignVar('config', Registry::getInstance()->config);
		$this->view->assignVar('page_title', Registry::getInstance()->config['page_title']);
		$this->view->assignVar('user', isset(Registry::getInstance()->user) ? Registry::getInstance()->user : null);
		$this->view->assignVar('js', array());
	}
}
