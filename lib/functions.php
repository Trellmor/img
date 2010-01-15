<?php

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

function errorMsg($msg)
{
	outputHTML($msg . '<br /><br /><a href="javascript:history.back();">Return</a>');
	die();
}

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

function url() {
	$url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
	if ($url[strlen($url) - 1] != '/') {
		$url .= '/';
	}
	return $url;
}

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

function unlink_safe($f)
{
	if (file_exists($f) && is_writable(($f))) {
		unlink($f);
	}
}

function one_wordwrap( $string, $width, $wrap )
{
	$s=explode( " ", $string );
	$new_string = '';
	foreach( $s as $k => $v ) {
		if( strlen( $v ) > $width ) $v = wordwrap( $v, $width, $wrap, true );
		$new_string .= $v . ' ';
	}
	return $new_string;
}

function copyright($year)
{
	if ($year < date("Y")) {
		$year .= '-' . date("Y");
	}
	return '<p id="copy">&copy; ' . $year . ' by <a href="http://blog.pew.cc">Daniel Triendl</a></p>';
}

function outputHTML($content, $opt = NULL) {
	$title = 'img.pew.cc - Image Hosting';
	$title = (isset($opt['title'])) ? $opt['title'] . ' - ' . $title : $title;

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
   "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
		<title><?php echo $title; ?></title>
		<link rel="stylesheet" type="text/css" href="style.css" />
<?php
	if (isset($opt['lgihtbox']) && $opt['lightbox']) {
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