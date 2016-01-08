<?php namespace Application;

class Registry {
	private $vars = array();
	private static $instance = NULL;
	
	private function __construct() {
	}
	
	public function __destruct() {
		self::$instance = NULL;
	}
	
	/**
	 * Get global registry instance
	 * 
	 * @return Registry instance
	 */
	public static function getInstance() {
		if (self::$instance == NULL) {
			$class = __CLASS__;
			self::$instance = new $class;
		}
		
		return self::$instance;
	}
	
	/**
	 * Set a variable
	 * 
	 * @param string $index
	 * @param mixed $value
	 */
	public function __set($index, $value) {
		$this->vars[$index] = $value;
	}
	
	/**
	 * Get a variable
	 * 
	 * @param string $index
	 * @return mixed
	 */
	public function __get($index) {
		return $this->vars[$index];
	}
	
	/**
	 * Check if a variable is set
	 * 
	 * @param string $index
	 * @return True if the variable is set
	 */
	public function __isset($index) {
		return isset($this->vars[$index]);
	}
}

?>