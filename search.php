<?php

require_once('lib/functions.php');

if (isset($_GET['q'])) {
	require_once('lib/class.sqlite.php');
	$db = new sqlite('lib/db.sqlite');
	
	$search = explode(' ', $_GET['q']);
	
	// Get matching tags
	$tags = array();
	$sql = "SELECT ROWID as id, text FROM tags WHERE ";
	$clause = '';
	$count = 0;
	foreach ($search as $s) {
		$s = trim($s);
		if (!empty($s)) {
			if (!empty($clause)) $clause .= ' or ';
			$clause .= "tag LIKE '%" . $db->escape($s) . "%'";
			$count++;
		}
	}
	if (empty($clause)) errorMsg("Invalid search string.");
	$sql .= $clause;
	
	$res = $db->query($sql);
	
	if (!$db->numrows($res)) errorMsg("No results found.");
	
	$tags = array();
	while ($row = $db->fetch($res)) {
		$tags[] = $row['id'];
	}
	
	// Select all images that contain all of these tags
	$sql = "SELECT image FROM imagetags WHERE tag IN ('" . implode("', '", $tags) . "')";
	$res = $db->query($sql);
	$images = array();
	while ($row = $db->fetch($res)) {
		if (!isset($images[$row['image']])) $images[$row['image']] = 1;
		else $images[$row['image']]++;
	}
	
	// Order by relevance
	arsort($images);
	// Image must contain all tags
	//$images = array_keys($images, $count);
	$images = array_keys($images);
	
	// Get the results
	$sql = "SELECT ROWID as id, location, original_name from images WHERE ROWID IN ('" . implode("', '", $images) . "');";
	$res = $db->query($sql);
	if (!$db->numrows($res)) errorMsg("No results found.");
	// Save the results on an array;
	$full_images = array();
	while ($row = $db->fetch($res)) {
		$full_images[$row['id']] = $row;
	}
	
	// Output images ordered by relevance
	$output = '';
	foreach ($images as $i) {
		$preview = dirname($full_images[$i]['location']) . '/preview/' . basename($full_images[$i]['location']);
		$output .= '<div class="previewimage"><a href="' . $full_images[$i]['location'] . '" class="lightbox" rel="lightbox.search"><img src="' . $preview . '" alt="' . htmlentities($full_images[$i]['original_name']) . '" /></a><br />' . "\n";
		$output .= '<a href="image.php?i=' . urlnumber_encode($i) . '">Show</a></div>' . "\n";
	}

	outputHTML('<h2>' . one_wordwrap(htmlentities($_GET['q']), 5, '<wbr />') . '</h2>' . $output . '<br style="clear: both;" />', array('title' => 'Search: ' . htmlentities($_GET['q']), 'lightbox' => true));
	
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