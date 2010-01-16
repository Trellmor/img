<?php

error_reporting(E_ALL);

require_once('lib/functions.php');
require_once('lib/config.php');
require_once('lib/class.sqlite.php');

$db = new sqlite('lib/db.sqlite');

if (isset($_GET['tag'])) {

	$page = (isset($_GET['p']) && is_numeric($_GET['p'])) ? (int)$_GET['p'] : 1;
	$offset =  ($page - 1) * $pagelimit;
	
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
 t.tag = '" . $db->escape(urldecode($_GET['tag'])) . "' and
 it.tag = t.ROWID and
 i.ROWID = it.image
ORDER BY
 i.time DESC
LIMIT
 " . $offset . ", " . $pagelimit . ";";

	$res = $db->query($sql);
	$images = '';
	$tag_text = '';
	while ($row = $db->fetch($res)) {
		$tag_text = $row['text'];
		$preview = dirname($row['name']) . '/preview/' . basename($row['name']);
		$images .= '<div class="previewimage"><a href="' . $row['name'] . '" class="lightbox" rel="lightbox.tag"><img src="' . $preview . '" alt="' . htmlentities($row['original_name']) . '" /></a><br />' . "\n";
		$images .= '<a href="image.php?i=' . urlnumber_encode($row['id']) . '">Show</a></div>' . "\n";
	}
	
	$sql = "SELECT
 count(i.ROWID) as count
FROM
 images i,
 tags t,
 imagetags it
WHERE
 t.tag = '" . $db->escape(urldecode($_GET['tag'])) . "' and
 it.tag = t.ROWID and
 i.ROWID = it.image;";
	$row = $db->fetch($db->query($sql));
	
	// Generate page counter
	$pages = '<p id="pages">';
	for ($i = 1; $i <= ceil($row['count']/$pagelimit); $i++) {
		if ($i != $page) $pages .= '<a href="browse.php?tag=' . $_GET['tag'] . '&amp;p=' . $i . '">' . $i . '</a>';
		else $pages .= $i; 
		$pages .= ' &middot; ';
	}
	$pages = substr($pages, 0, -10) . '</p>';
	
	outputHTML('<h2>' . one_wordwrap(htmlentities($tag_text), 5, '&shy;') . '</h2>' . $images . '<br style="clear: both;" />' . $pages, array('title' => 'Tag: ' . htmlentities($tag_text), 'lightbox' => true));

} else {

	$sql = "SELECT tag, text, count FROM tags ORDER BY count DESC";
	$sql .= (isset($_GET['tags']) && $_GET['tags'] == 'all') ? ';' : ' LIMIT 100;';
	
	$res = $db->query($sql);
	$tags = array();
	$texts = array();
	while ($row = $db->fetch($res)) {
		$tags[$row['tag']] = $row['count'];
		$texts[$row['tag']] = htmlentities($row['text']);
	}
	
	// $tags is the array
	       
	ksort($tags);
	       
	$max_size = 32; // max font size in pixels
	$min_size = 12; // min font size in pixels
	       
	// largest and smallest array values
	$max_qty = max(array_values($tags));
	$min_qty = min(array_values($tags));
	       
	// find the range of values
	//$spread = $max_qty - $min_qty;
	//if ($spread == 0) { // we don't want to divide by zero
	//	$spread = 1;
	//}
	       
	// set the font-size increment
	//$step = ($max_size - $min_size) / ($spread);
	       
	// loop through the tag array
	$cloud = '';
	foreach ($tags as $tag => $count) {
		// calculate font-size
		// find the $value in excess of $min_qty
		// multiply by the font-size increment ($size)
		// and add the $min_size set above
		//$size = round($min_size + (($count - $min_qty) * $step));
		
		// Logarythmic tag list
		$weight = (log($count)-log($min_qty)) / (log($max_qty) - log($min_qty));
		$size = $min_size + round(($max_size - $min_size) * $weight);
	    
		$cloud .= '<a href="browse.php?tag=' . urlencode($tag) . '" class="tags" style="font-size: ' . $size . 'px">' . $texts[$tag] . '</a> ';
	}

	outputHTML('<p>' . $cloud . '</p><br style="clear: both;" /><p id="browse"><a href="browse.php?tags=all">Show all tags</a></p>', array('title' => 'Tags'));
}

?>