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


if (isset($_GET['tag'])) {
	
	require_once('lib/init.php');
	
	// Find all tags that start with the search string
	$stmt = DAL::Select_TagSuggestions($pdo, $_GET['tag']);
	$stmt->execute();
	
	$tags = array();
	while(($tag = $stmt->fetchColumn(0)) !== false) {
		$tags[] = htmlentities($tag, ENT_QUOTES, 'UTF-8');
	}
	
	// Send results
	echo json_encode($tags);
}

?>