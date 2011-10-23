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
require_once('lib/class.browse.php');
require_once('lib/class.search.php');

if (isset($_GET['q'])) {
	$search = new search($pdo);
	$images = $search->search($_GET['q']);


	// If we got no results, exit
	if (!count($images)) errorMsg("No results found.");
	
	// Only get a limited number of result
	$page = (isset($_GET['p']) && is_numeric($_GET['p'])) ? (int)$_GET['p'] : 1;
	$offset =  ($page - 1) * $pagelimit;
	$imagecount = count($images);
	$images = array_slice($images, $offset, $pagelimit);
	
	// Output images ordered by relevance
	$output = '';
	foreach ($images as $image) {
		$output .= '<div class="previewimage"><a href="' . $image->name . '" class="lightbox" rel="lightbox"><img src="' . $image->getPreview() . '" alt="' . htmlentities($image->original_name, ENT_QUOTES, 'UTF-8') . '" /></a><br />' . "\n";
		$output .= '<a href="image.php?i=' . urlnumber_encode($image->id) . '">Show</a></div>' . "\n";
	}

	// Generate page counter
	$pages = '<p id="pages">';
	for ($i = 1; $i <= ceil($imagecount/$pagelimit); $i++) {
		if ($i != $page) $pages .= '<a href="search.php?q=' . stripslashes_safe($_GET['q']) . '&amp;p=' . $i . '">' . $i . '</a>';
		else $pages .= $i; 
		$pages .= ' &middot; ';
	}
	$pages = substr($pages, 0, -10) . '</p>';
	
	outputHTML('<h2>' . htmlentities(one_wordwrap(stripslashes_safe($_GET['q']), 5, '&shy;'), ENT_QUOTES, 'UTF-8', false) . '</h2>' . $output . '<br style="clear: both;" />' . $pages, array('title' => 'Search: ' . htmlentities(stripslashes_safe($_GET['q']), ENT_QUOTES, 'UTF-8'), 'lightbox' => true));

} else {
	// For advanced options
	$header = '<script type="text/javascript">' . "\n";
	$header .= '$(document).ready(function() { $("#inputsearch").focus(); });' . "\n";
	$header .= '</script>' . "\n";
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
	<input type="text" name="q" size="40" id="inputsearch" tabindex="1" />
	<input type="submit" value="Search" tabindex="2" />
	<br />&nbsp;
</div>
</form>
';
	
	outputHTML($output, array('title' => 'Search', 'header' => $header));
}

?>