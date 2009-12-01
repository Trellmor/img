<?php

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
	'image/jpeg'	=> 'jpeg',
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
 * Preview size and with
 * 
 * The sapect ratio of the image will be kept
 */
$preview_width = 150;
$preview_height = 150;

?>