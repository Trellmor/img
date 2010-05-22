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

// Open database
$db = new sqlite('lib/db.sqlite');

if (isset($_GET['tag'])) {

	// Calculate page offset
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
	// Generate HTML output
	while ($row = $db->fetch($res)) {
		// Save tag text to display as header
		$tag_text = $row['text'];
		
		$preview = dirname($row['name']) . '/preview/' . basename($row['name']);
		$images .= '<div class="previewimage"><a href="' . $row['name'] . '" class="lightbox" rel="lightbox"><img src="' . $preview . '" alt="' . htmlentities($row['original_name'], ENT_QUOTES, 'UTF-8') . '" /></a><br />' . "\n";
		$images .= '<a href="image.php?i=' . urlnumber_encode($row['id']) . '">Show</a></div>' . "\n";
	}
	
	// Generate page count
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
	
	$pages = '<p id="pages">';
	for ($i = 1; $i <= ceil($row['count']/$pagelimit); $i++) {
		if ($i != $page) $pages .= '<a href="browse.php?tag=' . $_GET['tag'] . '&amp;p=' . $i . '">' . $i . '</a>';
		else $pages .= $i; 
		$pages .= ' &middot; ';
	}
	$pages = substr($pages, 0, -10) . '</p>';
	
	outputHTML('<h2>' . htmlentities(one_wordwrap($tag_text, 5, '&shy;'), ENT_QUOTES, 'UTF-8', false) . '</h2>' . $images . '<br style="clear: both;" />' . $pages, array('title' => 'Tag: ' . htmlentities($tag_text, ENT_QUOTES, 'UTF-8'), 'lightbox' => true));

} elseif(isset($_GET['user'])) {
	// Calculate page offset
	$page = (isset($_GET['p']) && is_numeric($_GET['p'])) ? (int)$_GET['p'] : 1;
	$offset =  ($page - 1) * $pagelimit;
	
	$sql = "SELECT
 ROWID as id,
 location as name,
 original_name
FROM
 images
WHERE
 user = '" . $db->escape(urldecode($_GET['user'])) . "'
ORDER BY
 time DESC
LIMIT
 " . $offset . ", " . $pagelimit . ";";

	$res = $db->query($sql);
	$images = '';
	// Generate HTML output
	while ($row = $db->fetch($res)) {
		$preview = dirname($row['name']) . '/preview/' . basename($row['name']);
		$images .= '<div class="previewimage"><a href="' . $row['name'] . '" class="lightbox" rel="lightbox"><img src="' . $preview . '" alt="' . htmlentities($row['original_name'], ENT_QUOTES, 'UTF-8') . '" /></a><br />' . "\n";
		$images .= '<a href="image.php?i=' . urlnumber_encode($row['id']) . '">Show</a></div>' . "\n";
	}
	
	// Generate page count
	$sql = "SELECT
 count(ROWID) as count
FROM
 images
WHERE
 user = '" . $db->escape(urldecode($_GET['user'])) . "';";
	$row = $db->fetch($db->query($sql));
	
	$pages = '<p id="pages">';
	for ($i = 1; $i <= ceil($row['count']/$pagelimit); $i++) {
		if ($i != $page) $pages .= '<a href="browse.php?user=' . $_GET['user'] . '&amp;p=' . $i . '">' . $i . '</a>';
		else $pages .= $i; 
		$pages .= ' &middot; ';
	}
	$pages = substr($pages, 0, -10) . '</p>';
	
	outputHTML('<h2>' . htmlentities(one_wordwrap(urldecode($_GET['user']), 5, '&shy;'), ENT_QUOTES, 'UTF-8', false) . '</h2>' . $images . '<br style="clear: both;" />' . $pages, array('title' => 'Tag: ' . htmlentities($_GET['user'], ENT_QUOTES, 'UTF-8'), 'lightbox' => true));
	
} elseif(isset($_GET['ip']) && isset($_GET['time'])) {
	$sql = "SELECT
 ROWID as id,
 location as name,
 original_name
FROM
 images
WHERE
 ip = '" . $db->escape($_GET['ip']) . "' and
 time = '" . $db->escape($_GET['time']) . "';";

	$res = $db->query($sql);
	$images = '';
	// Generate HTML output
	while ($row = $db->fetch($res)) {
		$preview = dirname($row['name']) . '/preview/' . basename($row['name']);
		$images .= '<div class="previewimage"><a href="' . $row['name'] . '" class="lightbox" rel="lightbox"><img src="' . $preview . '" alt="' . htmlentities($row['original_name'], ENT_QUOTES, 'UTF-8') . '" /></a><br />' . "\n";
		$images .= '<a href="image.php?i=' . urlnumber_encode($row['id']) . '">Show</a></div>' . "\n";
	}
	
	outputHTML('<h2>' . htmlentities(one_wordwrap(urldecode($_GET['ip'] . ' - ' . $_GET['time']), 5, '&shy;'), ENT_QUOTES, 'UTF-8', false) . '</h2>' . $images . '<br style="clear: both;" />', array('title' => 'Upload: ' . htmlentities($_GET['ip'] . ' - ' . $_GET['time'], ENT_QUOTES, 'UTF-8'), 'lightbox' => true));
} else {

	// Get tags from db
	$sql = "SELECT tag, text, count FROM tags ORDER BY count DESC, ROWID DESC";
	$sql .= (isset($_GET['tags']) && $_GET['tags'] == 'all') ? ';' : ' LIMIT 100;';

	$res = $db->query($sql);
	$tags = array();
	$texts = array();
	while ($row = $db->fetch($res)) {
		$tags[$row['tag']] = $row['count'];
		$texts[$row['tag']] = htmlentities($row['text'], ENT_QUOTES, 'UTF-8');
	}
	
	// $tags is the array
	ksort($tags);

	// largest and smallest array values
	$max_qty = max(array_values($tags));
	$min_qty = min(array_values($tags));
	       
	       
	// loop through the tag array and generate HTML output
	$cloud = '';
	foreach ($tags as $tag => $count) {		
		// Logarythmic tag list
		$weight = (log($count)-log($min_qty)) / (log($max_qty) - log($min_qty));
		$size = $min_size + round(($max_size - $min_size) * $weight);
	    
		$cloud .= '<a href="browse.php?tag=' . urlencode($tag) . '" class="tags" style="font-size: ' . $size . 'px">' . $texts[$tag] . '</a> ';
	}

	outputHTML('<p>' . $cloud . '</p><br style="clear: both;" /><p id="browse"><a href="browse.php?tags=all">Show all tags</a></p>', array('title' => 'Tags'));
}

?>