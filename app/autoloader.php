<?php namespace Application;

class Autoloader {
	protected $root = '/';
	protected $prefixes = array();
	
	public function __construct($root) {		
		$this->root = rtrim($root, '/') . '/';
	}
		
	public function addNamespace($prefix, $dir, $prepend = false) {
		$prefix = trim($prefix, '\\') . '\\';
		
		if (isset($this->prefixes[$prefix]) === false) {
			$this->prefixes[$prefix] = array();
		}
		
		$dir = rtrim($dir, '/') . '/';
		if ($prepend) {
			array_unshift($this->prefixes[$prefix], $dir);	
		} else {
			$this->prefixes[$prefix][] = $dir; 
		}
		
	}
	
	public function register() {
		spl_autoload_register(array($this, 'loadClass'));
	}
	
	public function loadClass($class) {
		$prefix = $class;
				
		while (false !== $pos = strrpos($prefix, '\\')) {
			$prefix = substr($class, 0, $pos + 1);
			
			$relative_class = substr($class, $pos + 1);
			
			$mapped_file = $this->loadMappedFile($prefix, $relative_class);
			if ($mapped_file) {
				return $mapped_file;
			}
			
			$prefix = rtrim($prefix, '\\');
		}
		
		$mapped_file = $this->loadMappedFile('\\', $class);
		if ($mapped_file) {
			return $mapped_file;
		}
		
		return false;
	}
	
	protected function loadMappedFile($prefix, $relative_class) {
		if (isset($this->prefixes[$prefix]) === false) {
			return false;
		}
		
		foreach ($this->prefixes[$prefix] as $dir) {		
			$file = $dir . str_replace('\\', '/', strtolower($relative_class)) . '.php';
				
			if ($this->requireFile($file)) {
				return $file;
			}
		}
		
		return false;
	}
	
	protected function requireFile($file) {
		if (file_exists($file)) {
			require $file;
			return true;
		}
		return false;
	}
}
