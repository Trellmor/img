<?php namespace Controllers;

use Application\CSRF;
use Application\Exceptions\ValidationException;
use Models\Tag;
use Application\Registry;
use Application\Uri;

class Image extends Controller {
	private $csrf;
	
	public function __construct() {
		parent::__construct();
		
		$this->csrf = new CSRF();
		$this->view->assignVar('csrf', $this->csrf);
	}
	
	public function image($id) {
		$image = \Models\Image::getImageByEncodedId($id);
		
		if ($image !== false) {
			$tags = Tag::getTagsForImage($image->getId());
			
			$this->view->assignVar('image', $image);
			$this->view->assignVar('tags', $tags);
			$this->view->assignVar('deletelink', ($this->canDelete($image)) ? 
					Uri::to('delete/' . $image->getEncodedId())->param($this->csrf->getName(), $this->csrf->getToken()) : null);
			$this->view->load('image');
		} else {
			$this->error(404, _('Image not found.'));
			return;
		}
	}
	
	public function download($id) {
		$image = \Models\Image::getImageByEncodedId($id);
		
		if ($image !== false) {
			header('Content-Disposition: attachment');
			readfile($image->getPath());
			die();
		} else {
			$this->error(404, _('Image not found.'));
			return;
		}
	}
	
	private function canDelete($image) {
		if (!isset(Registry::getInstance()->user) || Registry::getInstance()->user == null) {
			return false;
		}
		
		if (Registry::getInstance()->user->isAdmin()) {
			return true;
		}
		
		if ($image->getUser() === null || $image->getUser() != Registry::getInstance()->user->getId()) {
			return false;
		}
		
		return true;
	}

	public function delete($id) {
		try {
			if (!$this->csrf->verifyToken('GET')) {
				throw new ValidationException(_('Access denied'), 403);
			}
			
			$image = \Models\Image::getImageByEncodedId($id);


			if ($image === false) {
				throw new ValidationException(_('Image not found.'), 404);
			}
			
			if (!$this->canDelete($image)) {
				throw new ValidationException(_('Access denied'), 403);
			}

			$image->delete();
			
			$this->success(_('Image deleted'));
		} catch (ValidationException $e) {
			$this->error(($e->getCode() == 0) ? 200 : $e->getCode(), $e->getMessage());
		} catch (\Exception $e) {
			$this->error(200, $e->getMessage());
		}
	}
}