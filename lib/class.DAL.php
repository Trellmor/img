<?php
/**
 * @package img.pew.cc
 * @author Daniel Triendl <daniel@pew.cc>
 * @version $Id$
 * @license http://opensource.org/licenses/agpl-v3.html
 */

/**
 * img.pew.cc Image Hosting
 * Copyright (C) 2009-2010  Daniel Triendl <daniel@pew.cc>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class PDOMultiStatement {
	private $pdo;
	private $statements = array();
	private $transaction = true;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}

	public function add(PDOStatement $statement) {
		$this->statements[] = $statement;
	}

	public function transaction($transaction = NULL) {
		$return = $this->transaction;
		if ($transaction !== NULL)
		$this->transaction = $transaction;
		return $return;
	}

	public function execute() {
		$return = (bool)count($this->statements);
		if ($this->transaction) $this->pdo->beginTransaction();
		try {
			for ($i = 0; $i < count($this->statements); $i++) {
				$return = $return && $this->statements[$i]->execute();
			}
			if ($this->transaction) $this->pdo->commit();
		} catch (PDOException $e) {
			if ($this->transaction) $this->pdo->rollBack();
			throw $e;
		}
		return $return;
	}
	
	public function setFetchMode($mode, $classname, array $ctorargs) {
		for ($i = 0; $i < count($this->statements); $i++) {
			$this->statements[$i]->setFetchMode($mode, $classname, $ctorargs);
		}
	}
	
	public function fetchAll() {
		$return = array();
		for ($i = 0; $i < count($this->statements); $i++) {
			$return = array_merge($return, $this->statements[$i]->fetchAll());
		}
		return $return;
	}
}

class DAL {
	private function __construct() {

	}

	public static function Select_User_Cookie(PDO $pdo, $user, $cookie) {
		$return = $pdo->prepare('SELECT user FROM users WHERE user = :user and cookie = :cookie;');
		$return->bindValue(':user', $user, PDO::PARAM_STR);
		$return->bindValue(':cookie', $cookie, PDO::PARAM_STR);

		return $return;
	}

	public static function Update_User_Lastlogin(PDO $pdo, $user) {
		$return = $pdo->prepare('UPDATE users SET last_login = :last_login WHERE user = :user;');
		$return->bindValue(':user', $user, PDO::PARAM_STR);
		$return->bindValue(':last_login', time(), PDO::PARAM_INT);

		return $return;
	}

	public static function Select_Image(PDO $pdo, $id) {
		$return = $pdo->prepare('SELECT id,
		 location as name, 
		 original_name, 
		 user,
		 time,
		 path
		FROM images WHERE id = :id;');
		$return->bindValue(':id', $id);

		return $return;
	}

	public static function Delete_Image(PDO $pdo, $id) {
		$return = new PDOMultiStatement($pdo);

		$stmt = $pdo->prepare('DELETE FROM images WHERE id = :id');
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$return->add($stmt);

		$stmt = $pdo->prepare('UPDATE tags Set count = count - 1 WHERE id IN (SELECT tag FROM imagetags WHERE image = :id);');
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$return->add($stmt);

		$stmt = $pdo->prepare('DELETE FROM tags WHERE count < 1');
		$return->add($stmt);

		$stmt = $pdo->prepare('DELETE FROM imagetags WHERE image = :id');
		$stmt->bindValue(':id', $id, PDO::PARAM_INT);
		$return->add($stmt);

		return $return;
	}

	public static function Insert_Image(PDO $pdo, $location, $path, $ip, $time, $original_name, $user, $md5) {
		$return = $pdo->prepare('INSERT INTO images (
		 location,
		 path,
		 ip,
		 time,
		 original_name,
		 user,
		 md5
		) VALUES (
		 :location,
		 :path,
		 :ip,
		 :time,
		 :original_name,
		 :user,
		 :md5
		);');

		$return->bindValue(':location', $location, PDO::PARAM_STR);
		$return->bindValue(':path', $path, PDO::PARAM_STR);
		$return->bindValue(':ip', $ip, PDO::PARAM_INT);
		$return->bindValue(':time', $time, PDO::PARAM_INT);
		$return->bindValue(':original_name', $original_name, PDO::PARAM_STR);
		$return->bindValue(':user', $user, PDO::PARAM_STR);
		$return->bindValue(':md5', $md5, PDO::PARAM_STR);

		return $return;
	}

	public static function Select_Image_Tags(PDO $pdo, $image) {
		$return = $pdo->prepare('SELECT t.id as id, t.tag as tag, t.text as text FROM tags t, imagetags it WHERE t.id = it.tag and it.image = :image;');
		$return->bindValue(':image', $image, PDO::PARAM_INT);

		return $return;
	}

	public static function Select_Tag_Id(PDO $pdo, $tag) {
		$return = $pdo->prepare('SELECT id FROM tags WHERE tag = :tag');
		$return->bindValue(':tag', $tag, PDO::PARAM_STR);

		return $return;
	}

	public static function Insert_Tag(PDO $pdo, $tag, $text) {
		$return = $pdo->prepare('INSERT INTO tags (tag, text) VALUES (:tag, :text);');
		$return->bindValue(':tag', $tag, PDO::PARAM_STR);
		$return->bindValue(':text', $text, PDO::PARAM_STR);

		return $return;
	}

	public static function Select_ImageTag(PDO $pdo, $image, $tag) {
		$return = $pdo->prepare('SELECT id FROM imagetags WHERE image = :image and tag = :tag;');
		$return->bindValue(':image', $image, PDO::PARAM_INT);
		$return->bindValue(':tag', $tag, PDO::PARAM_INT);

		return $return;
	}

	public static function Insert_ImageTag(PDO $pdo, $image, $tag) {
		$return = new PDOMultiStatement($pdo);
		$return->transaction(false);

		$stmt = $pdo->prepare('INSERT INTO imagetags (image, tag) VALUES(:image, :tag);');
		$stmt->bindValue(':image', $image, PDO::PARAM_INT);
		$stmt->bindValue(':tag', $tag, PDO::PARAM_INT);
		$return->add($stmt);

		$stmt = $pdo->prepare('UPDATE tags SET count = count + 1 WHERE id = :tag;');
		$stmt->bindValue(':tag', $tag, PDO::PARAM_INT);
		$return->add($stmt);

		return $return;
	}

	public static function Select_Images_By_Tags(PDO $pdo, array $tags, $offset = 0, $count = 2147483647) {
		$t = '';
		foreach ($tags as $tag) {
			if (!empty($t)) $t .= ',';
			$t .= $pdo->quote($tag, PDO::PARAM_STR);
		}

		$return = $pdo->prepare('SELECT
		 i.id as id,
		 i.location as name,
		 i.original_name as original_name
		FROM
		 images i 
		INNER JOIN imagetags it ON it.image = i.id
		INNER JOIN tags t on t.id = it.tag 
		WHERE t.tag in (' . $t . ')
		GROUP BY (i.id)
		HAVING COUNT(*) = :tag_count
		ORDER BY
		 i.time DESC
		LIMIT
		 ' . (int)$offset . ', ' . (int)$count . ';');
		$return->bindValue(':tag_count', count($tags), PDO::PARAM_INT);

		return $return;
	}

	public static function Count_Images_By_Tags(PDO $pdo, array $tags) {
		$t = '';
		foreach ($tags as $tag) {
			if (!empty($t)) $t .= ',';
			$t .= $pdo->quote($tag, PDO::PARAM_STR);
		}

		$pdo->beginTransaction();
		try {
			$stmt = $pdo->prepare('CREATE TEMP TABLE tempcount AS
			SELECT
			 count(i.id) as count
			FROM
			 images i 
			INNER JOIN imagetags it ON it.image = i.id
			INNER JOIN tags t on t.id = it.tag 
			WHERE t.tag in (' . $t . ')
			GROUP BY (i.id)
			HAVING COUNT(*) = :tag_count;');
			$stmt->bindValue(':tag_count', count($tags), PDO::PARAM_INT);
			$stmt->execute();
			$return = $pdo->query('SELECT count(*) from tempcount;')->fetchColumn(0);
			$pdo->query('DROP TABLE tempcount');
			$pdo->commit();
		} catch (PDOException $e) {
			$pdo->rollBack();
			throw $e;
		}

		return (int)$return;
	}
	
	public static function Select_Images_By_User(PDO $pdo, $user, $offset = 0, $count = 2147483647) {
		$return = $pdo->prepare('SELECT
		 id as id,
		 location as name,
		 original_name
		FROM
		 images
		WHERE
		 user = :user
		ORDER BY
		 time DESC
		LIMIT
		 ' . (int)$offset . ', ' . (int)$count . ';');
		$return->bindValue(':user', $user, PDO::PARAM_STR);
		
		return $return;
	}
	
	public static function Count_Images_By_User(PDO $pdo, $user) {
		$stmt = $pdo->prepare('SELECT
		 count(*)
		FROM
		 images
		WHERE
		 user = :user;');
		$stmt->bindValue(':user', $user, PDO::PARAM_STR);
		$stmt->execute();
		$return = $stmt->fetchColumn(0);

		return (int)$return;
	}

	public static function Select_Tags_By_Tags(PDO $pdo, array $tags) {
		$t = '';
		foreach ($tags as $tag) {
			if (!empty($t)) $t .= ',';
			$t .= $pdo->quote($tag, PDO::PARAM_STR);
		}

		$return = $pdo->prepare('SELECT
		 count(t.tag) as count,
		 t.id as id,
		 t.tag as tag,
		 t.text as text
		FROM
		 tags t
		INNER JOIN imagetags it on it.tag = t.id
		WHERE it.image in (SELECT
							i.id
						   FROM
		 					images i 
						   INNER JOIN imagetags it ON it.image = i.id
						   INNER JOIN tags t2 on t2.id = it.tag 
						   WHERE
						    t2.tag in (' . $t . ')
						   GROUP BY 
						    i.id
						   HAVING COUNT(*) = :tag_count)
		GROUP BY 
		 t.tag,
		 t.text
		ORDER BY
		 t.tag;');
		$return->bindValue(':tag_count', count($tags), PDO::PARAM_INT);

		return $return;
	}

	public static function Select_Tags(PDO $pdo, $limit = -1) {
		$sql = 'SELECT id, tag, text, count FROM tags ORDER BY count DESC, id DESC';
		$sql .= ($limit != -1) ? ' LIMIT ' . $limit . ';' : ';';
		return $pdo->prepare($sql);
	}

	public static function Select_Images_By_Ip_Time(PDO $pdo, $ip, $time) {
		$return = $pdo->prepare('SELECT
		 id,
		 location as name,
		 original_name as original_name
		FROM
		 images
		WHERE
		 ip = :ip and
		 time = :time;');
		$return->bindValue(':ip', $ip, PDO::PARAM_INT);
		$return->bindValue(':time', $time, PDO::PARAM_INT);

		return $return;
	}

	public static function Select_TagSuggestions(PDO $pdo, $tag) {
		$return = $pdo->prepare('SELECT text FROM tags WHERE tag LIKE :tag LIMIT 10;');
		$return->bindValue(':tag', strtolower($tag) . '%', PDO::PARAM_STR);

		return $return;
	}
	
	public static function Select_Images_Search(PDO $pdo, array $tags) {
		$return = new PDOMultiStatement($pdo);
		
		$sql = 'SELECT 
		 i.id as id,
		 i.location as name,
		 i.original_name as original_name,
		 i.user as user,
		 i.time as time
		FROM
		 images i,
		 imagetags it
		WHERE
		 i.id = it.image and
		 it.tag in (SELECT id FROM tags WHERE ';
		
		$condition = '';
		foreach ($tags as $tag) {
			if (!empty($condition)) $condition .= ' or ';
			$condition .= 'tag LIKE ' . $pdo->quote($tag, PDO::PARAM_STR);
		}
		
		$sql .= $condition . ' GROUP BY id);';
		$return->add($pdo->prepare($sql));
		
		$sql = 'SELECT
		 id,
		 location as name,
		 original_name,
		 user,
		 time
		FROM
		 images
		WHERE
		 ';
		$condition = '';
		foreach ($tags as $tag) {
			if (!empty($condition)) $condition .= ' or ';
			$condition .= 'original_name LIKE ' . $pdo->quote($tag, PDO::PARAM_STR);
		}
		$sql .= $condition;
		$return->add($pdo->prepare($sql));
		
		return $return;
	}

	public static function Delete_ImageTags(PDO $pdo, $image) {
		$return = new PDOMultiStatement($pdo);

		$stmt = $pdo->prepare('UPDATE tags SET count = count - 1 WHERE id IN (SELECT tag FROM imagetags WHERE image = :image);');
		$stmt->bindValue(':image', $image, PDO::PARAM_INT);
		$return->add($stmt);

		$stmt = $pdo->prepare('DELETE FROM imagetags WHERE image = :image');
		$stmt->bindValue(':image', $image, PDO::PARAM_INT);
		$return->add($stmt);

		return $return;
	}

	public static function Insert_User(PDO $pdo, $user, $cookie) {
		$return = $pdo->prepare('INSERT OR REPLACE INTO users (
		 user,
		 cookie,
		 last_login
		) VALUES (
		 :user,
		 :cookie,
		 :time
		);');
		$return->bindValue(':user', $user, PDO::PARAM_STR);
		$return->bindValue(':cookie', $cookie, PDO::PARAM_STR);
		$return->bindValue(':time', time(), PDO::PARAM_INT);

		return $return;
	}
}

?>