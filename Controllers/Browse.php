<?php namespace Controllers;

use Models\Image;
use Models\Tag;
use Application\Registry;
use Application\Uri;
use Application\Input;

class Browse extends Controller {
	public function search() {
		$this->view->load('search');
	}
	
	public function performSearch() {
		$input = new Input('POST');
		$input->filter('tags', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_REQUIRE_ARRAY);
		
		if ($input->tags === false) {
			$this->error(200, _('Invalid search string'));
			return;
		}
		
		$this->redirect(Uri::to('tags/') . $this->encodeTags($input->tags) . '/');
	}
	
	public function tags($tags, $page = 1) {
		try {
			$offset = ($page - 1) * Registry::getInstance()->config['pagelimit'];
			
			$tags = $this->decodeTags($tags);			
			
			$images = Image::getImagesByTags($tags, $offset);
			if (count($images) > 0) {
				$imagetags = Tag::getTagsForImages($images);

				$this->view->assignVar('images', $images);
				$this->view->assignVar('tags', $imagetags);
				$this->view->assignVar('page', $page);
				$this->view->assignVar('pagelimit', Registry::getInstance()->config['pagelimit']);
				$this->view->assignVar('nextpage', Uri::to('tags/') . $this->encodeTags($tags) . '/' . ($page + 1) . '/');
				$this->view->assignVar('prevpage', Uri::to('tags/') . $this->encodeTags($tags) . '/' . ($page - 1) . '/');
				$this->view->load('images');
			} else {
				$this->error(200, _('No images found'));
			}
		} catch (\Exception $e) {
			$this->error(200, $e->getMessage());
		}
	}
	
	public function album($uploadId) {
		try {
			$uploadId = urldecode($uploadId);
			$images = Image::getImagesByUploadId($uploadId);
			if (count($images) > 0) {
				$tags = Tag::getTagsForImages($images);
				
				$this->view->assignVar('images', $images);
				$this->view->assignVar('tags', $tags);
				$this->view->assignVar('page', 0);
				$this->view->load('images');
			} else {
				$this->error(200, _('No images found'));
			}
		} catch (\Exception $e) {
			$this->error(200, $e->getMessage());
		}
	}
	
	private function encodeTags($tags) {
		$search = '';
		foreach ($tags as $tag) {
			$tag = trim($tag);
			if (!empty($tag)) {
				$search .= Tag::encodeTag($tag) . ' ';
			}
		}
		return substr($search, 0, -1);
	}
	
	private function decodeTags($tags) {		
		$tags = explode(' ', $tags);
		
		for ($i = 0; $i < count($tags); $i++) {
			$tags[$i] = Tag::decodeTag($tags[$i]);
			if (empty($tags[$i])) {
				unset($tags[i]);
			}
		}
		array_unique($tags);
		return $tags;
	}
}