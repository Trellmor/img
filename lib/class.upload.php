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
require_once(__DIR__ . '/functions.php');

class UploadException extends ImgException {};

class upload {
	private $time;
	private $img = '';
	private $mime;
	private $name = '';
	private $dir;
	private $pdo = NULL;
	private $tags = '';
	private $preview_width = 150;
	private $preview_height = 150;

	public function __construct()
	{
		$this->time = time();
		$this->mime = array();
		$this->dir = __DIR__ . '../';
	}

	public function __destruct() {
		$this->pdo = NULL;
	}

	public function image($img = NULL)
	{
		$return = $this->img;
		if ($img !== NULL) $this->img = $img;
		return $return;
	}

	public function mimeTypes($mime = NULL)
	{
		$return = $this->mime;
		if ($mime !== NULL) $this->mime = $mime;
		return $return;
	}

	public function name($name = NULL)
	{
		$return = $this->name;
		if ($name !== NULL) $this->name = $name;
		return $return;
	}

	public function dir($dir = NULL)
	{
		$return = $this->dir;
		if ($dir !== NULL) $this->dir = $dir;
		return $return;
	}

	public function pdo(PDO $pdo = NULL)
	{
		$return = $this->pdo;
		if ($pdo !== NULL) $this->pdo = $pdo;
		return $return;
	}

	public function tags($tags = NULL)
	{
		$return = $this->tags;
		if ($tags !== NULL) $this->tags = $tags;
		return $return;
	}

	public function time($time = NULL)
	{
		$return = $this->time;
		if ($time !== NULL) $this->time = $time;
		return $return;
	}

	public function preview_width($preview_width = NULL)
	{
		$return = $this->preview_width;
		if ($preview_width !== NULL) $this->preview_width = $preview_width;
		return $return;
	}

	public function preview_height($preview_height = NULL)
	{
		$return = $this->preview_height;
		if ($preview_height !== NULL) $this->preview_height = $preview_height;
		return $return;
	}

	public function save()
	{
		/*
		 * [0]			- width
		 * [1]			- geight
		 * [2]			- IMAGETYPE_XXX
		 + [3]			- Text string with width and height
		 * ["bits"]
		 * ["channels"]
		 * ["mime"]		- Mime type
		 */
		$info = getimagesize($this->img);

		// Check if this type of image is allowed
		if (!isset($this->mime[$info['mime']])) {
			unlink_safe($this->img);
			return false;
			//errorMsg('Imagetype not allowed.');
		}

		$md5 = md5_file($this->img);

		// Assign the correct extension for this image
		$name = str_replace('\'', '', $this->name);
		$name = explode('.', $name);

		if(count($name) < 2) {
			$name = $name[0] . '.' . $this->mime[$info['mime']];
		} else {
			$name[count($name) - 1] = $this->mime[$info['mime']];
			$name = implode('.', $name);
		}

		// Generate a URL save string to send to the browser
		$location = trim(str_replace('//', '/', $this->dir . '/'));
		
		// Choose the location for the file
		$name = trim(str_replace('//', '/', checkExists(realpath(__DIR__ . '/../') . '/' . $this->dir . '/' . $name)));
		$location .= basename($name);
		$location = explode('/', $location); 
		for ($i = 0; $i < count($location); $i++) {
			$location[$i] = rawurlencode($location[$i]);
		}
		$location = implode('/', $location);

		// Move the file to it's new location
		if (!isCLI()) {
			if (!move_uploaded_file_save($this->img, $name)) {
				unlink_safe($this->img);
				throw new UploadException('Can\'t move uploaded file.');
			}
		} else {
			if (!file_exists(dirname($name))) {
				if(!mkdir(dirname($name), 0777, true)) {
					throw new UploadException('Can\'t move file. (Directory create failed.)');
				}
			}

			if (!rename($this->img, $name)) {
				throw new UploadException('Can\'t move file. (File rename failed ' . $name . ')');
			}
		}

		/*
		 * Create preview
		 *
		 * We use imagemagick because it suports a broad range of file
		 * types
		 *
		 * Also, we call it directly with exec
		 *
		 * See http://www.imagemagick.org/Usage/thumbnails/ for more
		 * information about the commands used
		 */
		$preview = dirname($name) . '/preview/' . basename($name);
		if (!file_exists(dirname($preview))) mkdir(dirname($preview));
		exec('convert -define jpeg:size=' . $this->preview_width * 2 . 'x' . $this->preview_height * 2 . ' \\
		  \'' . $name . '\'[0] -thumbnail ' . $this->preview_width . 'x' . $this->preview_height . ' \\
		 -unsharp 0x.5 -strip \'' . $preview . '\'');

		// This is to make sure the image contains no privacy releated tags or anything
		exec('mogrify -strip \'' . $name . '\'');

		$user = (isset($_SESSION['openid_identity'])) ? $_SESSION['openid_identity'] : '';

		// Save image info
		$ip = (!isCLI()) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
		$stmt = DAL::Insert_Image($this->pdo, $location, $name, ip2long($ip), $this->time, $this->name, $user, $md5);
		$stmt->execute();
		$id = $this->pdo->lastInsertId();

		if (!empty($this->tags)) {
			$this->tagImg($id, $this->tags);
		}

		return true;
	}

	public function tagImg($id, $tags)
	{
		/*
		 * Tags
		 */
		$tags = explode(',', $tags);
		for ($i = 0; $i < count($tags); $i++) {
			$tags[$i] = trim($tags[$i]);
		}
		
		//Get old tags
		$stmt = DAL::Select_Image_Tags($this->pdo, $id);
		$stmt->execute();
		$tags = array_merge($tags, $stmt->fetchAll(PDO::FETCH_COLUMN, 1));
		
		$tags = $this->array_iunique($tags);
		$this->pdo->beginTransaction();
		try {
			foreach($tags as $tag) {
				if (empty($tag)) continue;
				// check if the tag already exists
				$stmt = DAL::Select_Tag_Id($this->pdo, strtolower($tag));
				$stmt->execute();
				if (($row = $stmt->fetch()) === false) {					
					// Tag doesn't exist
					$stmt = DAL::Insert_Tag($this->pdo, strtolower($tag), $tag);
					$stmt->execute(); 
					$tagid = $this->pdo->lastInsertId();
				} else {
					$tagid = $row['id'];
				}
				
				// Check if Imagetag exists
				$stmt = DAL::Select_ImageTag($this->pdo, $id, $tagid);
				$stmt->execute();
				if ($stmt->fetch() === false) {
					DAL::Insert_ImageTag($this->pdo, $id, $tagid)->execute();
				}
			}
			$this->pdo->commit();
		} catch (Exception $e) {
			$this->pdo->rollBack();
			throw new UploadException('SQL Error: ' . $e->getMessage());
		}
	}
	
	protected function in_iarray($str, $a){
		foreach ($a as $v) {
			if (strcasecmp($str, $v)==0) return true;
		}
		return false;
	}
	
	protected function array_iunique($a){
		$n = array();
		foreach ($a as $k=>$v) {
			if (!in_iarray($v, $n)) $n[$k]=$v;
		}
		return $n;
	}
}

?>
