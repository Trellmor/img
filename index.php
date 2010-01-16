<?php

error_reporting(E_ALL);

require_once('lib/functions.php');
require_once('lib/config.php');

$filetypes = '';
foreach ($mime as $f) {
	$filetypes .= $f . ', ';
}
$filetypes = substr($filetypes, 0, -2);

$tagjs  = '<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script>';
$tagjs .= '<script src="tag.js" type="text/javascript"></script>';
$tagjs .= '<script type="text/javascript">';
$tagjs .= "$(function () {
	$('#inputtags').attr('autocomplete', 'off');
	$('#inputtags').tagSuggest({
		url: 'tags.php',
		delay: 250,
		separator: ', ',
		tagContainer: 'p',
	});
});
</script>";

$content = '<form action="upload.php" method="post" enctype="multipart/form-data">
			<input type="hidden" name="MAX_FILE_SIZE" value="' . $maxsize.'" />
			<span class="text">File:</span><input type="file" size="40" name="image" /><br /><br />
			<span class="text">Tags:</span><input id="inputtags" type="text" size="40" name="tags" />
			<span class="text">&nbsp;</span><input id="submit" type="submit" name="submit" value="Upload" />
			<p id="info">
				Maximum upload size: ' . byteConvert($maxsize) . '<br />
				Allowed file types: ' . $filetypes . '<br />
				Use , (comma) to seperate tags 
			</p>
			<p id="browse"><a href="browse.php">Browse</a> | <a href="search.php">Search</a></p>
			</form>';

outputHTML($content, array('header' => $tagjs);

?>