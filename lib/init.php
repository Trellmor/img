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
error_reporting(E_ALL);

header('Content-Type: text/html; charset=UTF-8');

class ImgException extends Exception {};

require_once(__DIR__ . '/functions.php');
require_once(__DIR__ . '/config.php');

if ($debug)	error_reporting(0);
else error_reporting(E_ALL);
set_error_handler(create_function('$a, $b, $c, $d', 'throw new ErrorException($b, 0, $a, $c, $d);'), E_ALL);
set_exception_handler('exception_handler');

require_once(__DIR__ . '/class.DAL.php');

try {
	$pdo = new PDO($connection_string, $dbuser, $dbpass, array(PDO::ATTR_PERSISTENT => true));
} catch (PDOException $e) {
	exception_handler($e); //Log exception
	errorMsg('Database connection error.');
}
unset($dbuser);
unset($dbpass);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

session_start();

// Check if the user wants to be logged in automatically
if (!isLogin()) {
	if (isset($_COOKIE['openid_cookie'])) {
		if (get_magic_quotes_gpc()) $_COOKIE['openid_cookie'] = stripslashes($_COOKIE['openid_cookie']);
		list($identity, $cookie) = @unserialize($_COOKIE['openid_cookie']);
		$stmt = DAL::Select_User_Cookie($pdo, $identity, $cookie);
		$stmt->execute();
		if ($stmt->fetch() !== false) {
			$_SESSION['openid_identity'] = $identity;
			DAL::Update_User_Lastlogin($pdo, $identity)->execute();
		}
		$stmt->closeCursor();
	}
}

?>
