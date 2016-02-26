<?php

/**
 * Configuration file
 *
 * Do not modify the values here, instead create a file name
 * app/localconfig.php and change the individual there.
 * @var unknown
 */

$config = array();

/**
 * Database connection settings
 *
 * Warning: only sqlite is supported
 */
$config['database'] = array(
	'dsn'		=> 'sqlite:' . APP_ROOT . '/data/db.sqlite3',
	'username'	=> null,
	'password'	=> null,
	'options'	=> array()
);

/**
 * URI settings
 */
$config['uri'] = array(
	'scheme'	=> 'http',
	'host' 		=> null,
	'port' 		=> null,
	'path'		=> '/',
	'script'    => false
);

/**
 * Site language
 */
$config['language'] = 'en_US';

/**
 * Site title
 */
$config['page_title'] = 'img.pew.cc';

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
$config['mime'] = array(
	'image/gif'		=> 'gif',
	'image/jpeg'	=> 'jpg',
	'image/png'		=> 'png',
	'image/bmp'		=> 'bmp',
	'image/x-ms-bmp'=> 'bmp'
);

/**
 * Maximum allowed size for an image file
 */
$config['maxsize'] = 2 * 1024 * 1024;

/**
 * Image store location
 *
 * Will be created as a subfolder of /public/
 *
 * We create new folder vor every month
 *
 * You can set this to any path you want
 *
 * If the folder don't exist, it will be created
 */
$config['imgdir'] = date('Y/m/');

/**
 * Preview height and width
 *
 * The aspect ratio of the image will be kept
 */
$config['preview'] = array(
		'width' => 150,
		'height' => 150
);

/**
 * Browse/Search limit
 *
 * Controls how many images per page are returned for tag browsing/search
 */
$config['pagelimit'] = 16;

/**
 * Google Sign In
 *
 * See https://developers.google.com/identity/sign-in/web/devconsole-project
 */
$config['google-signin'] = array(
	'client_id' => '',
	'client_secret' => ''
);
