<?php namespace Application;

class Input{
	const POST = 'POST';
	const GET = 'GET';
	
	private $data;
	
	public function __construct($method) {
		switch ($method) {
			case static::GET:
				parse_str(parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY), $this->data);
				break;
			case static::POST:
				$this->data = $_POST;
				break;
			case 'NULL':
				$this->data = array();
				break;
			default:
				parse_str(file_get_contents('php://input'), $this->data);
				break;
		}
	}
	
	public function filter($variable, $filter, $options = null) {
		if ($options != null) {
			$this->data[$variable] = filter_var($this->{$variable}, $filter, $options);
		} else {
			$this->data[$variable] = filter_var($this->{$variable}, $filter);
		}
	}
	
	/**
	 * Set a variable
	 * 
	 * @param mixed $index
	 * @param mixed $value
	 */
	public function __set($index, $value) {
		if (isset($this->data[$index]) === false) {
			throw new \Exception('Invalid index: ' . $index);
		}
		$this->data[$index] = $value;
	}
	
	/**
	 * Get a variable
	 * 
	 * @param mixed $index
	 * @return mixed
	 */
	public function __get($index) {
		if (isset($this->data[$index])) {
			return $this->data[$index];
		} else {
			return null;
		}
	}
	
	public function __isset($index) {
		return isset($this->data[$index]);
	}
	
	public function save() {
		Session::start();
		$_SESSION['input'] = $this;
	}
	
	public static function restore() {
		if (isset($_SESSION['input'])) {
			Registry::getInstance()->input = $_SESSION['input'];
			unset($_SESSION['input']);
		}
	}
}

?>