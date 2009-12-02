<?php

/**
 * name		class.sqlite.php
 * begin	July 11, 2007
 * 
 * $Id: class.sqlite.php 27 2008-07-30 10:35:30Z trellmor $
 */

/**
 * yabs - yet another blog system
 * Copyright (C) 2007 Daniel Triendl
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
 * MA  02110-1301, USA
 *
 * http://www.gnu.org/copyleft/gpl.html
 */

class SQLException extends Exception { };

class sqlite {

	protected $_dbhandle = NULL;
	private $_querynum = 0;
	private $_author = 'Daniel Triendl';
	private $_version = '1.0.0';

	public function __construct( $dbname )
	{
		$this->_dbhandle = @sqlite_open( $dbname );
		if ( !$this->_dbhandle ) {
			throw new SQLException( 'Can\'t open database connection' );
		}
	}
	
	public function query( $sql = '' )
	{
		$sql = trim( $sql );
		$result = @sqlite_query( $this->_dbhandle, $sql, SQLITE_ASSOC, $error );
		if( !$result ) {
			// Something's wrong with the sqlite_last_error...
			throw new SQLException( sqlite_error_string( sqlite_last_error( $this->_dbhandle ) ) . "\n" . $error . "\n" . $sql );
		}
		
		$this->_querynum ++;
		return $result;
	}

	public function exec( $sql = '' )
	{
		$sql = trim( $sql );
		$error = '';
		$result = @sqlite_exec( $this->_dbhandle, $sql, $error );
		if( !$result ) {
			// Something's wrong with the sqlite_last_error...
			throw new SQLException( sqlite_error_string( sqlite_last_error( $this->_dbhandle ) ) . "\n" . $error . "\n" . $sql );
		}
		
		$this->_querynum ++;
		return $result;
	}

	public function fetch( $result )
	{
		if ( is_resource( $result ) ) {		
			// crap, why can't it strip everything befor a . on it's own?
			$row = sqlite_fetch_array( $result );
			if( is_array( $row ) ) {
				$return = array();
				foreach( $row as $key => $val ) {
					if( strpos( $key, '.' ) !== false ) {
						$key = explode( '.', $key );
						$key = $key[1];
					}
					$return[$key] = $val;
				}
				return $return;
			}
			return $row;
		} else {
			throw new SQLException( 'Got invalid ressource for sqlite::fetch()' );
		}
	}
	
	public function fetch_row( $result )
	{
		if ( is_resource( $result ) ) {
            $row = sqlite_fetch_array( $result );
            $return = array();
            foreach( $row as $single ) {
				$return[] = $singel;
            }
            return $return;
        } else {
            throw new SQLException( 'Got invalid ressource for sqlite::fetch_row()' );
        } 
	}
	
	public function numrows( $result )
	{
		if ( is_resource( $result ) ) {
            return sqlite_num_rows( $result );
        } else {
            throw new SQLException( 'Got invalid ressource for sqlite::numrows()' );
        } 
	}
	
	public function prev( $result )
	{
		if ( is_resource( $result ) ) {
			if( sqlite_has_prev( $result ) ) {
				sqlite_prev( $result );
			}
        } else {
            throw new SQLException( 'Got invalid ressource for sqlite::prev()' );
        } 
	}
	
	public function free( $result )
	{
		// No free?
		return true;
	}
	
	public function disconnect()
	{
		if ( $this->_dbhandle ) {
            sqlite_close( $this->_dbhandle );
            $this->_dbhandle = NULL;
        } else {
			throw new SQLException( 'No sqlite database opened.' );
        } 
	}

	public function affected_rows()
	{
		return sqlite_changes( $this->_dbhandle );
	}
	
	public function status( $element )
	{
		$return['file'] = __FILE__;
        $return['version'] = $this->_version;
        $return['author'] = $this->_author;
        $return['querynum'] = $this->_querynum;
        $return['database'] = 'sqlite';
        $return['errno'] = 0;
        $return['error'] = '';
        if ( $this->_dbhandle ) {
            $return['active'] = 1;
        } else {
            $return['active'] = 0;
        } 

        if ( $element !== false && isset( $return[$element] ) ) {
            return $return[$element];
        } else {
            return $return;
        }
	}

	public function escape( $string )
	{
		if ( get_magic_quotes_gpc() ) {
	    	$string = stripslashes( $string );
	    }
	    return sqlite_escape_string( $string );
	}
}

?>
