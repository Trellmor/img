<?php namespace DAL;

use Application\Registry;
class DAL {
	public static function Update_IncTagCount($tagId) {
		$sql = 'UPDATE tags SET count = count + 1 WHERE id = ?';
		$stmt = Registry::getInstance()->db->prepare($sql);
		$stmt->bindValue(1, $tagId, \PDO::PARAM_INT);
		$stmt->execute();
	}
	
	public static function Update_DecTagCount($imageId) {
		$sql = 'UPDATE tags SET count = count - 1 WHERE id IN (SELECT tag FROM imagetags WHERE image = ?)';
		$stmt = Registry::getInstance()->db->prepare($sql);
		$stmt->bindValue(1, $imageId, \PDO::PARAM_INT);
		$stmt->execute();
	}
	
	public static function Select_ImagesByTags($tags, $offset, $count) {
		$t = '';
		foreach ($tags as $tag) {
			if (!empty($t)) $t .= ',';
			$t .= Registry::getInstance()->db->quote($tag, \PDO::PARAM_STR);
		}
		
		$sql = '
SELECT
	i.id,
	i.location,
	i.path,
	i.original_name,
	i.ip,
	i.time,
	i.user,
	i.md5,
	i.uploadid,
	i.width,
	i.height,
	i.size
FROM
	images i
INNER JOIN imagetags it ON it.image = i.id
INNER JOIN tags t on t.id = it.tag
WHERE t.tag in (' . $t . ')
GROUP BY (i.id)
HAVING COUNT(*) = :tag_count
ORDER BY
	i.time DESC
LIMIT ' . (int)$offset . ', ' . (int)$count . ';';
		$stmt = Registry::getInstance()->db->prepare($sql);
		$stmt->bindValue(':tag_count', count($tags), \PDO::PARAM_INT);
		$stmt->execute();
		return $stmt;
	}
}