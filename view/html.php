<?php namespace View;

class HTML {
	public static function filter($var) {
		return filter_var($var, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
	}
	
	public static function out($var) {
		echo static::filter($var);
	}
	
	public static function strip($var) {
		return filter_var($var, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
	}
	
	public static function wordwrap($var) {
		$var = explode(' ', static::strip($var));
		$new_string = '';
		foreach ($var as $v) {
			if (strlen($v) > 15) {
				$v = wordwrap($v, 15, '<wbr />', true);
			}
			$new_string .= $v . ' ';
		}
		return $new_string;
	}
}

?>