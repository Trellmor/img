<?php namespace Application;

use Composer\Script\Event;

class AssetManager {
	private $root;
	
	private static $assets = [
			'css' => [
					'twbs/bootstrap/dist/css/bootstrap.min.css',
					'select2/select2/dist/css/select2.min.css',
					'npm-asset/select2-bootstrap-theme/dist/select2-bootstrap.min.css',
					'npm-asset/blueimp-gallery/css/blueimp-gallery.min.css',					
			],
			'fonts' => [
					'twbs/bootstrap/dist/fonts/glyphicons-halflings-regular.eot',
					'twbs/bootstrap/dist/fonts/glyphicons-halflings-regular.svg',
					'twbs/bootstrap/dist/fonts/glyphicons-halflings-regular.ttf',
					'twbs/bootstrap/dist/fonts/glyphicons-halflings-regular.woff',
					'twbs/bootstrap/dist/fonts/glyphicons-halflings-regular.woff2',
			],
			'js' => [
					'twbs/bootstrap/dist/js/bootstrap.min.js',
					'npm-asset/blueimp-gallery/js/jquery.blueimp-gallery.min.js',
					'moxiecode/plupload/js/plupload.full.min.js',
					'select2/select2/dist/js/select2.min.js',
			],
			'img' => [
					'npm-asset/blueimp-gallery/img/loading.gif',	
			],
	];
	
	public function __construct() {
		$this->root = realpath(__DIR__ . '/../');
	}
	
	
	public static function Build(Event $event) {
		$am = new AssetManager();
		$am->dump();
	}
	
	public function dump() {
		foreach (static::$assets as $folder=>$assets) {
			$this->dumpFolder($folder, $assets);
		}
	}
	
	private function dumpFolder($folder, $assets) {
		$folder = $this->root . '/public/' . $folder;
		if (!file_exists($folder)) {
			mkdir($folder);
		}
		
		foreach ($assets as $asset) {
			$this->copyAsset($asset, $folder);
		}
	}
	
	private function copyAsset($asset, $destfolder) {
		$asset = $this->root . '/vendor/' . $asset;
		
		if (!file_exists($asset)) {
			throw new \Exception('Asset missing: ' . $asset);
		}
		
		$name = basename($asset);
		$dest = $destfolder . '/' . $name;
		if (!copy($asset, $dest)) {
			throw new \Exception('Failed to copy ' . $asset);
		}
	}
}

?>