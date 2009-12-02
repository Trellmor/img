<?php

error_reporting(E_ALL);

require_once('lib/functions.php');
require_once('lib/config.php');
require_once('lib/class.sqlite.php');

if (!isset($_POST['submit'])) {
	errorMsg('No file uploaded.');
}

/**
 * ["name"]
 * ["type"]		- mime type
 * ["tmp_name"]	- temporary file location
 * ["error"]	- upload error
 * ["size"]		- Size in bytes
 */
$img = $_FILES['image'];

if ($img['error']) {
	unlink_safe($img['tmp_name']);
	errorMsg('Image uplaod error.');
}

if ($img['size'] > $maxsize) {
	unlink_safe($img['tmp_name']);
	errorMsg('Image too big.');
}

/**
 * [0]			- width
 * [1]			- geight
 * [2]			- IMAGETYPE_XXX
 + [3]			- Text string with width and height
 * ["bits"]
 * ["channels"]
 * ["mime"]		- Mime type
 */

$info = getimagesize($img['tmp_name']);

if (!isset($mime[$info['mime']])) {
	unlink_safe($img['tmp_name']);
	errorMsg('Imagetype not allowed.');
}

$name = explode('.', $img['name']);

if(count($name) < 2) {
	$name = $name[0] . '.' . $mime[$info['mime']];
} else {
	$name[count($name) - 1] = $mime[$info['mime']];
	$name = implode('.', $name);
}

$name = str_replace('//', '/', checkExists($imgdir . '/' . $name));

if (!move_uploaded_file_save($img['tmp_name'], $name)) {
	unlink_safe($img['tmp_name']);
	errorMsg('Can\'t move uploaded file.');
}

/**
 * Create preview
 * 
 * We use imagemagick because it suüüprts a broad range of file
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
 ' . escapeshellarg($name) . ' -auto-orient \\
 -thumbnail ' . $preview_width . 'x' . $preview_height . ' -unsharp 0x.5 \\
 ' . escapeshellarg($preview));

$db = new sqlite('lib/db.sqlite');

$db->exec("INSERT INTO images (
 location,
 ip,
 time
) VALUES (
 '" . $db->escape($name) . "',
 '" . ip2long($_SERVER['REMOTE_ADDR']) . "',
 '" . time() . "');" );
$res = $db->query("SELECT last_insert_rowid() as id;");
$row = $db->fetch($res);
header('Location: ' . url() . '?i=' . urlnumber_encode($row['id']));
errorMsg('Image saved.<br /><br /><a href="' . url() . '?i=' . $row['id'] . '">Continue</a>');

?>