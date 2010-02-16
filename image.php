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
require_once('lib/class.sqlite.php');

if (!isset($_GET['i'])) {
	errorMsg('Image not found.');
}

// Open database connection
$db = new sqlite('lib/db.sqlite');
$id = urlnumber_decode($_GET['i']);
	
// Select image
$row = $db->fetch($db->query("SELECT ROWID as id, location, original_name, user FROM images WHERE ROWID = '" . $id . "';"));
if (!$row) {
	errorMsg('Image not found.');
}

$id = $row['id'];
$name = $row['location'];
$preview = dirname($name) . '/preview/' . basename($name);
$original_name = htmlentities($row['original_name']);
$user = $row['user'];

// Get tags
$res = $db->query("SELECT t.tag, t.text FROM tags t, imagetags i WHERE t.ROWID = i.tag and i.image = '" . $id . "';");
$tags = '';
while ($row = $db->fetch($res)) {
	$tags .= '<a href="browse.php?tag=' . urlencode($row['tag']) . '">' . htmlentities($row['text']) . '</a>, ';
}
$tags = substr($tags, 0, -2);

// Generate HTML and code snippets for inserting the image
$output = '<h2 id="imagename"><a href="' . $name . '">' . one_wordwrap($original_name, 5, '&shy;') . '</a></h2>
			<a id="preview" href="' . $name . '" rel="lightbox" ><img src="' . $preview . '" alt="" /></a>
			<p id="tags">Tags: ' . $tags . '<br /></p>
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
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="' . url() . $name . '" /></td>
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="&lt;a href=&quot;' . url() . $name . '&quot;&gt;&lt;img src=&quot;' . url() . $preview . '&quot; alt=&quot;' . $original_name . ' - img.pew.cc&quot; /&gt;&lt;/a&gt;" /></td>
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="[URL=' . url() . $name . '][IMG]' . url() . $preview . '[/IMG][/URL]" /></td>
					</tr>
					<tr>
						<td>Full</td>
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="' .  url() . $name . '" /></td>
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="&lt;img src=&quot;' . url() . $name . '&quot; alt=&quot;' . $original_name . ' - img.pew.cc&quot; /&gt;" /></td>
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="[IMG]' . url() . $name . '[/IMG]" /></td>
					</tr>
				</tbody>
			</table>';

$header = '';

if(isLogin()) {
	if ($user == $_SESSION['openid_identity'] || isAdmin()) {
		$header = '<script type="text/javascript" src="js/image.js"></script>';
	}
}

outputHTML($output, array('title' => 'Image: ' . $original_name, 'lightbox' => true, 'header' => $header));

?>