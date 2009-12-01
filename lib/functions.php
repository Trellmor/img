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
	die($msg);
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
	return 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/';
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

?>