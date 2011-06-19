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

/**
 * Maximum allowed size for an image file
 * 
 * We use the upload_max_filesize from php.ini, if you want a lower
 * value, set $maxsize to an integer.
 */
$maxsize = ini2bytes(ini_get("upload_max_filesize"));

/**
 * Allowed mime types and their extension
 * 
 * Other mime types are (for example):
 *  'image/tiff' => 'tiff',
 *  'image/jp2' => 'jp2',
 *  'image/iff' => 'iff',
 *  'image/vnd.wap.wbmp' => 'bmp',
 *  'image/xbm' => 'xbm',
 *  'image/vnd.microsoft.icon' => 'ico',
 *  'image/psd' => 'psd',
 */
$mime = array(
	'image/gif'		=> 'gif',
	'image/jpeg'	=> 'jpg',
	'image/png'		=> 'png',
	'image/bmp'		=> 'bmp',
);

/**
 * Image store location
 * 
 * We create new folder vor every month
 * 
 * You can set this to any path you want
 * 
 * If the folder don't exist, it will be created
 */
$imgdir = date('Y/m/');

/**
 * Preview height and width
 * 
 * The aspect ratio of the image will be kept
 */
$preview_width = 150;
$preview_height = 150;

/**
 * Browse/Search limit
 * 
 * Controls how many images per page are returned for tag browsing/search
 */
$pagelimit = 30;

/**
 * Tag cloud font size in pixels
 */
$max_size = 32;
$min_size = 12;

/**
 * Admin OpenID accounts
 */
$admins = array(
	'http://trellmor.myopenid.com/',
);

/**
 * Page title
 */
$page_title = 'img.pew.cc';

?>