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

require_once(__DIR__ . '/class.DAL.php');

class BrowseException extends ImgException {};

class image {
	public $id;
	public $name;
	public $original_name;
	public $user;
	public $time;
	public $path;
	public $count = 1;
	private $pdo;
	
	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;
	}
	
	public function pdo(PDO $pdo = NULL) {
		$return = $this->pdo;
		if ($pdo !== null) 
			$this->pdo = $pdo;
		return $return;
	}
	
	public function getPreview() {
		return dirname($this->name) . '/preview/' . basename($this->name);
	}
	
	public function delete() {
		$stmt = DAL::Delete_Image($this->pdo, $this->id);
		$return = $stmt->execute();
		if ($return) {
			unlink_safe($this->name);
			unlink_safe($this->getPreview());
		}
		return $return;
	}
}

class tag {
	public $id;
	public $tag;
	public $text;
	public $count;
}

class browse {
	private $pdo = NULL;
	protected $resultCount = 0;

	public function __construct(PDO $pdo) {
		$this->pdo = $pdo;		
	}
	
	public function pdo(PDO $pdo = NULL) {
		$return = $this->pdo;
		if ($pdo != NULL) $this->pdo = $pdo;
		return $return;
	}
	
	public function resultCount() {
		return $this->resultCount;
	}
	
	public function getImage($id) {
		$stmt = DAL::Select_Image($this->pdo, $id);
		$stmt->setFetchMode(PDO::FETCH_CLASS, 'image', array($this->pdo));
		$stmt->execute();
		$return = $stmt->fetch();
		if ($return === false) throw new BrowseException('Image not found.');
		return $return;
	}
	
	public function getImageTags($id) {
		$stmt = DAL::Select_Image_Tags($this->pdo, $id);
		$stmt->setFetchMode(PDO::FETCH_CLASS, 'tag');
		$stmt->execute();
		return $stmt->fetchAll();
	}
	
	public function getImagesByTags($tags, $offset = 0, $count = -1) {
		// prepare tag list
		$tags = $this->prepareTags($tags);
		if (count($tags) == 0) throw new BrowseException('Invalid tag count: ' . count($tags));
		
		// get Number of matched images
		$this->resultCount = DAL::Count_Images_By_Tags($this->pdo, $tags);

		// get Images
		$stmt = DAL::Select_Images_By_Tags($this->pdo, $tags);
		$stmt->setFetchMode(PDO::FETCH_CLASS, 'image', array($this->pdo));
		$stmt->execute();
		
		//generate Return array		
		$return = array();
		if ($count == -1) 
			$count = $this->resultCount;
		else 
			$count = $offset + $count;			
			
		for ($i = $offset; $i < $count; $i++) {
			$r = $stmt->fetch(PDO::FETCH_CLASS, PDO::FETCH_ORI_ABS, $i);
			if ($r === false) break;
			$return[] = $r;
		}			
		return $return;	
	}
	
	public function getImagesByUser($user, $offset = 0, $count = -1) {
		$this->resultCount = DAL::Count_Images_By_User($this->pdo, $user);
		
		$stmt = DAL::Select_Images_By_User($this->pdo, $user);
		$stmt->setFetchMode(PDO::FETCH_CLASS, 'image', array($this->pdo));
		$stmt->execute();
		
		//generate Return array		
		$return = array();
		if ($count == -1) 
			$count = $this->resultCount;
		else 
			$count = $offset + $count;			
			
		for ($i = $offset; $i < $count; $i++) {
			$r = $stmt->fetch(PDO::FETCH_CLASS, PDO::FETCH_ORI_ABS, $i);
			if ($r === false) break;
			$return[] = $r;
		}			
		return $return;	
	}
	
	public function getImagesByIpTime($ip, $time) {
		$stmt = DAL::Select_Images_By_Ip_Time($this->pdo, $ip, $time);
		$stmt->setFetchMode(PDO::FETCH_CLASS, 'image', array($this->pdo));
		$stmt->execute();
		return $stmt->fetchAll();
	}
	
	public function getTagListTags($tags) {
		// prepare tag list
		$tags = $this->prepareTags($tags);
		if (count($tags) == 0) throw new BrowseException('Invalid tag count: ' . count($tags));

		$stmt = DAL::Select_Tags_By_Tags($this->pdo, $tags);
		$stmt->setFetchMode(PDO::FETCH_CLASS, 'tag');
		$stmt->execute();
		
		return $stmt->fetchAll(PDO::FETCH_CLASS, 'tag');
	}
	
	public function getTagList($limit = -1) {
		$stmt = DAL::Select_Tags($this->pdo, $limit);
		$stmt->setFetchMode(PDO::FETCH_CLASS, 'tag');
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_CLASS, 'tag');
	}
	
	protected function prepareTags($tags) {
		$tags = explode(',', $tags);
		for ($i = 0; $i < count($tags); $i++) {
			$tags[$i] = trim($tags[$i]);
			if (empty($tags[$i])) {
				unset($tags[i]);
			}
		}
		array_unique($tags);
		return $tags;
	}
}

?>