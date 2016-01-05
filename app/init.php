<?php namespace Application;

if (version_compare(PHP_VERSION, '5.4') < 0) {
	die('PHP >= 5.4 required');
}

/**
 * Unregister globals
 */
if (ini_get('register_globals')) {
	$sg = array($_REQUEST, $_SERVER, $_FILES);
	
	foreach ($sg as $global) {
		foreach (array_keys($global) as $key) {
			unset(${$key});
		}
	}
}

/**
 * Remove magic quotes
 */
if (get_magic_quotes_gpc()) {
	$gpc = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
	
	array_walk_recursive($gpc, function(&$value) {
		$value = stripslashes($value);
	});
}

/**
 * Autoloader
 */
require_once APP_ROOT . '/app/autoloader.php';

/**
 * Load application config
 */
require_once APP_ROOT . '/app/config.php';
if (file_exists(APP_ROOT . '/app/localconfig.php')) {
	require_once APP_ROOT . '/app/localconfig.php';
}

/**
 * Initialize autoloader
 */
$autoloader = new Autoloader(APP_ROOT);
$autoloader->addNamespace('\\', APP_ROOT);
$autoloader->addNamespace('Application\\', APP_ROOT . '/app');
$autoloader->addNamespace('DAL', APP_ROOT . '/DAL');
$autoloader->register();
Registry::getInstance()->autoloader = $autoloader;

/**
 * Initialize database connection
 */
$config['database']['options'] += [\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ, \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION];
Registry::getInstance()->db = new \PDO($config['database']['dsn'], $config['database']['username'], 
		$config['database']['password'], $config['database']['options']);
unset($config['database']['username']);
unset($config['database']['password']);
Registry::getInstance()->db->exec('PRAGMA foreign_keys = ON');

Registry::getInstance()->config = $config;

/**
 * Initialize routing
 */
Registry::getInstance()->router = new Router();
require_once APP_ROOT . '/controllers/routes.php';

/**
 * Internationalization
 */
setlocale(LC_ALL, Registry::getInstance()->config['language']. '.utf8');
bindtextdomain('default', APP_ROOT . '/locale');
bind_textdomain_codeset('default', 'UTF-8');
textdomain('default');

/**
 * Session
 */
Session::start();
Input::restore();

/**
 * Route URL
 */
if (!Registry::getInstance()->router->route($_SERVER['REQUEST_METHOD'], Uri::detectPath())) {
	//No valid route found
	http_response_code(404);
	echo '<h1>Page not found.</h1>';
}

?>