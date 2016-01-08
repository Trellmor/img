<?php namespace Controllers;

use Application\Input;
use Models\Tag;
class TagSuggest extends JSONController {
	public function suggest() {
		$input = new Input('GET');
		$input->filter('term', FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW);
		$result = [];
		$tags = Tag::getMatchingTags($input->term);
		foreach ($tags as $tag) {
			$result[] = [
					'id' => $tag->getTag(),
					'text' => $tag->getTag()
			];
		}
		$this->returnJSON($result);
	}
}

?>