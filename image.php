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

if (!isset($_GET['i'])) {
	errorMsg('Image not found.');
}

// Open database connection
$id = urlnumber_decode($_GET['i']);

$browse = new browse($pdo);
try {
	$image = $browse->getImage($id);
} catch (BrowseException $e) {
	errorMsg($e->getMessage);
}

// Get tags
$tags = $browse->getImageTags($id);
$tags_text = '';
foreach ($tags as $tag) {
	$tags_text .= '<a href="browse.php?tag=' . urlencode($tag->tag) . '">' . htmlentities($tag->text, ENT_QUOTES, 'UTF-8') . '</a>, ';
}
$tags_text = substr($tags_text, 0, -2);

// Generate HTML and code snippets for inserting the image
$output = '<h2 id="imagename"><a href="' . $image->name . '">' . htmlentities(one_wordwrap($image->original_name, 5, '&shy;'), ENT_QUOTES, 'UTF-8', false) . '</a></h2>
			<a id="preview" href="' . $image->name . '" rel="lightbox" ><img src="' . $image->getPreview() . '" alt="" /></a>
			<p id="tags">Tags: ' . $tags_text . '</p>
			<table>
				<thead>
					<tr>
						<th class="tabletext">&nbsp;</th>
						<th class="tablecode">Plain</th>
						<th class="tablecode">HTML</th>
						<th class="tablecode">BB code</th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>Preview</td>
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="' . url() . $image->name . '" /></td>
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="&lt;a href=&quot;' . url() . $image->name . '&quot;&gt;&lt;img src=&quot;' . url() . $image->getPreview() . '&quot; alt=&quot;' . htmlentities($image->original_name, ENT_QUOTES, 'UTF-8') . ' - ' . $page_title . '&quot; /&gt;&lt;/a&gt;" /></td>
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="[URL=' . url() . $image->name . '][IMG]' . url() . $image->getPreview() . '[/IMG][/URL]" /></td>
					</tr>
					<tr>
						<td>Full</td>
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="' .  url() . $image->name . '" /></td>
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="&lt;img src=&quot;' . url() . $image->name . '&quot; alt=&quot;' . htmlentities($image->original_name, ENT_QUOTES, 'UTF-8') . ' - ' . $page_title . '&quot; /&gt;" /></td>
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="[IMG]' . url() . $image->name . '[/IMG]" /></td>
					</tr>';
if ($show_local_path) {
$output .= '					<tr>
						<td>Path</td>
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="' . $image->path . '" /></td>
						<td />
						<td />
					</tr>';
}					
$output .= '				</tbody>
			</table>';

$header = '';

if(isLogin()) {
	if ($image->user == $_SESSION['openid_identity'] || isAdmin()) {
		$header  = '<script src="js/jquery.tag.js" type="text/javascript"></script>';
		$header .= '<script type="text/javascript" src="js/image.js"></script>';
	}
}

outputHTML($output, array('title' => 'Image: ' . htmlentities($image->original_name, ENT_QUOTES, 'UTF-8'), 'lightbox' => true, 'header' => $header));

?>