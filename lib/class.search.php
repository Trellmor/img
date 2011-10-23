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

require_once(__DIR__ . '/class.DAL.php');
require_once(__DIR__ . '/class.browse.php');

class search extends browse {
	public function __construct(PDO $pdo) {
		parent::__construct($pdo);
	}

	public static function image_sort($a, $b) {
		if ($a->count == $b->count) return 0;
		//Sort reverse
		return ($a->count > $b->count) ? -1 : +1;
	}
	
	public function search($searchterms) {
		$search = $this->prepareTags($searchterms);
		$stmt = DAL::Select_Images_Search($this->pdo(), $search);
		$stmt->setFetchMode(PDO::FETCH_CLASS, 'image', array($this->pdo()));
		$stmt->execute();
		$images = $stmt->fetchAll();
		$images = $this->image_unique($images);
		usort($images, array('search', 'image_sort'));
		return $images;
	}
	
	protected function image_unique($images) {
		$return = array();
		
		foreach ($images as $image) {
			if (($search = $this->image_search($image, $return)) === false) {
				$return[] = $image;
			} else {
				$search->count++;
			}
		}
		return $return;
	}
	
	protected function image_search($needle, $haystack) {
		foreach ($haystack as $i) {
			if ($i->id == $needle->id) return $i; 
		}		
		return false;
	}
	
	protected function prepareTags($tags) {
		$tags = str_replace(' ', ',', $tags);
		$tags = parent::prepareTags($tags);
		
		foreach($tags as $key => $tag) {
			$tags[$key] = '%' . strtolower($tag) . '%';
		}
		
		array_unique($tags);
		
		return $tags;
	}
}

?>