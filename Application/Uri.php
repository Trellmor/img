<?php namespace Application;

use Application\Exceptions\UriException;

class Uri {
	private static $base = null;
	
	private $path;
	private $params = [];
	
	public static function getBase() {
		if (static::$base == null) {
			static::generateBaseUri();
		}
		
		return static::$base;
	}
	
	/**
	 * Create new Uri instance with specific path
	 * 
	 * @param string $path
	 * @return Uri instance
	 */
	public static function to($path) {
		$uri = new Uri();
		return $uri->path($path);
	}
	
	/**
	 * Create new URI instance to the current page
	 * 
	 * @return Uri instance
	 */
	public static function currentPage() {
		$uri = static::to(static::detectPath());
		foreach ($_GET as $k => $v) {
			$uri->param($k, $v);
		}
		
		return $uri;
	}
	
	/**
	 * Set the URI path
	 * 
	 * @param string $path
	 * @return Uri instance
	 */
	public function path($path) {
		$path = explode('/', $path);
		for ($i = 0; $i < count($path); $i++) {
			$path[$i] = urlencode($path[$i]);
		}		
		
		$this->path = implode('/', $path);
		return $this;
	}
	
	/**
	 * Get the path component
	 * 
	 * @return string Path
	 */
	public function getPath() {
		return Uri::pathTo($this->path);
	}
	
	/**
	 * Set a query param
	 * 
	 * Name and value will be url encoded with rawurlencode 
	 * 
	 * @param string $param Param name
	 * @param string $value
	 */
	public function param($param, $value) {
		if ($value !== null) {
			$param = rawurlencode($param);
			$value = rawurlencode($value);
			$this->params[$param] = $value;
		} else {
			if (isset($this->params[$param])) {
				unset($this->params[$param]);
			}
		}
		return $this;
	}
	
	/**
	 * Convert to string
	 * 
	 * @return string
	 */
	public function __toString() {
		if (static::$base == null) {
			static::generateBaseUri();
		}
		
		$base = static::$base;
		
		if (Registry::getInstance()->config['uri']['script']) {
			$base .= 'index.php/';
		}
		
		$uri = rtrim($base . ltrim($this->path, '/'), '/') . '/';
		if (count($this->params) > 0) {
			$uri .= '?';
			$query_string = '';
			foreach ($this->params as $param => $value) {
				if ($query_string != '') {
					$query_string .= '&';
				}
				$query_string .= $param . '=' . $value;
			}
			$uri .= $query_string;
		}
		return $uri;
	}
	
	/**
	 * Generate a routable path 
	 * 
	 * @param string $path
	 * @return string Formatted path
	 */
	public static function pathTo($path) {
		return trim($path, '/') . '/';
	}
	
	/**
	 * Detect the current requested path
	 * 
	 * @throws UriException
	 * @return string
	 */
	public static function detectPath() {
		if (isset($_SERVER['REQUEST_URI']) === false) {
			throw new UriException('REQUEST_URI not set');
		}
		
		return static::detectPathFrom($_SERVER['REQUEST_URI']);
	}
	
	private static function detectPathFrom($uri) {
		$uri = static::removeQuery($uri);
		$uri = urldecode($uri);		
		$uri = static::removeScript($uri);
		
		return rtrim(ltrim($uri, '/'), '/') . '/';
	}
	
	/**
	 * Parse an URI string
	 * 
	 * @param string $uri
	 * @return Uri instance
	 */
	public static function parse($uri) {
		$uri = static::to(static::detectPathFrom(parse_url($uri, PHP_URL_PATH)));
		
		$query = array();
		parse_str(parse_url($uri, PHP_URL_QUERY), $query);
		foreach ($query as $k => $v) {
			$uri->param($k, $v);
		}
		return $uri;
	}
	
	private static function generateBaseUri() {
		$config = Registry::getInstance()->config['uri'];
		
		$base = ($config['scheme'] != null) ? $config['scheme'] : 'http';
		$base .= '://';
		
		$base .= ($config['host'] != null) ? $config['host'] : $_SERVER['HTTP_HOST'];
		
		if ($config['port'] != null) $base .= ':' . $config['port'];
		
		$base .= rtrim('/' . ltrim($config['path'], '/'), '/') . '/';
		
		static::$base = $base;  
	}
	
	private static function removeScript($uri) {
		if (isset($_SERVER['SCRIPT_NAME']) === false) {
			return $uri;
		}
		
		//Normalize
		$uri = '/' . ltrim($uri, '/');
		
		$dir = dirname($_SERVER['SCRIPT_NAME']);
		if (($pos = strpos($uri, $dir)) === 0) {
			$uri = substr($uri, strlen($dir));
		}
		
		$script = basename($_SERVER['SCRIPT_NAME']);
		if (($pos = strpos($uri, $script)) === 1) {
			$uri = substr($uri, strlen($script) + 1);
		}
		
		return $uri;
	}
	
	private static function removeQuery($uri) {
		if (($pos = strpos($uri, '?')) !== false) {
			$uri = substr($uri, 0, $pos);
		}
		return $uri;
	}
}

?>