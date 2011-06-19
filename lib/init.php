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

require_once(__DIR__ . '/functions.php');
require_once(__DIR__ . '/config.php');
require_once(__DIR__ . '/class.sqlite.php');

$db = new sqlite(__DIR__ . '/db.sqlite');

session_start();

// Check if the user wants to be logged in automatically
if (!isLogin()) {
	if (isset($_COOKIE['openid_cookie'])) {
		if (get_magic_quotes_gpc()) $_COOKIE['openid_cookie'] = stripslashes($_COOKIE['openid_cookie']);
		list($identity, $cookie) = @unserialize($_COOKIE['openid_cookie']);
		$res = $db->query("SELECT count(*) as count FROM users WHERE user = '" . $db->escape($identity) . "' and cookie = '" . $cookie . "';");
		$row = $db->fetch($res);
		if ($row['count']) {
			$_SESSION['openid_identity'] = $identity;
			$db->exec("UPDATE users SET last_login = '" . time() . "' WHERE user = '" . $db->escape($identity) . "';");
		}
	}
}

?>
