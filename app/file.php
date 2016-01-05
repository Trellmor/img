<?php namespace Application;

class File {	
	public static function unlink($filename) {
		if (file_exists($filename) && is_writable(($filename))) {
			return unlink($filename);
		}
		return false;
	}
	
	public static function move_uploaded_file($filename, $destination)
	{
		$dir = dirname($destination);
		if (!file_exists($dir)) {
			if(!mkdir($dir, 0755, true)) {
				return false;
			}
		}
	
		return move_uploaded_file($filename, $destination);
	}	
	
	public static function getUniqueueName($filename)
	{
		static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		if(file_exists($filename)) {
			$filename = explode('.', $filename);
			$filename[count($filename) - 2] .= $chars[rand(0, strlen($chars) - 1)];
			$filename = static::getUniqueueName(implode('.', $filename));
		}
		return $filename;
	}
}