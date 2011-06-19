<?php
/**
 * @package img.pew.cc
 * @author Daniel Triendl <daniel@pew.cc>
 * @version $Id: upload.php 100 2011-01-06 23:17:53Z daniel $
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

require_once(__DIR__ . '/class.sqlite.php');

class UploadException extends Exception {};

class upload {
	private $time;
	private $img = '';
	private $mime;
	private $name = '';
	private $dir;
	private $db = NULL;
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
		$db = NULL;
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

	public function db($db = NULL)
	{
		$return = $this->db;
		if ($db !== NULL) $this->db = $db;
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

		// Choose the location for the file
		$name = trim(str_replace('//', '/', checkExists($this->dir . '/' . $name)));

		// Generate a URL save string to send to the browser
		$location = explode('/', $name);
		for ($i = 0; $i < count($location); $i++) {
			$location[$i] = rawurlencode($location[$i]);
		}
		$location = implode('/', $location);

		// Move the file to it's new location
		if (isCLI()) {
			if (!move_uploaded_file_save($this->img, $name)) {
				unlink_safe($this->img);
				throw new UploadException('Can\' move uploaded file.');
			}
		} else {
			if (!file_exists($this->dir)) {
				if(!mkdir($this->dir, 0777, true)) {
					throw new UploadException('Can\'t move file. (Directory create failed.)');
				}
			}

			if (!rename($this->img, $name)) {
				throw new UploadException('Can\'t move file. (File rename failed.)');
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
		$ip = (isCLI()) ? $_SERVER['REMOTE_ADDR'] : '127.0.0.1';
		$this->db->exec("INSERT INTO images (
		 location,
		 path,
		 ip,
		 time,
		 original_name,
		 user,
		 md5
		) VALUES (
		 '" . $this->db->escape($location) . "',
		 '" . $this->db->escape($name) . "',
		 '" . ip2long($ip) . "',
		 '" . $this->time . "',
		 '" . $this->db->escape($this->name) . "',
		 '" . $this->db->escape($user) . "',
		 '" . $this->db->escape($md5) . "'
		);" );
		$res = $this->db->query("SELECT last_insert_rowid() as id;");
		$row = $this->db->fetch($res);
		$id = $row['id'];

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
		$tags = array_unique($tags);
		$sql = "BEGIN;\n";
		foreach ($tags as $tag) {
			if (empty($tag)) continue;
			// check if the tag already exists
			$res = $this->db->query("SELECT ROWID as id FROM tags WHERE tag = '" . $this->db->escape(strtolower($tag)) . "'");
			if ($this->db->numrows($res) == 0) {
				$this->db->exec("INSERT INTO tags (tag, text) VALUES ('" . $this->db->escape(strtolower($tag)) . "', '" . $this->db->escape($tag) . "');");
				$row = $this->db->fetch($this->db->query("SELECT last_insert_rowid() as id;"));
			} else {
				$row = $this->db->fetch($res);
			}
			// Save the tag for this image and update tag counter
			$sql .= "INSERT INTO imagetags (image, tag) VALUES('" . $id . "', '" . $row['id'] . "');\n";
			$sql .= "UPDATE tags SET count = count + 1 WHERE ROWID = '" . $row['id'] . "';\n";
		}
		$sql .= "COMMIT;";
		// Commit all changes
		$this->db->exec($sql);
	}
}

?>