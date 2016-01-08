<?php namespace Models;

use Application\File;
use Application\Registry;
use DAL\QueryBuilder;
use DAL\DAL;
use Application\Exceptions\ValidationException;

class Image {
	public $id;
	public $location;
	public $path;
	public $original_name;
	public $ip;
	public $time;
	public $user;
	public $md5;
	public $uploadid;
	public $size;
	public $width;
	public $height;
	
	private static $columns = [
			'i.id',
			'i.location',
			'i.path',
			'i.original_name',
			'i.ip',
			'i.time',
			'i.user',
			'i.md5',
			'i.uploadid',
			'i.width',
			'i.height',
			'i.size'
	];
	
	public static function getImageById($id) {
		$qb = new QueryBuilder();
		$qb->table('images i');
		$qb->where('i.id = ?', [[$id, \PDO::PARAM_INT]]);
		$stmt = $qb->query(static::$columns);
		return $stmt->fetchObject(__CLASS__);
	}
	
	public static function getImageByEncodedId($id) {
		$id = static::urlnumber_decode($id);
		return static::getImageById($id);
	}
	
	public static function getImagesByTags($tags, $offset) {
		if (count($tags) == 0) {
			throw new ValidationException(_('No tags'));
		}
		
		$stmt = DAL::Select_ImagesByTags($tags, $offset, Registry::getInstance()->config['pagelimit']);
		return $stmt->fetchAll(\PDO::FETCH_CLASS, __CLASS__);
	}
	
	public static function getImagesByUploadId($uploadId) {
		$qb = new QueryBuilder();
		$qb->table('images i');
		$qb->where('uploadid = ?', [$uploadId]);
		$qb->orderBy(['i.id ASC']);
		$stmt = $qb->query(static::$columns);
		return $stmt->fetchAll(\PDO::FETCH_CLASS, __CLASS__);
	}

	public function getId() {
		return $this->id;
	}
	
	public function getLocation() {
		return $this->location;
	}
	
	public function getPreview() {
		return dirname($this->location) . '/preview/' . basename($this->location);
	}
	
	public function getOriginalName() {
		return $this->original_name;
	}
	
	public function getEncodedId() {
		return $this->urlnumber_encode($this->id);
	}
	
	public function getUploadId() {
		return $this->uploadid;
	}
	
	public function getPath() {
		return $this->path;
	}
	
	public function getSize() {
		return $this->size;
	}
	
	public function getWidth() {
		return $this->width;
	}
	
	public function getHeight() {
		return $this->height;
	}
	
	public function getTime() {
		return $this->time;
	}
	
	public function getUser() {
		return $this->user;
	}
	
	public function getFormattedSize() {
	    static $s = ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB'];

        if ($this->size != 0) {
        	$e = floor(log($this->size) / log(1024));
        	return sprintf('%.2f '.$s[$e], ($this->size / pow(1024, floor($e))));
        } else {
        	$e = 0;
        	return sprintf('%.2f '.$s[$e], $this->size);
        }
	}

	public function delete() {
		Registry::getInstance()->db->beginTransaction();
		try {
			DAL::Update_DecTagCount($this->id);
			
			$qb = new QueryBuilder();
			$qb->table('imagetags')->where('image = ?', [[$this->id, \PDO::PARAM_INT]])->delete();
			
			$qb = new QueryBuilder();
			$qb->table('tags')->where('count < 1', [])->delete();
			

			$qb = new QueryBuilder();
			$qb->table('images')->where('id = ?', [[$this->id, \PDO::PARAM_INT]])->delete();

			File::unlink(APP_ROOT . '/' . $this->path);
			File::unlink(dirname(APP_ROOT . '/' . $this->path) . '/preview/' . basename($this->path));
			
			Registry::getInstance()->db->commit();
		} catch (\PDOException $e) {
			Registry::getInstance()->db->rollBack();
			throw $e;
		}
	}
	
	public function upload($file, $tags, $uploadid) {
		$info = getimagesize($file['tmp_name']);
		
		if (!isset(Registry::getInstance()->config['mime'][$info['mime']])) {
			File::unlink($file['tmp_name']);
			throw new ValidationException(_('Imagetype not allowed.'));
		}
		
		$this->md5 = md5_file($file['tmp_name']);
		
		// Assign the correct extension for this image
		$name = str_replace('\'', '', basename($file['name']));
		$name = str_replace(':', '', $name);
		$name = explode('.', $name);
		
		if(count($name) < 2) {
			$name = $name[0] . '.' . Registry::getInstance()->config['mime'][$info['mime']];
		} else {
			$name[count($name) - 1] = Registry::getInstance()->config['mime'][$info['mime']];
			$name = implode('.', $name);
		}
		
		// Generate a URL save string to send to the browser
		$location = trim(str_replace('//', '/', Registry::getInstance()->config['imgdir'] . '/'));
			
		// Choose the location for the file
		$realpath = APP_ROOT + '/public/' . Registry::getInstance()->config['imgdir'] . '/' . $name;
		$realpath = File::getUniqueueName($realpath);

		$this->path =  str_replace(APP_ROOT + '/', '', $realpath, 1);

		$location .= basename($this->path);
		$location = explode('/', $location);
		for ($i = 0; $i < count($location); $i++) {
			$location[$i] = rawurlencode($location[$i]);
		}
		$this->location = implode('/', $location);
		
		// Move the file to it's new location
		if (!File::move_uploaded_file($file['tmp_name'], $realpath)) {
			File::unlink($file['tmp_name']);
			throw new UploadException('Can\'t move uploaded file.');
		}
		
		/*
		* Create preview
		*
		* We use imagemagick because it suports a broad range of file
		* types
		*
		* Also, we call it directly with exec
		*
		* See http://www.imagemagick.org/Usage/thumbnails/ for more
		* information about the commands used
		*/
		$w = Registry::getInstance()->config['preview']['width'];
		$h = Registry::getInstance()->config['preview']['height'];
		$preview = dirname($realpath) . '/preview/' . basename($realpath);
		if (!file_exists(dirname($preview))) mkdir(dirname($preview), 0755);
		exec ('convert -define jpeg:size=' . $w * 2 . 'x' . $h * 2 . ' \\
		  ' . escapeshellarg($realpath) . '[0] -thumbnail ' . $w . 'x' . $h . ' \\
		 -unsharp 0x.5 -strip ' . escapeshellarg($preview) . '');

		// This is to make sure the image contains no privacy releated tags or anything
		exec('mogrify -strip ' . escapeshellarg($realpath) . '');
		
		$this->ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '0.0.0.0'; 
		
		$qb = new QueryBuilder();
		Registry::getInstance()->db->beginTransaction();
		try {
			$qb->table('images');
			
			$this->id = $qb->insert([
					'location' => $this->location,
					'path' => $this->path,
					'original_name' => $file['name'],					
					'ip' => $this->ip,
					'time' => [time(), \PDO::PARAM_INT],
					'user' => (isset(Registry::getInstance()->user)) ? Registry::getInstance()->user->getId() : null,
					'md5' => $this->md5,
					'uploadid' => $uploadid,
					'width' => $info[0],
					'height' => $info[1],
					'size' => filesize ($this->path),
			]);
			
			$this->saveTags($tags);
			
			Registry::getInstance()->db->commit();
		} catch(\PDOException $e) {
			File::unlink($this->path);
			File::unlink($this->getPreview($this->path));
			Registry::getInstance()->db->rollBack();
			throw $e;
		}
	}
	
	private function saveTags($tags) {
		for ($i = 0; $i < count($tags); $i++) {
			$tags[$i] = trim($tags[$i]);
		}
		
		$qb = new QueryBuilder();
		$qb->table('imagetags it')->innerJoin('tags t', 't.id = it.tag');
		$stmt = $qb->where('it.image = ?', [[$this->id, \PDO::PARAM_INT]])->query(['t.tag']);
		$tags = array_merge($tags, $stmt->fetchAll(\PDO::FETCH_COLUMN, 0));
		$tags = $this->array_iunique($tags);

		foreach($tags as $tag) {
			if (empty($tag)) continue;
			
			// check if the tag already exists
			$qb = new QueryBuilder();
			$stmt = $qb->table('tags')->where('tag = ?', [$tag])->query(['id']);
			if (($tagid = $stmt->fetchColumn(0)) === false) {
				// Tag doesn't exist
				$qb = new QueryBuilder();
				$tagid = $qb->table('tags')->insert([
						'tag' => $tag,
						'count' => 0
				]);
			}
			
			// Check if Imagetag exists
			$qb = new QueryBuilder();
			$qb->table('imagetags')->where('image = ? and tag = ?', [
					[$this->id, \PDO::PARAM_INT],
					[$tagid, \PDO::PARAM_INT],
			]);
			$stmt = $qb->query(['id']);
			if ($stmt->fetch() === false) {
				$qb = new QueryBuilder();
				$qb->table('imagetags')->insert([
						'image' => [$this->id, \PDO::PARAM_INT],
						'tag' => [$tagid, \PDO::PARAM_INT]
				]);
						
				DAL::Update_IncTagCount($tagid);
			}			
		}
	}
	
	
	private static function urlnumber_encode($number)
	{
		//0-9 = 0-9
		//a-z = 10-35
		//A-Z = 36-61
		//$-_.+!*'(), = 62-64
		static $table = array(
				'0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
				'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
				'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
				'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
				'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
				'-', '_', '.',
		);
	
		$r = $number % 64;
		if ($number - $r == 0) {
			return $table[$r];
		} else {
			return static::urlnumber_encode((($number - $r) / 64)) . $table[$r];
		}
	}
	
	private static function urlnumber_decode($str)
	{
		static $table = array(
				'0' => 0, '1' => 1, '2' => 2, '3' => 3, '4' => 4,
				'5' => 5, '6' => 6, '7' => 7, '8' => 8, '9' => 9,
				'a' => 10, 'b' => 11, 'c' => 12, 'd' => 13, 'e' => 14,
				'f' => 15, 'g' => 16, 'h' => 17, 'i' => 18, 'j' => 19,
				'k' => 20, 'l' => 21, 'm' => 22, 'n' => 23, 'o' => 24,
				'p' => 25, 'q' => 26, 'r' => 27, 's' => 28, 't' => 29,
				'u' => 30, 'v' => 31, 'w' => 32, 'x' => 33, 'y' => 34, 'z' => 35,
				'A' => 36, 'B' => 37, 'C' => 38, 'D' => 39, 'E' => 40,
				'F' => 41, 'G' => 42, 'H' => 43, 'I' => 44, 'J' => 45,
				'K' => 46, 'L' => 47, 'M' => 48, 'N' => 49, 'O' => 50,
				'P' => 51, 'Q' => 52, 'R' => 53, 'S' => 54, 'T' => 55,
				'U' => 56, 'V' => 57, 'W' => 58, 'X' => 59, 'Y' => 60, 'Z' => 61,
				'-' => 62, '_' => 63, '.' => 64,
		);
	
		$str  = trim($str);
	
		$c = substr($str, 0, 1);
		if (strlen($str) > 1) {
			return $table[$c] * pow(64, strlen($str) - 1) + static::urlnumber_decode(substr($str, 1));
		} else {
			return $table[$c];
		}
	}
	
	private function in_iarray($str, $a){
		foreach ($a as $v) {
			if (strcasecmp($str, $v)==0) return true;
		}
		return false;
	}
	
	private function array_iunique($a){
		$n = array();
		foreach ($a as $k=>$v) {
			if (!$this->in_iarray($v, $n)) $n[$k]=$v;
		}
		return $n;
	}
}