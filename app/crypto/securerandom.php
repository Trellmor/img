<?php namespace Application\Crypto;

use Application\Exceptions\RNGException;

class SecureRandom {
	public function getBytes($count) {
		$bytes = '';
		if (function_exists('openssl_random_pseudo_bytes') &&
				(strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN')) {
			// Primary source for random bytes is the OpenSSL prng,
			// but OpenSSL is slow on windows, so avoid it there
			$bytes = openssl_random_pseudo_bytes($count);
		} else if (function_exists('mcrypt_create_iv')) {
			// Use mcrypt_create_iv to read bytes from /dev/urandom
			$bytes = mcrypt_create_iv($count, MCRYPT_DEV_URANDOM);
		} else if (is_readable('/dev/urandom') &&
				($hRand = @fopen('/dev/urandom', 'rb')) !== FALSE) {
			// Read from /dev/urandom directly if available
			$bytes = fread($hRand, $count);
			fclose($hRand);
		}
		
		if ($bytes === false || Utils::binaryStrlen($bytes) < $count) {
			throw new RNGException('Failed to get random bytes.');
		}		
		
		return $bytes;
	}
}

?>