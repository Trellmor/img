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

require_once('lib/init.php');

if (!isset($_POST['submit'])) {
	errorMsg('No file uploaded.');
}

/*
 * ["name"]
 * ["type"]		- mime type
 * ["tmp_name"]	- temporary file location
 * ["error"]	- upload error
 * ["size"]		- Size in bytes
 */
header('Content-type: text/plain');
fixFilesArray($_FILES['image']);
$time = time();
$uploadcount = 0;

foreach ($_FILES['image'] as $img) {
	// Upload failed
	if ($img['error']) {
		unlink_safe($img['tmp_name']);
		continue;
		//errorMsg('Image uplaod error.');
	}

	// The image is to big
	if ($img['size'] > $maxsize) {
		unlink_safe($img['tmp_name']);
		continue;
		//errorMsg('Image too big.');
	}

	/*
	 * [0]			- width
	 * [1]			- geight
	 * [2]			- IMAGETYPE_XXX
	 + [3]			- Text string with width and height
	 * ["bits"]
	 * ["channels"]
	 * ["mime"]		- Mime type
	 */
	$info = getimagesize($img['tmp_name']);

	// Check if this type of immage is allowed
	if (!isset($mime[$info['mime']])) {
		unlink_safe($img['tmp_name']);
		continue;
		//errorMsg('Imagetype not allowed.');
	}
	
	$md5 = md5_file($img['tmp_name']);

	// Assign the correct extension for this image
	$name = (get_magic_quotes_gpc()) ? stripslashes($img['name']) : $img['name'];
	$name = str_replace('\'', '', $name);
	$name = explode('.', $name);

	if(count($name) < 2) {
		$name = $name[0] . '.' . $mime[$info['mime']];
	} else {
		$name[count($name) - 1] = $mime[$info['mime']];
		$name = implode('.', $name);
	}

	// Choose the location for the file
	$name = trim(str_replace('//', '/', checkExists($imgdir . '/' . $name)));
	
	// Generate a URL save string to send to the browser
	$location = explode('/', $name);
	for ($i = 0; $i < count($location); $i++) {
		$location[$i] = rawurlencode($location[$i]);
	}
	$location = implode('/', $location);
	
	// Move the file to it's new location
	if (!move_uploaded_file_save($img['tmp_name'], $name)) {
		unlink_safe($img['tmp_name']);
		errorMsg('Can\'t move uploaded file.');
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
	exec('convert -define jpeg:size=' . $preview_width * 2 . 'x' . $preview_height * 2 . ' \\
	  \'' . $name . '\'[0] -thumbnail ' . $preview_width . 'x' . $preview_height . ' \\
	 -unsharp 0x.5 -strip \'' . $preview . '\'');
	
	// This is to make sure the image contains no privacy releated tags or anything
	exec('mogrify -strip \'' . $name . '\'');
	
	$user = (isset($_SESSION['openid_identity'])) ? $_SESSION['openid_identity'] : '';
	
	// Save image info
	$db->exec("INSERT INTO images (
	 location,
	 path,
	 ip,
	 time,
	 original_name,
	 user,
	 md5
	) VALUES (
	 '" . $db->escape($location) . "',
	 '" . $db->escape($name) . "',
	 '" . ip2long($_SERVER['REMOTE_ADDR']) . "',
	 '" . $time . "',
	 '" . $db->escape($img['name']) . "',
	 '" . $db->escape($user) . "',
	 '" . $db->escape($md5) . "'
	);" );
	$res = $db->query("SELECT last_insert_rowid() as id;");
	$row = $db->fetch($res);
	$id = $row['id'];

	/*
	 * Tags
	 */
	if (isset($_POST['tags'])) {
		$tags = explode(',', $_POST['tags']);
		for ($i = 0; $i < count($tags); $i++) {
			$tags[$i] = trim($tags[$i]);
		}
		$tags = array_unique($tags);
		$sql = "BEGIN;\n";
		foreach ($tags as $tag) {
			if (empty($tag)) continue;
			// check if the tag already exists
			$res = $db->query("SELECT ROWID as id FROM tags WHERE tag = '" . $db->escape(strtolower($tag)) . "'");
			if ($db->numrows($res) == 0) {
				$db->exec("INSERT INTO tags (tag, text) VALUES ('" . $db->escape(strtolower($tag)) . "', '" . $db->escape($tag) . "');");
				$row = $db->fetch($db->query("SELECT last_insert_rowid() as id;"));
			} else {
				$row = $db->fetch($res);
			}
			// Save the tag for this image and update tag counter
			$sql .= "INSERT INTO imagetags (image, tag) VALUES('" . $id . "', '" . $row['id'] . "');\n";
			$sql .= "UPDATE tags SET count = count + 1 WHERE ROWID = '" . $row['id'] . "';\n";
		}
		$sql .= "COMMIT;";
		// Commit all changes
		$db->exec($sql);
	}
	$uploadcount++;
}

if ($uploadcount > 0) {
	// Redirect to image
	header('Location: ' . url() . 'browse.php?ip=' . ip2long($_SERVER['REMOTE_ADDR']) . '&time=' . $time);
	errorMsg('Image saved.<br /><br /><a href="' . url() . 'browse.php?ip=' . ip2long($_SERVER['REMOTE_ADDR']) . '&time=' . $time . '">Continue</a>');
} else {
	errorMsg('No images uploaded.');
}

?>