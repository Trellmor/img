<?php


if (isset($_GET['tag'])) {
	require_once('lib/class.sqlite.php');
	
	$db = new sqlite('lib/db.sqlite');
	
	$sql = "SELECT text FROM tags WHERE tag LIKE '" . $db->escape($_GET['tag']) . "%' LIMIT 10;";
	
	$res = $db->query($sql);
	$tags = array();
	
	while ($row = $db->fetch($res)) {
		$tags[] = $row['text'];
	}
	
	echo json_encode($tags);
}

?>