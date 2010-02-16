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

require_once('lib/init.php');
require_once('lib/openid/class.openid.php');

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
	$_SESSION['openid_identity'] = '';
	session_destroy();
	if (isset($_COOKIE['openid_cookie'])) {
		setcookie('openid_cookie', NULL, 0);
	}
	errorMsg('You have been logged out.');
}

$oid = new OpenID();

if (isset($_POST['openid_identifier'])) {
	$oid->SetIdentifier($_POST['openid_identifier']);
	try {
		$oid->DiscoverEndpoint();
	} catch (OpenIDException $e) {
		// If we fail to discover an endpoint, exit
		errorMsg('OpenID endpoint not found.');
	}
	$oid->SetReturnTo(url() . 'login.php');
	$oid->SetRealm(url());
	if (isset($_POST['openid_remember']) && $_POST['openid_remember'] == 'remember') {
		$_SESSION['openid_remember'] = true;
	}
	$oid->RedirectUser();
	exit();
}

if ($oid->IsResponse()) {
	try {
		$mode = $oid->GetResponseMode();
		if ($mode == 'id_res') {
			if($oid->VerifyAssertion()) {
				$_SESSION['openid_identity'] = $oid->GetIdentifier();
				if (isset($_SESSION['openid_remember']) && $_SESSION['openid_remember']) {
					$cookie = md5($oid->GetIdentifier().microtime().mt_rand());
					$db->exec("INSERT OR REPLACE INTO users (
 user,
 cookie,
 last_login
) VALUES (
 '" . $db->escape($oid->GetIdentifier()) . "',
 '" . $db->escape($cookie) . "',
 '" . time() . "'
);");
					setcookie('openid_cookie', serialize(array($oid->GetIdentifier(), $cookie)), time() + 60 * 60 * 24 * 30);
				}
				errorMsg('Login successful.<br />You are now logged in as <a href="browse.php?user=' . urlencode($oid->GetIdentifier()) . '"><i>' . htmlentities($oid->GetIdentifier()) . '</i></a>', url());
			} else {
				session_destroy();
				errorMsg('Login failed.', url());
			}
		} else {
			session_destroy();
			errorMsg('Login failed: ' . $mode, url());
		}
	} catch (OpenIDException $e) {
		session_destroy();
		errorMsg('Login failed:' . $e->getMessage(), url());
	}
}

if (!empty($_SESSION['openid_identity'])) {
	outputHTML('You are logged in as <a href="browse.php?user=' . urlencode($_SESSION['openid_identity']) . '"><i>' . htmlentities($_SESSION['openid_identity']) . '</i></a><br /><br /><a href="login.php?action=logout">Logout</a>');
} else {
	$output = '<h2>OpenID Login</h2>
<form action="login.php" method="post">
<div id="login">
	<input type="text" name="openid_identifier" size="30" id="inputopenid_identifier" />
	<input type="submit" name="openid_submit" value="Login" id="inputopenid_submit" />
	<br />&nbsp;
</div>
</form>
';
	outputHTML($output, array('title' => 'Login'));
}

?>