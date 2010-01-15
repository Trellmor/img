<?php

error_reporting(E_ALL);

header('Content-Type: text/html; charset=UTF-8');

require_once('lib/functions.php');
require_once('lib/config.php');

$filetypes = '';
foreach ($mime as $f) {
	$filetypes .= $f . ', ';
}
$filetypes = substr($filetypes, 0, -2);
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
   "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" href="style.css" />
		<title>img.pew.cc - Image Hosting</title>
		<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3/jquery.min.js"></script>
		<script src="tag.js" type="text/javascript"></script>
		<script type="text/javascript">
$(function () {
	$('#inputtags').attr('autocomplete', 'off');
	$('#inputtags').tagSuggest({
		url: 'tags.php',
		delay: 250,
		separator: ', ',
		tagContainer: 'p',
	});
});
    </script>
	</head>
	<body>
		<h1><a href="http://img.pew.cc">img.pew.cc</a></h1>
		<form action="upload.php" method="post" enctype="multipart/form-data">
		<div id="content">
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $maxsize; ?>" />
			<span class="text">File:</span><input type="file" size="40" name="image" /><br /><br />
			<span class="text">Tags:</span><input id="inputtags" type="text" size="40" name="tags" />
			<span class="text">&nbsp;</span><input id="submit" type="submit" name="submit" value="Upload" />
			<p id="info">
				Maximum upload size: <?php echo byteConvert($maxsize) ?><br />
				Allowed file types: <?php echo $filetypes; ?><br />
				Use , (comma) to seperate tags 
			</p>
			<p id="browse"><a href="browse.php">Browse images</a></p>
		</div>
		</form>
		<?php echo copyright(2009); ?>
	</body>
</html>