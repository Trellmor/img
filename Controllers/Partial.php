<?php namespace Controllers;

class Partial extends Controller {
	public function navbar()  {
		$this->view->load('navbar');
	}
}
