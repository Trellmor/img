<?php namespace View;

use Application\Exceptions\InvalidViewException;

class View {
	private $template;
	private $vars = array();
	
	public function __construct() {
		$this->assignVar('view', $this);
		$this->assignVar('js', array());
	}
		
	public function assignVar($name, $value) {
		$this->vars[$name] = $value;
	}
	
	public function assignVars($vars) {
		$this->vars = array_merge($this->vars, $vars);
	}
	
	public function getVars() {
		return $this->vars;	
	}
	
	public function load($view) {
		$file = __DIR__ . '/' . $view . '.php';
		if (file_exists($file)) {
			extract($this->vars, EXTR_REFS);
			include $file;
		} else {
			throw new InvalidViewException('View not found: ' . $view);
		}		
	}
}

?>