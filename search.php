<?php

require_once('lib/functions.php');

if (isset($_GET['q'])) {
	require_once('lib/class.sqlite.php');
	$db = new sqlite('lib/db.sqlite');
	
	$search = explode(' ', $_GET['q']);
	$opt = ' and ';
	
	$sql = "SELECT
 i.ROWID as id,
 i.location as name,
 i.original_name,
 t.text
FROM
 images i,
 tags t,
 imagetags it
WHERE
 (";

	foreach ($search as $s) {
		$s = trim($s);
		$sql .= " t.tag LIKE '" . $db->escape($s) . "' " . $opt;
	}
	$sql = substr($sql, 0, -(strlen($opt)));
	$sql.= "
 ) and
 it.tag = t.ROWID and
 i.ROWID = it.image;";
	
	$res = $db->query($sql);
	$images = '';
	$tag_text = '';
	while ($row = $db->fetch($res)) {
		$tag_text = $row['text'];
		$preview = dirname($row['name']) . '/preview/' . basename($row['name']);
		$images .= '<div class="previewimage"><a href="' . $row['name'] . '" class="lightbox" rel="lightbox.tag"><img src="' . $preview . '" alt="' . htmlentities($row['original_name']) . '" /></a><br />' . "\n";
		$images .= '<a href="image.php?i=' . urlnumber_encode($row['id']) . '">Show</a></div>' . "\n";
	}
	outputHTML('<h2>' . one_wordwrap(htmlentities($tag_text), 5, '<wbr />') . '</h2>' . $images . '<br style="clear: both;" />', array('title' => 'Search: ' . htmlentities($_GET['q']), 'lightbox' => true));
	
} else {
	$header = '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script>';
	$header .= '<script type="text/javascript">';
	$header .= "$(document).ready(function() {
					$('#advanced_link').click(function(e) {
						e.preventDefault();
						$('#advanced').slideToggle('slow');
					})
				});";
	$header .= '</script>';
	
	$output = '<h2>Search</h2>
<form action="search.php" method="get">
<div id="search">
	<input type="text" name="q" size="40" id="inputsearch" /><br />&nbsp;
</div>
</form>
';
	
	outputHTML($output, array('title' => 'Search', 'header' => $header));
}

?>