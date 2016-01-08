<?php namespace Models;

use DAL\QueryBuilder;

class Tag {
	private $id;
	private $tag;
	private $count;
	
	private static $columns = [
			't.id',
			't.tag',
			't.count'
	];
	
	public static function getTagsForImage($imageId) {	
		$qb = new QueryBuilder();
		$qb->table('imagetags it')->innerJoin('tags t', 't.id = it.tag');
		$qb->where('it.image = ?', [
				[$imageId, \PDO::PARAM_INT],
		]);
		
		$stmt = $qb->query(static::$columns);
		return $stmt->fetchAll(\PDO::FETCH_CLASS, __CLASS__);
	}
	
	public static function getMatchingTags($tag) {
		$qb = new QueryBuilder();
		$qb->table('tags t')->where('tag like ?', [$tag . '%'])->orderBy(['tag ASC'])->limit(10);
		$stmt = $qb->query(static::$columns);
		return $stmt->fetchAll(\PDO::FETCH_CLASS, __CLASS__);
	}
	
	public static function getTagsForImages($images) {
		$ids = '';
		foreach ($images as $image) {
			$ids .= (int)$image->getId() . ',';
		}
		$ids = substr($ids, 0, -1);
		
		$qb = new QueryBuilder();
		$qb->table('imagetags it')->innerJoin('tags t', 'it.tag = t.id');
		$qb->where('it.image in (' . $ids . ')', []);
		$qb->orderBy(['t.tag ASC']);
		$stmt = $qb->query(static::$columns, true);
		return $stmt->fetchAll(\PDO::FETCH_CLASS, __CLASS__);
	}
	
	public function getTag() {
		return $this->tag;
	}	
	
	public function getCount() {
		return $this->count;
	}
}