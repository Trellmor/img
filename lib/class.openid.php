<?php
/**
 * class.openid.php
 * 
 * A simple class to handle OpenID 2.0 logins
 * @author Daniel Triendl <daniel@pew.cc>
 * @version 0.2.0 $Id$
 * @package login
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */

/**
 * class.openid.php A simple class to handle OpenID 2.0 logins
 * Copyright 2010 Daniel Triendl <daniel@pew.cc>
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 3.0 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>. 
 */

/**
 * Dummy class for OpenID Exceptions
 *
 * @package login
 */
class OpenIDException extends Exception {};

/**
 * OpenID Class
 *
 * This class is used to handle an OpenID login
 * @package login
 * @subpackage openid
 */
class OpenID {
	// The openid_* stuff should be merged into openid_parameters in the future
	protected	$openid_identifier;
	protected	$openid_identifier_type;
	protected	$openid_endpoint;
	protected	$openid_return_to;
	protected	$openid_realm;
	private		$user_agent					= 'Mozilla/5.0 (compatible; trell_openid; http://dev.pew.cc/wiki/openid)';
	protected	$openid_parameters			= array();
	
	/**
	 * Check if required extensions are loaded and initialize some data if
	 * available
	 *
	 */
	public function __construct()
	{
		// Check for required extensions
		if (!extension_loaded('cURL')) {
			throw new OpenIDException('cURL extension not available');
		}
		
		if (!extension_loaded('SimpleXML')) {
			throw new OpenIDException('SimpleXML extension not available.');
		}
		
		// Parse some info if present
		if ($this->IsResponse()) {
			if ($this->GetResponseMode() == 'id_res') {
				if (!empty($_GET['openid_identity'])) $this->openid_identifier = $this->NormalizeIdentifier(urldecode($_GET['openid_identity']));
				if (!empty($_GET['openid_claimed_id'])) $this->openid_identifier = $this->NormalizeIdentifier(urldecode($_GET['openid_claimed_id']));
				if (!empty($_GET['openid_return_to'])) $this->openid_return_to = urldecode($_GET['openid_return_to']);
				$this->ParseQuery();
			}
		}
	}
	
	/**
	 * Set OpenID Identity
	 *
	 * @param	string		$identifier
	 */
	public function SetIdentifier($identifier)
	{
		$this->openid_identifier = $this->NormalizeIdentifier($identifier);
	}
	
	/**
	 * Return OpenID Identity
	 *
	 * @return	string						OpenID Identity
	 */
	public function GetIdentifier()
	{
		return $this->openid_identifier;
	}
	
	/**
	 * Get OP Endpoint
	 *
	 * @return	string						OP Endpoint
	 */
	public function GetEndpoint()
	{
		return $this->openid_endpoint;
	}
	
	/**
	 * Set Op Endpoint
	 *
	 * @param	string		$endpoint
	 */
	public function SetEndpoint($endpoint)
	{
		$this->openid_endpoint = $endpoint;
	}
	
	/**
	 * Set Return_to url
	 *
	 * @param	string		$return_to		URL
	 */
	public function SetReturnTo($return_to)
	{
		$this->openid_return_to = $return_to;
	}
	
	/**
	 * Set the OpenID realm, see http://openid.net/specs/openid-authentication-2_0.html#realms
	 *
	 * @param	string		$realm
	 */
	public function SetRealm($realm)
	{
		$this->openid_realm = $realm;
	}
	
	/**
	 * Set a parameter
	 *
	 * @param	string		$parameter
	 * @param	string		$value
	 */
	public function SetParameter($parameter, $value)
	{
		$this->openid_parameters[$parameter] = $value;
	}
	
	/**
	 * Get the value of a parameter if available
	 *
	 * @param	string		$parameter
	 * @return	string						The value or empty if not set
	 */
	public function GetParameter($parameter)
	{
		if (isset($this->openid_parameters[$parameter])) return $this->openid_parameters[$parameter];
		else return '';
	}
	
	/**
	 * Get the URL where the user needs to be redirected to perform the loigin
	 *
	 * @return	string						URL
	 */
	public function GetRequestAuthentificationURL()
	{
		$url = parse_url($this->openid_endpoint);
		
		$url['query'] = (empty($url['query'])) ? '' : $url['query'] . '&';
		// Generate openid_parameters array from values
		$this->SetParameter('openid.ns', 'http://specs.openid.net/auth/2.0');
		if ($this->GetParameter('openid.mode') == '') $this->SetParameter('openid.mode', 'checkid_setup');
		
		if (empty($this->openid_identifier)) throw new OpenIDException('OpenID Identifier not set.');
		
		$this->SetParameter('openid.identity', $this->openid_identifier);
		$this->SetParameter('openid.claimed_id', $this->openid_identifier);
		
		if (!empty($this->openid_return_to)) $this->SetParameter('openid.return_to', $this->openid_return_to);
		if (!empty($this->openid_realm)) $this->SetParameter('openid.realm', $this->openid_realm);
		
		// Generate query from parameters
		foreach ($this->openid_parameters as $k => $v) {
			$url['query'] .= $k . '=' . urlencode($v) . '&';
		}
		
		$url['path'] = (empty($url['path'])) ? '/' : $url['path']; 
		return $url['scheme'] . '://' . $url['host'] . $url['path'] . '?' . $url['query'];
	}
	
	/**
	 * Redirects the USER to the OP endpoint.
	 * Uses HTTP H
	 *
	 */
	public function RedirectUser()
	{
		$url = $this->GetRequestAuthentificationURL();
		if (headers_sent()) {
			echo '<script type="text/javascript"> window.location="' . $url . '"; </script>';
		} else {
			header('Location: ' . $url);
		}
	}
	
	/**
	 * Try to discover the OpenID Endpoint (Server)
	 *
	 */
	public function DiscoverEndpoint()
	{
		// If its an xri, use xri.net to discover an XRDS document
		if ($this->openid_identifier_type == 'xri') {
			throw new OpenIDException('XRI Identifier endpoint discovery is not supported');
		}
		
		// If it's an url, use Yadis to find the Endpoint, if that fails, use HTML discovery
		if ($this->openid_identifier_type == 'url') {
			try {
				$xrds = $this->DiscoverXRDSByYadis($this->openid_identifier);
				$this->openid_endpoint = $this->DiscoverEndpointFromXRDS($xrds);
			} catch (OpenIDException $e) {
				throw new OpenIDException($e->getMessage());
				$this->openid_endpoint = $this->DiscoverEndpointFromHTML($this->openid_identifier);
			}
		}
	}
	
	/**
	 * Try to discover and OpenID Endpoint by using the Yadis protocol
	 * See http://yadis.org/wiki/Yadis_1.0_(HTML)
	 *
	 * @param	string		$url
	 * @return	string						XRDS document
	 */
	protected function DiscoverXRDSByYadis($url)
	{
		$c = curl_init($url);
		curl_setopt($c, CURLOPT_HEADER, true);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_TIMEOUT, 5);
		curl_setopt($c, CURLOPT_USERAGENT, $this->user_agent);
		// Ignore warnings about safe mode and/or open basedir
		@curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($c, CURLOPT_MAXREDIRS, 10);
		curl_setopt($c, CURLOPT_HTTPHEADER, array(
        	'Accept: application/xrds+xml',
    	));
   	
    	$data = curl_exec($c);
    	$head = explode("\r\n\r\n", trim(substr($data, 0, curl_getinfo($c, CURLINFO_HEADER_SIZE))));
    	$head = $head[count($head) - 1];
		$body = substr($data, curl_getinfo($c, CURLINFO_HEADER_SIZE));
    	
    	// cURL is not following redirects because of safe_mode or open_basedir
    	if (ini_get('safe_mode') || ini_get('open_basedir')) {
    		if (curl_getinfo($c, CURLINFO_HTTP_CODE) == 301 || curl_getinfo($c, CURLINFO_HTTP_CODE) == 302) {
	    		static $count = 0;
	    		
	    		if ($count > 10) {
	    			$count = 0;
	    			throw new OpenIDException('Maximum redirection count reached.');
	    		}
	    		
				$matches = array();
				preg_match('/Location:(.*?)\n/', $head, $matches);
				$url = @parse_url(trim(array_pop($matches)));
				if (!$url) {
					//couldn't process the url to redirect to
					$count = 0;
					throw new OpenIDException('Coudn\t process the url to redirect to.');
				}
				
				$last_url = parse_url(curl_getinfo($c, CURLINFO_EFFECTIVE_URL));
				
				if (empty($url['scheme'])) $url['scheme'] = $last_url['scheme'];
				if (strtolower($url['scheme']) != 'http' && strtolower($url['scheme']) != 'https') throw new OpenIDException('Invalid redirection target.');
				if (empty($url['host'])) $url['host'] = $last_url['host'];
				if (empty($url['path'])) $url['path'] = $last_url['path'];
				
				$new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . (!empty($url['query']) ? '?' . $url['query'] : '');
				
				$count++;
				return $this->DiscoverXRDSByYadis($new_url);
    		}
    	}
    	
		if (preg_match('/Content-Type: application\/xrds\+xml/', $head)) {
			// If it's a Yadis document, return it
			return $body;
		} elseif (preg_match('/X-XRDS-Location: (.*)/', $head, $match)) {
    		// Try to find the X-XRDS-Location Header
    		$xrds = trim($match[1]);
    	} else {
			// Try to find the X-XRDS-Location meta tag
    		preg_match_all('/<meta[^>]*http-equiv=[\'"]X-XRDS-Location[\'"][^>]*content=[\'"]([^\'"]+)[\'"][^>]*\/?>/i', $body, $matches1);
			preg_match_all('/<meta[^>]*content=\'"([^\'"]+)[\'"][^>]*http-equiv=[\'"]X-XRDS-Location[\'"][^>]*\/?>/i', $body, $matches2);
    		$matches = array_merge($matches1[1], $matches2[1]);
    		$xrds = '';
    		if (count($matches)) $xrds = trim($matches[0]);
    	}
    	
    	if (empty($xrds)) {
    		throw new OpenIDException('Can\'t find XRDS Locations');
    	}
    	
    	// Fetch xrds
    	curl_setopt($c, CURLOPT_URL, $xrds);
    	curl_setopt($c, CURLOPT_HEADER, false);
    	
    	$data = curl_exec($c);
    	
    	curl_close($c);
 
    	return($data);
	}
	
	/**
	 * Parses and XRDS document and returns and OpenID 2.0 Endpoint if found
	 *
	 * @param	string		$xrds			XRDS document
	 * @param	bool		$op_identifier	Accept http://specs.openid.net/auth/2.0/server Type too
	 * @return	string						OpenID 2.0 Endpoint
	 */
	protected function DiscoverEndpointFromXRDS($xrds)
	{
		$xml = new SimpleXMLElement($xrds);

		$xrd = $xml->XRD[count($xml->XRD)-1];

		$endpoint = '';
		
		// Search for OP Identifier, see http://openid.net/specs/openid-authentication-2_0.html 7.3.2.1.1
		foreach($xrd->Service as $service) {
			foreach ($service->Type as $type) {
				if ($type == 'http://specs.openid.net/auth/2.0/server') {
					if (isset($service->URI)) {
						// This is for OP Identifier, in this case use http://specs.openid.net/auth/2.0/identifier_select as identity
						$this->SetIdentifier('http://specs.openid.net/auth/2.0/identifier_select');
						return (string)$service->URI;
					}
				}
			}
		}
		
		// Search for claimed identifier, see http://openid.net/specs/openid-authentication-2_0.html 7.3.2.1.2
		foreach($xrd->Service as $service) {
			foreach ($service->Type as $type) {
				if ($type == 'http://specs.openid.net/auth/2.0/signon') {
					if (isset($service->URI)) {
						return (string)$service->URI;
					}
				}
			}
		}
		
		if (empty($endpoint)) throw new OpenIDException('No OpenID 2.0 Endpoint found.');
	}
	
	/**
	 * Parse the OpenID Identifier URL for OpenID endpoint info
	 *
	 * @return	string						OpenID 2.0 Endpoint
	 */
	protected function DiscoverEndpointFromHTML()
	{
		$c = curl_init($this->openid_identifier);
		curl_setopt($c, CURLOPT_HEADER, true);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_TIMEOUT, 5);
		curl_setopt($c, CURLOPT_USERAGENT, $this->user_agent);
		// Ignore warnings about safe mode and/or open basedir
		@curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
		curl_setopt($c, CURLOPT_MAXREDIRS, 10);

		$data = curl_exec($c);
    	$head = explode("\r\n\r\n", trim(substr($data, 0, curl_getinfo($c, CURLINFO_HEADER_SIZE))));
    	$head = $head[count($head) - 1];
		$data = substr($data, curl_getinfo($c, CURLINFO_HEADER_SIZE));
    	
    	// cURL is not following redirects because of safe_mode or open_basedir
    	if (ini_get('safe_mode') || ini_get('open_basedir')) {
    		if (curl_getinfo($c, CURLINFO_HTTP_CODE) == 301 || curl_getinfo($c, CURLINFO_HTTP_CODE) == 302) {
	    		static $count = 0;
	    		
	    		if ($count > 10) {
	    			$count = 0;
	    			throw new OpenIDException('Maximum redirection count reached.');
	    		}
	    		
				$matches = array();
				preg_match('/Location:(.*?)\n/', $head, $matches);
				$url = @parse_url(trim(array_pop($matches)));
				if (!$url) {
					//couldn't process the url to redirect to
					$count = 0;
					throw new OpenIDException('Coudn\t process the url to redirect to.');
				}
				
				$last_url = parse_url(curl_getinfo($c, CURLINFO_EFFECTIVE_URL));
				
				if (empty($url['scheme'])) $url['scheme'] = $last_url['scheme'];
				if (strtolower($url['scheme']) != 'http' && strtolower($url['scheme']) != 'https') throw new OpenIDException('Invalid redirection target.');
				if (empty($url['host'])) $url['host'] = $last_url['host'];
				if (empty($url['path'])) $url['path'] = $last_url['path'];
				
				$new_url = $url['scheme'] . '://' . $url['host'] . $url['path'] . (!empty($url['query']) ? '?' . $url['query'] : '');
				
				$count++;
				return $this->DiscoverEndpointFromHTML($new_url);
    		}
    	}
		
		curl_close($c);
		
		preg_match_all('/<link[^>]*rel=[\'"]openid2.provider[\'"][^>]*href=[\'"]([^\'"]+)[\'"][^>]*\/?>/i', $data, $matches1);
		preg_match_all('/<link[^>]*href=\'"([^\'"]+)[\'"][^>]*rel=[\'"]openid2.provider[\'"][^>]*\/?>/i', $data, $matches2);
		$providers = array_merge($matches1[1], $matches2[1]);
		if (count($providers)) {
			$provider = trim($providers[0]);
		}
		
		if (empty($provider)) throw new OpenIDException('No OpenID 2.0 Endpoint found.');
		
		return $provider;
	}
	
	/**
	 * Normalizes an OpenID Identifier
	 * See http://openid.net/specs/openid-authentication-2_0.html#normalization
	 *
	 * @param	string		$identifier
	 * @return	string						Normalized identifier
	 */
	protected function NormalizeIdentifier($identifier)
	{
		$identifier = trim($identifier);
		
		if (substr(strtolower($identifier), 0, 6) == 'xri://') {
			$identifier = substr($identifier, 6);
		}

		// Identifier starts with a XRI Global Context Symbol, see http://www.oasis-open.org/committees/download.php/15376 2.2.1 
		if (in_array(substr($identifier, 0, 1), array('@', '=', '+', '$', '!', '('))) {
			$this->openid_identifier_type = 'xri';
			
			return $identifier;
		}
		
		// Treat as URL
		$this->openid_identifier_type = 'url';
		
		// If it doesn't start with http or https, prefix it with http
		if (!preg_match('/^http(s)?:\/\//i', $identifier)) {
			$identifier = 'http://' . $identifier;
		}
		
		// Strip of fragment part
		if (($pos = strpos($identifier, '#')) !== false) {
			$identifier = substr($identifier, 0, $pos - 1);
		}
		
		// Ok, parse the url to see if we need to append a trailing slash
		$parse = parse_url($identifier);
		if (!isset($parse['path'])) {
			$identifier .= '/';
		}
		
		return $identifier;
	}
	
	/**
	 * Used to verify the login after the server redirected the user back to
	 * us with an positive assertion 
	 *
	 * @return	bool						true if OP verified the login
	 */
	public function VerifyAssertion()
	{
		if (!$this->IsResponse()) throw new OpenIDException('No server response found.');
		if (!isset($_GET['openid_mode']) || $_GET['openid_mode'] != 'id_res') throw new OpenIDException('Login wasn\'t successfull.');
	
		if (!$this->VerifyURL($this->openid_return_to)) throw new OpenIDException('openid.return URL didn\'t match.');
		
		// Check if OP is authorized to make assertions about this user
		// If this is not empty, assume that the value is from a previous lookup and correct
		if (empty($this->openid_endpoint)) {
			$this->DiscoverEndpoint();
		}
		if(!isset($_GET['openid_op_endpoint']) || $this->openid_endpoint != urldecode($_GET['openid_op_endpoint'])) {
			throw new OpenIDException('Endpoint didn\'t match discovered endpoint.');
		}
		
		$post = $this->openid_parameters;
		$post['openid.mode'] = 'check_authentication';
		$c = curl_init($this->openid_endpoint);
		curl_setopt($c, CURLOPT_HEADER, false);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($c, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($c, CURLOPT_TIMEOUT, 5);
		curl_setopt($c, CURLOPT_USERAGENT, $this->user_agent);
		curl_setopt($c, CURLOPT_POSTFIELDS, $post);
		
		$data = curl_exec($c);
		curl_close($c);
		$data = $this->ParseResponse($data);

		if (!isset($data['ns']) || $data['ns'] != 'http://specs.openid.net/auth/2.0') throw new OpenIDException('Invalid NS in response.');
		
		if (!isset($data['is_valid'])) throw new OpenIDException('Server didn\'t say if the login is valid.');
		
		return ($data['is_valid'] == 'true') ? true : false;
	}
	
	/**
	 * Parse a direct request response to an array
	 *
	 * @param	sting		$string			Response data
	 * @return	array						Response array
	 */
	protected function ParseResponse($string)
	{
		$string = explode("\n", $string);
		$array = array();
		foreach ($string as $s) {
			trim($s);
			$s = explode(':', $s);
			$key = $s[0];
			unset($s[0]);
			$val = implode(':', $s);
			$array[$key] = $val;
		}
		return $array;
	}
	
	/**
	 * Verify that url matches the current page URL as specified in 
	 * http://openid.net/specs/openid-authentication-2_0.html#verify_return_to
	 *
	 * @param	string		$url
	 * @return	bool
	 */
	protected function VerifyURL($url)
	{
		$url = parse_url($url);
		
		if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') {
			if ($url['scheme'] != 'https') return false;
		} else {
			if ($url['scheme'] != 'http') return false;
		}
		
		if ($_SERVER['HTTP_HOST'] != $url['host']) return false;
		
		if (!isset($url['path'])) $url['path'] = '';
		if ($_SERVER['PHP_SELF'] != $url['path']) return false;

		// Analyze query
		if (!empty($url['query'])) {
					echo "query";
			$query = explode('&', $url['query']);
			if (($pos = strpos($_SERVER["REQUEST_URI"], '&')) !== false) {
				$query2 = substr($_SERVER['REQUEST_URI'], $pos);
				$query2 = explode('&', $query);
			}
			foreach ($query as $q) {
				if (!in_array($q, $query2)) return false;
			}
		}
		
		return true;	
	}
	
	/**
	 * Check if this is a reply from an OP
	 *
	 * @return	bool
	 */
	public function IsResponse()
	{
		if (isset($_GET['openid_ns']) && urldecode($_GET['openid_ns']) == 'http://specs.openid.net/auth/2.0')
			return true;
		else
			return false;
	}
	
	/**
	 * Returns the openid.mode if set by the server response
	 *
	 * @return	string						Response mode
	 * 											Possible modes:
	 * 												id_res			Login successful, if this is set, proceed further
	 *	 											setup_needed	Login failed, retry with checkid_setup insted of checkid_immediate
	 * 												cancel			User canceled login
	 * 											
	 */
	public function GetResponseMode()
	{
		if (!$this->IsResponse()) throw new OpenIDException('No server response found.');
		if (empty($_GET['openid_mode'])) throw new OpenIDException('openid.mode not found in server response, maybe the server implementation is faulty?');
		return $_GET['openid_mode'];
	}
	
	/**
	 * Parse the QUERY_STRING parameter to get the correct array indices
	 *
	 */
	protected function ParseQuery()
	{
		if (!$this->IsResponse()) throw new OpenIDException('No server response found.');
		
		$query = explode('&', $_SERVER['QUERY_STRING']);
		foreach ($query as $q) {
			$q = explode('=', $q);
			$k = $q[0];
			if (substr($k, 0, 7) == 'openid.') {
				unset($q[0]);
				$v = urldecode(implode('=', $q));
				$this->openid_parameters[$k] = $v;
			}
		}
	}
	
	/**
	 * Get the namespace for an extension uri
	 *
	 * @param	string		$uri
	 * @return	string						The namespace
	 */
	public function GetNamespace($uri)
	{
		foreach ($this->openid_parameters as $k => $v) {
			if (substr($k, 0, 10) == 'openid.ns.') {
				
				if ($v == $uri) {
					return substr($k, 10);
				}
			}
		}
		throw new OpenIDException('Namepspace not found.');
	}
}

?>