<?php namespace Application;

class Route {
	const GET = 'GET';
	const POST = 'POST';
	
	private $method;
	private $class;
	private $function;
	private $url;	
	
	public function __construct($method, $class, $function, $url) {
		$this->method = $method;
		$this->class = $class;
		$this->function = $function;
		$this->url = str_replace('/', '\\/', $url);
	}
	
	public static function get($class, $function, $url) {
		$c = __CLASS__;
		return new $c(static::GET, $class, $function, $url);
	}
	
	public static function post($class, $function, $url) {
		$c = __CLASS__;
		return new $c(static::POST, $class, $function, $url);
	}
	
	/**
	 * Checks if the passed url matches the route. If it maches the class 
	 * will be created and the function called with the arguments extracted
	 * from the url
	 * 
	 * @param unknown_type $url
	 */
	public function matchUrl($method, $url) {
		if ($this->method == $method) {
			if (preg_match('/^' . $this->url . '$/', $url, $matches)) {
				$params = $this->prepareParams($matches);
				$this->execute($params);
				return true;
			}
			return false;
		}
	}
	
	protected function prepareParams($params) {
		unset($params[0]);
		return array_values($params);
	}
	
	protected function execute($params) {
		$instance = new $this->class;
		call_user_func_array(array($instance, $this->function), $params);
	}
}

class Router {
	private $routes = array();
	private $errorRoute = null;
	
	public function addRoute($route) {
		$this->routes[] = $route;
	}
	
	public function setErrorRoute($route) {
		$this->errorroute = $route;
	}
	
	public function route($method, $url) {
		foreach ($this->routes as $route) {
			if ($route->matchUrl($method, $url)) {
				return true;
			}
		}
		
		return false;
	}
}

?>