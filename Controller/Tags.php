<?php

namespace Controller;

use Model\Tag;

class Tags extends ImgController {

	public function tags() {
		$this->getTags(100);
	}

	public function allTags() {
		$this->getTags(0);
	}

	private function getTags($count) {
		$tags = Tag::getTopTags($count);

		$min = Tag::getMinCount($count);
		$max = Tag::getMaxCount($count);

		$div = log($max - ($min - 1)) / 100;

		$this->view->assignVar('tags', $tags);
		$this->view->assignVar('count', $count);
		$this->view->assignVar('min', $min);
		$this->view->assignVar('div', $div);
		$this->view->load('tags');
	}
}
