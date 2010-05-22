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
 * One Wordwrap an UTF-8 string
 * Original function by andrnag at yandex dot ru
 * See http://de.php.net/manual/en/function.wordwrap.php#94452
 * This function inserts a break character if a word is longer then width
 * Every space starts a new word
 *
 * @param 	sring			$str			Input string
 * @param	integer			$width			The column width. 
 * @param	string			$break			The line is broken using the optional break  parameter. 
 * @return	string							Returns the given string wrapped at the specified column. 
 */
function one_wordwrap($str, $width, $break)
{
	$str =  preg_split('/([\x20\r\n\t]++|\xc2\xa0)/sSX', $str, -1, PREG_SPLIT_NO_EMPTY);
	$return = '';
	foreach ($str as $val) {
		do {
			$return .= mb_substr($val, 0, $width, 'utf-8');
			if (mb_strlen($val, 'utf-8') > $width) $return .= $break;
			$val = mb_substr($val, $width, mb_strlen($val, 'utf-8') - $width, 'utf-8');
		} while ($val != '');
		$return .= ' ';
	}
	return mb_substr($return, 0, -1, 'utf-8');
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
	return '<p id="copy">&copy;' . $year . ' by <a href="http://pew.cc">Daniel Triendl</a></p>';
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
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1/jquery.min.js"></script>
		<script type="text/javascript">
$(document).ready(function() {
	$("#login_icon").click(function() {
		$("#login_form").show("slow");
	});
});
		</script>
		<link rel="stylesheet" type="text/css" href="css/style.css" />
<?php
	if (isset($opt['lightbox']) && $opt['lightbox']) {
?>
 		<script type="text/javascript" src="js/jquery.lightbox-0.5.min.js"></script>
 		<link rel="stylesheet" type="text/css" href="css/jquery.lightbox-0.5.css" media="screen" />
 		<script type="text/javascript">
$(function() {
	$('a[rel*=lightbox]').lightBox(); // Select all links that contains lightbox in the attribute rel
});
		</script>
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
		<p id="bug"><a href="http://dev.pew.cc/newticket">Report a bug</a></p>
		<?php echo copyright(2009); ?>
<?php
	if(!isLogin()) {
?>
		<div id="login_icon"><img src="images/openid-login-bg.gif" alt="OpenID Login" title="Login using OpenID" /> Login</div>
		<div id="login_form">
			<form action="login.php" method="post">
				<div>
					<input type="text" name="openid_identifier" id="inputopenid_identifier" /><br />
					<a id="openid_get" href="http://openid.net/get-an-openid/">Get an OpenID</a>
					<input type="submit" name="openid_submit" value="Login" id="inputopenid_submit" />
					<br style="clear: both;" />
					Stay logged in <input type="checkbox" name="openid_remember" value="remember" id="inputopenid_remember" />
				</div>
			</form>
		</div>
<?php
	}
	if (isLogin()) {
?>
		<div id="login_status">
			<a href="browse.php?user=<?php echo urlencode($_SESSION['openid_identity']) ?>"><i><?php echo htmlentities($_SESSION['openid_identity']) ?></i></a> <a href="login.php?action=logout">Logout</a>
		</div>
<?php
	}
?>
	</body>
</html>
<?php
}

function isLogin()
{
	return !empty($_SESSION['openid_identity']);
}

function isAdmin()
{
	global $admins;
	
	if (!isLogin()) return false;
	return in_array($_SESSION['openid_identity'], $admins);
}

/**
 * Fixes the odd indexing of multiple file uploads from the format:
 *
 * $_FILES['field']['key']['index']
 *
 * To the more standard and appropriate:
 *
 * $_FILES['field']['index']['key']
 *
 * @param array $files
 * @author Corey Ballou
 * @link http://www.jqueryin.com
 */
function fixFilesArray(&$files)
{
	$names = array( 'name' => 1, 'type' => 1, 'tmp_name' => 1, 'error' => 1, 'size' => 1);

	foreach ($files as $key => $part) {
		// only deal with valid keys and multiple files
		$key = (string) $key;
		if (isset($names[$key]) && is_array($part)) {
			foreach ($part as $position => $value) {
				$files[$position][$key] = $value;
			}
			// remove old key reference
			unset($files[$key]);
		}
	}
}

?>
