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
require_once(__DIR__ . '/lib/class.upload.php');

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
//header('Content-type: text/plain');
if (isset($_FILES['image'])) {
	fixFilesArray($_FILES['image']);
} elseif (isset($_FILES['file'])) {
	$_FILES['image'] = array($_FILES['file']);
} else {
	errorMsg('No file uploaded.');
}

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
	
	$upload = new upload();
	$upload->time($time);
	$upload->image($img['tmp_name']);
	$upload->mimeTypes($mime);
	$name = (get_magic_quotes_gpc()) ? stripslashes($img['name']) : $img['name'];
	$upload->name($name);
	$upload->pdo($pdo);
	$upload->preview_height($preview_height);
	$upload->preview_width($preview_width);
	if (isset($_POST['tags'])) {
		$upload->tags($_POST['tags']);
	}
	$upload->dir($imgdir);
	
	try {
		if ($upload->save()) {
			$uploadcount++;
		}
	} catch (UploadException $e) {
		errorMsg($e->getMessage());
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