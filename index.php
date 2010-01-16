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

error_reporting(E_ALL);

require_once('lib/functions.php');
require_once('lib/config.php');

$filetypes = '';
foreach ($mime as $f) {
	$filetypes .= $f . ', ';
}
$filetypes = substr($filetypes, 0, -2);

$tagjs  = '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script>';
$tagjs .= '<script src="tag.js" type="text/javascript"></script>';
$tagjs .= '<script type="text/javascript">';
$tagjs .= "$(function () {
	$('#inputtags').attr('autocomplete', 'off');
	$('#inputtags').tagSuggest({
		url: 'tags.php',
		delay: 250,
		separator: ', ',
		tagContainer: 'p',
	});
});
</script>";

$content = '<form action="upload.php" method="post" enctype="multipart/form-data">
			<div>
			<input type="hidden" name="MAX_FILE_SIZE" value="' . $maxsize.'" />
			<span class="text">File:</span><input type="file" size="40" name="image" /><br /><br />
			<span class="text">Tags:</span><input id="inputtags" type="text" size="40" name="tags" />
			<span class="text">&nbsp;</span><input id="submit" type="submit" name="submit" value="Upload" />
			<p id="info">
				Maximum upload size: ' . byteConvert($maxsize) . '<br />
				Allowed file types: ' . $filetypes . '<br />
				Use , (comma) to seperate tags 
			</p>
			<p id="browse"><a href="browse.php">Browse</a> | <a href="search.php">Search</a></p>
			</div>
			</form>';

outputHTML($content, array('header' => $tagjs));

?>