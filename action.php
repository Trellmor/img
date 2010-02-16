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

require_once('lib/config.php');
require_once('lib/functions.php');
require_once('lib/class.sqlite.php');

if (!isLogin()) errorMsg('Not logged in.');

$db = new sqlite('lib/db.sqlite');

if (!isset($_GET['image'])) errorMsg('Image not found.');
$res = $db->query("SELECT ROWID as id, user, location FROM images WHERE ROWID = '" . $db->escape(urlnumber_decode($_GET['image'])) . "';");
if (!$db->numrows($res)) errorMsg("Image not found.");
$row = $db->fetch($res);
if ($row['user'] != $_SESSION['openid_identity'] && !isAdmin()) errorMsg('Permission denied.');

switch (@$_GET['type']) {
	case 'image':
		switch (@$_GET['action']) {
			case 'delete':
				$db->exec("BEGIN;
DELETE FROM images WHERE ROWID = '" . $row['id'] . "';
UPDATE tags SET count = count - 1 WHERE ROWID IN (SELECT tag FROM imagetags WHERE image = '" . $row['id'] . "');
DELETE FROM tags WHERE count < 1;
DELETE FROM imagetags WHERE image = '" . $row['id'] . "';
COMMIT;");
				unlink_safe($row['location']);
				unlink_safe(dirname($row['location']) . '/preview/' . basename($row['location']));
				errorMsg('Image deleted.', url());
				break;
			default:
				errorMsg('No action set.');
				break;
		}
		break;
	default:
		errorMsg('No action type set.');
		break;
}

?>