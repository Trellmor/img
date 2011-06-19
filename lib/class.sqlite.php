<?php
/**
 * class.sqlite.php
 * 
 * A simple class to interact with sqlite2 databases.
 * @author Daniel Triendl <daniel@pew.cc>
 * @version 1.1.0 $Id$
 * @package database
 * @license http://opensource.org/licenses/lgpl-3.0.html
 */


/**
 * class.sqlite.php A simple php class to interact with sqlite2 databases
 * Copyright 2007-2010 Daniel Triendl <daniel@pew.cc>
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
 * Dummy class for SQL Exceptions
 *
 * @package database
 */
class SQLException extends Exception {};

/**
 * SQLite Class
 *
 * This class is used to interact with a sqlite2 database
 * @package database
 * @subpackage sqlite
 */
class sqlite {
	protected $_dbhandle = NULL;
	private $_querynum = 0;

	/**
	 * Open a connection to the database
	 *
	 * @param	string		$dbname			Path and name of the database file
	 * @param	bool		$persistent		Use persistent handle
	 */
	public function __construct($dbname, $persistent = true)
	{
		if ($persistent) {
			// Use persistent connection if possible, saves use some time for opening the database
			$this->_dbhandle = @sqlite_popen($dbname, 0666, $error);
		} else {
			$this->_dbhandle = @sqlite_open($dbname, 0666, $error);
		}
		if ( !$this->_dbhandle ) {
			throw new SQLException('Can\'t open database connection: ' . $error);
		}
		// Use rowname instead of tablename.rowname for assoc array fetch
		$this->exec('PRAGMA short_column_names = ON');
	}
	
	/**
	 * Destructor
	 */
	public function __destruct()
	{
		$this->disconnect();
	}
	
	/**
	 * Execute a SQL query and returns a resource handle
	 *
	 * @param	string		$sql			SQL query to execute
	 * @return	resource
	 */
	public function query($sql)
	{
		$result = @sqlite_query($this->_dbhandle, $sql, SQLITE_ASSOC, $error);
		if(!$result) {
			// Something's wrong with the sqlite_last_error...
			throw new SQLException(sqlite_error_string(sqlite_last_error($this->_dbhandle)) . "\n" . $error . "\n" . $sql);
		}
		
		$this->_querynum++;
		return $result;
	}

	/**
	 * Executes a result-less SQL query
	 *
	 * @param	string		$sql			SQL query to execute
	 */
	public function exec($sql)
	{
		$result = @sqlite_exec($this->_dbhandle, $sql, $error);
		if(!$result) {
			throw new SQLException(sqlite_error_string(sqlite_last_error($this->_dbhandle)) . "\n" . $error . "\n" . $sql );
		}
		
		$this->_querynum++;
	}

	/**
	 * Fetches the next row from a result set as an array
	 *
	 * @param	resource	$result
	 * @return	array						Associative array
	 */
	public function fetch($result)
	{
		if (is_resource($result)) {
			$row = sqlite_fetch_array($result, SQLITE_ASSOC);
			return $row;
		} else {
			throw new SQLException( 'Got invalid ressource for sqlite::fetch()' );
		}
	}
	
	/**
	 * Returns the number of rows in a result set
	 *
	 * @param 	resource	$result
	 * @return	integer						Number of rows
	 */
	public function numrows($result)
	{
		if (is_resource( $result)) {
            return sqlite_num_rows($result);
        } else {
            throw new SQLException('Got invalid ressource for sqlite::numrows()');
        } 
	}
	
	/**
	 * This has no meaning for sqlite, it's here for  compatibility with other sql classes
	 *
	 * @param	resource		$result
	 * @return	bool
	 */
	public function free($result)
	{
		// No free?
		return true;
	}
	
	/**
	 * Closes and removes the database handle
	 * 
	 * This should not be used if you want to use persistent connections
	 */
	public function disconnect()
	{
		if ($this->_dbhandle) {
            sqlite_close($this->_dbhandle);
            $this->_dbhandle = NULL;
        } else {
			throw new SQLException('No sqlite database opened.');
        } 
	}

	/**
	 * Escapes a string for use as a query parameter
	 *
	 * @param	string		$string			The string to escape
	 * @return	string						The escaped string
	 */
	public function escape( $string )
	{
		// If magic_qoutes_gpc is on we need to removed the slashes to avoid excaping something 2 times
		if (get_magic_quotes_gpc()) {
	    	$string = stripslashes($string);
	    }
	    return sqlite_escape_string($string);
	}
}

?>
