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
require_once('lib/class.upload.php');

if (!isLogin()) errorMsg('Not logged in.');

if (!isset($_GET['image'])) errorMsg('Image not found.');

$browse = new browse($pdo);
try {
	$image = $browse->getImage(urlnumber_decode($_GET['image']));
} catch (BrowseException $e) {
	errorMsg($e->getMessage());
}

if ($image->user != $_SESSION['openid_identity'] && !isAdmin()) errorMsg('Permission denied.');

switch (@$_GET['type']) {
	case 'image':
		switch (@$_GET['action']) {
			case 'delete':
				if ($image->delete()) {
					errorMsg('Image deleted.' . url());
				} else {
					errorMsg('Delte failed' . url());
				}
				break;
			default:
				errorMsg('No action set.');
				break;
		}
		break;
	case 'tags':
		switch (@$_GET['action']) {
			case 'edit':
				if (isset($_POST['tags'])) {					
					$stmt = DAL::Delete_ImageTags($pdo, $image->id);
					$stmt->execute();
					
					$upload = new upload();
					$upload->pdo($pdo);
					$upload->tagImg($image->id, $_POST['tags']);
					
					header('Location: ' . url() . 'image.php?i=' . urlnumber_encode($image->id));
					errorMsg('Tags edited.', 'image.php?i=' . urlnumber_encode($image->id));
				}
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
