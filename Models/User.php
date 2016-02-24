<?php namespace Models;

use Application\Registry;
use Application\Exceptions\ValidationException;
use DAL;

class User {
	private $id = -1;
	private $user;
	private $mail;
	private $admin = false;

	public function __construct() {
	}

	public static function load($userId) {
		$qb = new DAL\QueryBuilder();
		$qb->table('users')->where('id = ?', [[$userId, \PDO::PARAM_INT]]);
		return $qb->query(['id', 'user', 'mail', 'admin'])->fetchObject(__CLASS__);
	}

	public static function loadUser($user) {
		$qb = new DAL\QueryBuilder();
		$qb->table('users')->where('user = ?', [$user]);
		return $qb->query(['id', 'user', 'mail', 'admin'])->fetchObject(__CLASS__);
	}

	public function save() {
		$contentValues = [
				'user' => $this->user,
				'mail' => $this->mail,
				'admin' => $this->admin
		];
		if ($this->id < 0) {
			$this->insert($contentValues);
		} else {
			$this->update($contentValues);
		}
	}

	private function insert($contentValues) {
		Registry::getInstance()->db->beginTransaction();
		try {
				$qb = new DAL\QueryBuilder();
				$this->id = $qb->table('users')->insert($contentValues);

				Registry::getInstance()->db->commit();
		} catch (\PDOException $e) {
			Registry::getInstance()->db->rollback();
			throw $e;
		}
	}

	private function update($contentValues) {
		Registry::getInstance()->db->beginTransaction();
		try {
			$qb = new DAL\QueryBuilder();
			$qb->table('users')->where('id = :id', ['id' => [$this->id, \PDO::PARAM_INT]])->update($contentValues);

			Registry::getInstance()->db->commit();
		} catch (\PDOException $e) {
			Registry::getInstance()->db->rollback();
			throw $e;
		}
	}

	public function getId() {
		return $this->id;
	}

	public function getUser() {
		return $this->user;
	}

	public function setUser($value) {
		if (empty($value)) {
			throw new ValidationException(_('User is required.'));
		}

		$this->user = $value;
	}

	public function getMail() {
		return $this->user_mail;
	}

	public function setMail($value) {
		if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
			throw new ValidationException(_('Invalid E-Mail address.'));
		}
		$this->mail = $value;
	}

	public function isAdmin() {
		return (bool) $this->admin;
	}
}

?>
