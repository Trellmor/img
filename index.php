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

$filetypes = '';
foreach ($mime as $f) {
	$filetypes .= $f . ', ';
}
$filetypes = substr($filetypes, 0, -2);

$tagjs  = '<script src="js/jquery.tag.js" type="text/javascript"></script>' . "\n";
$tagjs .= '<script src="js/index.js" type="text/javascript"></script>' . "\n";

$content = '<form action="upload.php" method="post" enctype="multipart/form-data">
			<div>
			<input type="hidden" name="MAX_FILE_SIZE" value="' . $maxsize.'" />
			<div id="inputimagecontainer">
			<span class="text">File:</span><input type="file" size="39" name="image[]" />&nbsp;
			<img src="images/add.png" id="addimage" alt="Add another image" title="Add another image" /><br /><br />
			</div>
			<span class="text">Tags:</span><input id="inputtags" type="text" size="39" name="tags" />
			<span class="text">&nbsp;</span><input id="submit" type="submit" name="submit" value="Upload" />
			<p id="info">
				Maximum upload size: ' . byteConvert($maxsize) . '<br />
				Allowed file types: ' . $filetypes . '<br />
				Use , (comma) to seperate tags 
			</p>
			<p id="browse"><a href="browse.php">Browse</a> | <a href="search.php">Search</a></p>
			</div>
			</form>
		</div>
		<div id="loading">
			<img src="images/ico-loading.gif" alt="Loading..." />
			<span>Loading</span>';

outputHTML($content, array('header' => $tagjs));

?>
