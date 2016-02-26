<?php
namespace Application;

use Model\User;

class ImgApplication extends BaseApplication {

	protected function init() {
		parent::init();
		$this->openDB();
		$this->i18n();
		$this->loadUser();
		$this->loadRoutes();
	}

	/**
	 * Initialize database connection
	 */
	private function openDB() {
		$config = Registry::getInstance()->config;
		$config['database']['options'] += [
				\PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_OBJ,
				\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
		];
		Registry::getInstance()->db = new \PDO($config['database']['dsn'], $config['database']['username'], $config['database']['password'], $config['database']['options']);
		Registry::getInstance()->db->exec('PRAGMA foreign_keys = ON');
	}

	/**
	 * Internationalization
	 */
	private function i18n() {
		setlocale(LC_ALL, Registry::getInstance()->config['language'] . '.utf8');
		bindtextdomain('default', APP_ROOT . '/locale');
		bind_textdomain_codeset('default', 'UTF-8');
		textdomain('default');
	}

	private function loadRoutes() {
		require_once Registry::getInstance()->app_root . '/Controller/routes.php';
	}

	private function loadUser() {
		if (isset($_SESSION['user_id'])) {
			$user = User::load($_SESSION['user_id'], true);
			Registry::getInstance()->user = $user;
		}
	}
}
