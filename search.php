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

if (isset($_GET['q'])) {
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
	
	$tags = array();
	while ($row = $db->fetch($res)) {
		$tags[] = $row['id'];
	}
	
	// Select all images that contain one of these tags
	$sql = "SELECT image FROM imagetags WHERE tag IN ('" . implode("', '", $tags) . "')";
	$res = $db->query($sql);
	$images = array();
	while ($row = $db->fetch($res)) {
		if (!isset($images[$row['image']])) $images[$row['image']] = 1;
		else $images[$row['image']]++;
	}
	
	// Search image names
	$sql = "SELECT ROWID as id FROM images WHERE ";
	$clause = '';
	foreach ($search as $s) {
		$s = trim($s);
		if (!empty($s)) {
			if (!empty($clause)) $clause .= ' or ';
			$clause .= "original_name LIKE '%" . $db->escape($s) . "%'";
		}
	}
	// No real need to recheck $clause, because we checked it earlier when we searched tags
	$sql .= $clause;
	$res = $db->query($sql);
	while ($row = $db->fetch($res)) {
		if (!isset($images[$row['id']])) $images[$row['id']] = 1;
		else $images[$row['id']]++;
	}

	// If we got no results, exit
	if (!count($images)) errorMsg("No results found.");
	
	// Order by relevance
	arsort($images);
	// Images can contain any tags or matches in filename
	$images = array_keys($images);
	$imagecount = count($images);
	
	// Only get a limited number of result
	$page = (isset($_GET['p']) && is_numeric($_GET['p'])) ? (int)$_GET['p'] : 1;
	$offset =  ($page - 1) * $pagelimit;
	$images = array_slice($images, $offset, $pagelimit);
	
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
		$output .= '<div class="previewimage"><a href="' . $full_images[$i]['location'] . '" class="lightbox" rel="lightbox"><img src="' . $preview . '" alt="' . htmlentities($full_images[$i]['original_name']) . '" /></a><br />' . "\n";
		$output .= '<a href="image.php?i=' . urlnumber_encode($i) . '">Show</a></div>' . "\n";
	}

	// Generate page counter
	$pages = '<p id="pages">';
	for ($i = 1; $i <= ceil($imagecount/$pagelimit); $i++) {
		if ($i != $page) $pages .= '<a href="search.php?q=' . $_GET['q'] . '&amp;p=' . $i . '">' . $i . '</a>';
		else $pages .= $i; 
		$pages .= ' &middot; ';
	}
	$pages = substr($pages, 0, -10) . '</p>';
	
	outputHTML('<h2>' . one_wordwrap(htmlentities($_GET['q']), 5, '&shy;') . '</h2>' . $output . '<br style="clear: both;" />' . $pages, array('title' => 'Search: ' . htmlentities($_GET['q']), 'lightbox' => true));
	
} else {
	// For advanced options
	$header = '';
	/*$header .= '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script>';
	$header .= '<script type="text/javascript">';
	$header .= "$(document).ready(function() {
					$('#advanced_link').click(function(e) {
						e.preventDefault();
						$('#advanced').slideToggle('slow');
					})
				});";
	$header .= '</script>';*/
	
	// Generate a simple search field
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