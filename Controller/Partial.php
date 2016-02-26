<?php

namespace Controller;

class Partial extends ImgController {

	public function navbar() {
		$this->view->load('navbar');
	}
}
