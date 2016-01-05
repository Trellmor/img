<?php namespace Application\Crypto; 

class Utils {
	/**
	 * Constant time string comparison
	 *
	 * @param string $str1
	 * @param string $str2
	 *
	 * @return True if strings are equal
	 */
	public static function compareStr($str1, $str2) {
		$len1 = static::binaryStrlen($str1);
		$len2 = static::binaryStrlen($str2);
		$len = min($len1, $len2);
		$diff = $len1 ^ $len2;
	
		for ($i = 0; $i < $len; $i++) {
			$diff |= ord($str1[$i]) ^ ord($str2[$i]);
		}
	
		return $diff === 0;
	}
	
	/**
	 * Count the number of bytes in a string
	 *
	 * mbstring extension may overwrite strlen and return a strings character
	 * count instead of the number of bytes.
	 *
	 * @param string $str
	 *
	 * @return int The number of bytes
	 */
	public static function binaryStrlen($str) {
		if (function_exists('mb_Strlen')) {
			return mb_strlen($str, '8bit');
		}
		return strlen($str);
	}
	
	/**
	 * Get a substring based on byte limits
	 *
	 * @see binaryStrlen
	 *
	 * @param string $str Input string
	 * @param int $start
	 * @param int $length
	 *
	 * @return string The substring
	 */
	public static function binarySubstr($str, $start, $length) {
		if (function_exists('mb_substr')) {
			return mb_substr($str, $start, $length, '8bit');
		}
	
		return substr($string, $start, $length);
	}
}

?>