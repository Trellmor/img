<?php

error_reporting(E_ALL);

header('Content-Type: text/html; charset=UTF-8');

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
$location = explodE('/', $name);
for ($i = 0; $i < count($location); $i++) {
	$location[$i] = rawurlencode($location[$i]);
}
$location = implode('/', $location);

if (!move_uploaded_file_save($img['tmp_name'], $name)) {
	unlink_safe($img['tmp_name']);
	errorMsg('Can\'t move uploaded file.');
}

/**
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
 ' . escapeshellarg($name) . ' -thumbnail ' . $preview_width . 'x' . $preview_height . ' \\
 -unsharp 0x.5 ' . escapeshellarg($preview));

$db = new sqlite('lib/db.sqlite');

$db->exec("INSERT INTO images (
 location,
 path,
 ip,
 time,
 original_name
) VALUES (
 '" . $db->escape($location) . "',
 '" . $db->escape($name) . "',
 '" . ip2long($_SERVER['REMOTE_ADDR']) . "',
 '" . time() . "',
 '" . $db->escape($img['name']) . "'
);" );
$res = $db->query("SELECT last_insert_rowid() as id;");
$row = $db->fetch($res);
$id = $row['id'];

/**
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
		$row = $db->fetch($db->query("SELECT ROWID as id FROM tags WHERE tag = '" . $db->escape(strtolower($tag)) . "'"));
		if (!$row) {
			$db->exec("INSERT INTO tags (tag, text) VALUES ('" . $db->escape(strtolower($tag)) . "', '" . $db->escape($tag) . "');");
			$row = $db->fetch($db->query("SELECT last_insert_rowid() as id;"));
		}
		$sql .= "INSERT INTO imagetags (image, tag) VALUES('" . $id . "', '" . $row['id'] . "');\n";
		$sql .= "UPDATE tags SET count = count + 1 WHERE ROWID = '" . $row['id'] . "';\n";
	}
	$sql .= "COMMIT;";
	$db->exec($sql);
}

header('Location: ' . url() . 'image.php?i=' . urlnumber_encode($id));
errorMsg('Image saved.<br /><br /><a href="' . url() . 'image.php?i=' . urlnumber_encode($id) . '">Continue</a>');

?>