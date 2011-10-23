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

if (isset($_GET['tag'])) {

	// Calculate page offset
	$page = (isset($_GET['p']) && is_numeric($_GET['p'])) ? (int)$_GET['p'] : 1;
	$offset =  ($page - 1) * $pagelimit;
	
	$tags_in = urldecode($_GET['tag']);
	$browse = new browse($pdo);
	$images = $browse->getImagesByTags($tags_in, $offset, $pagelimit);
	
	$tag_text = ucwords(str_ireplace(',', ', ', stripslashes_safe($_GET['tag'])));
	$img_text = '';
	// Generate HTML output
	foreach ($images as $image) {
		$img_text .= '<div class="previewimage"><a href="' . $image->name . '" class="lightbox" rel="lightbox"><img src="' . $image->getPreview() . '" alt="' . htmlentities($image->original_name, ENT_QUOTES, 'UTF-8') . '" /></a><br />' . "\n";
		$img_text .= '<a href="image.php?i=' . urlnumber_encode($image->id) . '">Show</a></div>' . "\n";
	}
	
	$pages = '<p id="pages">';
	if ($page > 1 && ceil($browse->resultCount()/$pagelimit) > 1) {
		$pages .= '<a href="browse.php?tag=' . stripslashes_safe($_GET['tag']) . '">| &lt;</a> &middot; ';
		$pages .= '<a href="browse.php?tag=' . stripslashes_safe($_GET['tag']) . '&amp;p=' . ($page - 1)  . '">&lt; &lt;</a> &middot; '; 
	}	
	
	for ($i = 1; $i <= ceil($browse->resultCount()/$pagelimit); $i++) {
		if ($i != $page) $pages .= '<a href="browse.php?tag=' . stripslashes_safe($_GET['tag']) . '&amp;p=' . $i . '">' . $i . '</a>';
		else $pages .= $i; 
		$pages .= ' &middot; ';
	}
	
	if ($page < ceil($browse->resultCount()/$pagelimit)) {
		$pages .= '<a href="browse.php?tag=' . stripslashes_safe($_GET['tag']) . '&amp;p=' . ($page + 1)  . '">&gt; &gt;</a> &middot; ';
		$pages .= '<a href="browse.php?tag=' . stripslashes_safe($_GET['tag']) . '&amp;p=' . (ceil($browse->resultCount()/$pagelimit)) . '">&gt; |</a> &middot; '; 
	
	}
	
	$pages = substr($pages, 0, -10) . '</p>';
	
	//Get tags for images
	$tags = $browse->getTagListTags($tags_in);
	if (count($tags) > 0) {
		$tags_text = '</div><div id="taglist"><ul>';
		foreach($tags as $tag) {
			$tags_text .= '<li><a href="browse.php?tag=' . stripslashes_safe($_GET['tag']) . ',' . urlencode($tag->tag) . '">' . htmlentities($tag->text, ENT_QUOTES, 'UTF-8') . '(' . $tag->count . ')</a></li>';
		}
		$tags_text .= '</ul>';
	} else {
		$tags_text = '';
	}
	
	outputHTML('<h2>' . htmlentities(one_wordwrap($tag_text, 5, '&shy;'), ENT_QUOTES, 'UTF-8', false) . '</h2>' . $img_text . '<br style="clear: both;" />' . $pages . $tags_text, array('title' => 'Tag: ' . htmlentities($tag_text, ENT_QUOTES, 'UTF-8'), 'lightbox' => true));

} elseif(isset($_GET['user'])) {
	// Calculate page offset
	$page = (isset($_GET['p']) && is_numeric($_GET['p'])) ? (int)$_GET['p'] : 1;
	$offset =  ($page - 1) * $pagelimit;
	
	$browse = new browse($pdo);
	$images = $browse->getImagesByUser($_GET['user'], $offset, $pagelimit);
	

	$img_text = '';
	// Generate HTML output
	foreach ($images as $image) {
		$img_text .= '<div class="previewimage"><a href="' . $image->name . '" class="lightbox" rel="lightbox"><img src="' . $image->getPreview() . '" alt="' . htmlentities($image->original_name, ENT_QUOTES, 'UTF-8') . '" /></a><br />' . "\n";
		$img_text .= '<a href="image.php?i=' . urlnumber_encode($image->id) . '">Show</a></div>' . "\n";
	}
	
	// Generate page count	
	$pages = '<p id="pages">';
	for ($i = 1; $i <= ceil($browse->resultCount()/$pagelimit); $i++) {
		if ($i != $page) $pages .= '<a href="browse.php?user=' . stripslashes_safe($_GET['user']) . '&amp;p=' . $i . '">' . $i . '</a>';
		else $pages .= $i; 
		$pages .= ' &middot; ';
	}
	$pages = substr($pages, 0, -10) . '</p>';
	
	outputHTML('<h2>' . htmlentities(one_wordwrap(urldecode(stripslashes_safe($_GET['user'])), 5, '&shy;'), ENT_QUOTES, 'UTF-8', false) . '</h2>' . $img_text . '<br style="clear: both;" />' . $pages, array('title' => 'Tag: ' . htmlentities(stripslashes_safe($_GET['user']), ENT_QUOTES, 'UTF-8'), 'lightbox' => true));
} elseif(isset($_GET['ip']) && isset($_GET['time'])) {
	$browse = new browse($pdo);
	$images = $browse->getImagesByIpTime($_GET['ip'], $_GET['time']);

	$img_text = '';
	// Generate HTML output
	foreach ($images as $image) {
		$img_text .= '<div class="previewimage"><a href="' . $image->name . '" class="lightbox" rel="lightbox"><img src="' . $image->getPreview() . '" alt="' . htmlentities($image->original_name, ENT_QUOTES, 'UTF-8') . '" /></a><br />' . "\n";
		$img_text .= '<a href="image.php?i=' . urlnumber_encode($image->id) . '">Show</a></div>' . "\n";
	}
	
	outputHTML('<h2>' . htmlentities(one_wordwrap(urldecode($_GET['ip'] . ' - ' . $_GET['time']), 5, '&shy;'), ENT_QUOTES, 'UTF-8', false) . '</h2>' . $img_text . '<br style="clear: both;" />', array('title' => 'Upload: ' . htmlentities($_GET['ip'] . ' - ' . $_GET['time'], ENT_QUOTES, 'UTF-8'), 'lightbox' => true));
} else {

	// Get tags from db
	$browse = new browse($pdo);
	$tag_list = $browse->getTagList((isset($_GET['tags']) && $_GET['tags'] == 'all') ? -1 : 100);
	
	$tags = array();
	$texts = array();
	foreach ($tag_list as $tag) {
		$tags[$tag->tag] = $tag->count;
		$texts[$tag->tag] = htmlentities($tag->text, ENT_QUOTES, 'UTF-8');
	}
	
	// $tags is the array
	ksort($tags);

	// largest and smallest array values
	$max_qty = (count($tags) > 0) ? max(array_values($tags)) : 0;
	$min_qty = (count($tags) > 0) ? min(array_values($tags)) : 0;
	       
	// loop through the tag array and generate HTML output
	$cloud = '';
	foreach ($tags as $tag => $count) {		
		// Logarythmic tag list
		$div = log($max_qty) - log($min_qty);
		if ($div == 0) $div = 1;
		$weight = (log($count)-log($min_qty)) / $div;
		$size = $min_size + round(($max_size - $min_size) * $weight);
	    
		$cloud .= '<a href="browse.php?tag=' . urlencode($tag) . '" class="tags" style="font-size: ' . $size . 'px">' . $texts[$tag] . '</a> ';
	}

	outputHTML('<p>' . $cloud . '</p><br style="clear: both;" /><p id="browse"><a href="browse.php?tags=all">Show all tags</a></p>', array('title' => 'Tags'));
}

?>