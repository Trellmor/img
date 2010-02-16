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
 * Converts a php.ini value to an integer value
 *
 * @param	string		$s					The php.ini value
 * @return	integer							Value in bytes
 */
function ini2bytes($s)
{
	$s = trim($s);
	$l = strtolower($s[strlen($s) - 1]);
	switch($l) {
		case 'g':
			$s *= 1024;
		case 'm':
			$s *= 1024;
		case 'k':
			$s *= 1024;
	}
	return $s;
}

/**
 * Converts bytes to an human readable value
 *
 * @param 	integer			$bytes
 * @return	string
 */
function byteConvert($bytes)
{
        $s = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');

        if ($bytes != 0) {
        	$e = floor(log($bytes)/log(1024));
        	return sprintf('%.2f '.$s[$e], ($bytes/pow(1024, floor($e))));
        } else {
        	$e = 0;
        	return sprintf('%.2f '.$s[$e], $bytes);
        }
}

/**
 * Generates an error page
 *
 * After sending the page to the browser this function will stop the script (die)
 * @param 	string			$msg	Error Message
 */
function errorMsg($msg, $return = 'javascript:history.back();')
{
	outputHTML($msg . '<br /><br /><a href="' . $return . '">Return</a>');
	die();
}

/**
 * Generates a new file name if the file already exists
 *
 * @param	string			$f				Filename to check
 * @return	string							Name of a non-existing file
 */
function checkExists($f)
{
	$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
	if(file_exists($f)) {
		$f = explode('.', $f);
		$f[count($f) - 2] .= $chars[rand(0, strlen($chars) - 1)];
		$f = checkExists(implode('.', $f));
	}
	return $f;
}

/**
 * Generates the URL of the script based on the HTTP headers and script location
 *
 * @return	string							URL
 */
function url() {
	$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
	if ($url[strlen($url) - 1] != '/') {
		$url .= '/';
	}
	return $url;
}

/**
 * Moves a uploaded file and creates all directories as needed
 *
 * @param	string			$f				The file to move
 * @param	string			$d				The destination file
 * @return	bool							Success
 */
function move_uploaded_file_save($f, $d)
{
	$dir = dirname($d);
	if (!file_exists($dir)) {
		if(!mkdir($dir, 0777, true)) {
			return false;
		}
	}
	
	return move_uploaded_file($f, $d);
}

/**
 * Converts an integer in a URL save string
 *
 * @param 	integer		$number
 * @return	string							Encoded number
 */
function urlnumber_encode($number)
{
	//0-9 = 0-9
	//a-z = 10-35
	//A-Z = 36-61
	//$-_.+!*'(), = 62-64 
	static $table = array(
	 '0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
	 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
	 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
	 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
	 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
	 '-', '_', '.',
	);
	
	$r = $number % 64;
	if ($number - $r == 0)
		return $table[$r];
	else
		return  urlnumber_encode((($number - $r) / 64)) . $table[$r];
}

/**
 * Decodes a URL save number to an integer
 *
 * @param	string			$str			String to decode
 * @return	integet							Decoded number
 */
function urlnumber_decode($str)
{
	static $table = array(
	 '0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4,
	 '5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9,
	 'a' => 10, 'b' => 11, 'c' => 12, 'd' => 13, 'e' => 14,
	 'f' => 15, 'g' => 16, 'h' => 17, 'i' => 18, 'j' => 19,
	 'k' => 20, 'l' => 21, 'm' => 22, 'n' => 23, 'o' => 24,
	 'p' => 25, 'q' => 26, 'r' => 27, 's' => 28, 't' => 29,
	 'u' => 30, 'v' => 31, 'w' => 32, 'x' => 33, 'y' => 34, 'z' => 35,
	 'A' => 36, 'B' => 37, 'C' => 38, 'D' => 39, 'E' => 40,
	 'F' => 41, 'G' => 42, 'H' => 43, 'I' => 44, 'J' => 45,
	 'K' => 46, 'L' => 47, 'M' => 48, 'N' => 49, 'O' => 50,
	 'P' => 51, 'Q' => 52, 'R' => 53, 'S' => 54, 'T' => 55,
	 'U' => 56, 'V' => 57, 'W' => 58, 'X' => 59, 'Y' => 60, 'Z' => 61,
	 '-' => 62, '_' => 63, '.' => 64,
	);
	
	$str  = trim($str);
	
	//echo $str . "\n";
	
	$c = substr($str, 0, 1);
	if (strlen($str) > 1) {
		return $table[$c] * pow(64, strlen($str) - 1) + urlnumber_decode(substr($str, 1));
	} else {
		return $table[$c];
	}
}

/**
 * Removes a file if possible
 *
 * @param	string			$f				Filename
 */
function unlink_safe($f)
{
	if (file_exists($f) && is_writable(($f))) {
		unlink($f);
	}
}

/**
 * Inserts a wrapping string if a word is longer than $width
 *
 * @param	string			$string
 * @param	integer			$width			Maximum lenght of a word
 * @param	string			$wrap			String to insert
 * @return	string							Wrapped string
 */
function one_wordwrap( $string, $width, $wrap )
{
	$s=explode(" ", $string);
	$new_string = '';
	foreach ($s as $k => $v) {
		if(strlen($v) > $width) $v = wordwrap($v, $width, $wrap, true);
		$new_string .= $v . ' ';
	}
	return substr($new_string, 0, -1);
}

/**
 * Generates a copyright notice from $year to current year
 *
 * @param 	integer			$year			Starting year
 * @return	string							Copyright notice
 */
function copyright($year)
{
	if ($year < date("Y")) {
		$year .= '-' . date("Y");
	}
	return '<p id="copy">&copy; ' . $year . ' by <a href="http://blog.pew.cc">Daniel Triendl</a></p>';
}

/**
 * Generates the HTML output
 *
 * @param	string			$content		HTML content
 * @param	array			$opt			Options:
 * 											title		Title to display
 * 											lightbox	Insert lightbox JavaScript and css
 * 											header		Additional HTML headers
 */
function outputHTML($content, $opt = NULL) {
	$title = 'img.pew.cc - Image Hosting';
	$title = (isset($opt['title'])) ? $opt['title'] . ' - ' . $title : $title;

	header('Content-Type: text/html; charset=UTF-8');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
   "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
		<title><?php echo $title; ?></title>
		<link rel="stylesheet" type="text/css" href="style.css" />
<?php
	if (isset($opt['lightbox']) && $opt['lightbox']) {
?>
		<script type="text/javascript" src="lightbox/prototype.js"></script>
		<script type="text/javascript" src="lightbox/scriptaculous.js?load=effects,builder"></script>
		<script type="text/javascript" src="lightbox/lightbox.js"></script>
		<link rel="stylesheet" href="lightbox/lightbox.css" type="text/css" media="screen" />
<?php
	}
	if (isset($opt['header'])) echo $opt['header'];
?>
	</head>
	<body>
		<h1><a href="http://img.pew.cc">img.pew.cc</a></h1>
		<div id="content">
			<?php echo $content ?>
		</div>
		<?php echo copyright(2009); ?>
	</body>
</html>
<?php
}

?>