<?php

require_once('lib/functions.php');
require_once('lib/config.php');

?>
<html>
	<head>
		<title>img.pew.cc Image Hosting</title>
		<style type="text/css">
body {
	text-align: center;
	font-family: Arial,Helvetica,Sans-serif;
}

#copy {
	font-size: small;
	text-align: right;
}

#content {
	margin-left: auto;
	margin-right: auto;
	width: 500px;
	text-align: left;
}

#content .text {
	display: block;
	width: 50px;
	float: left;
}
		</style>
	</head>
	<body>
		<h1>img.pew.cc</h1>
		<div id="content">
		<form action="upload.php" method="post" enctype="multipart/form-data">
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $maxsize; ?>" />
			<span class="text">File:</span><input type="file" size="40" name="image" /><br /><br />
			<span class="text">Tags:</span><input type="text" size="40" name="tags" /><br /><br />
			<input type="Submit" name="submit" value="Upload" />
		</form>
		<p>Maximum upload size: <?php echo byteConvert($maxsize) ?></p>
		</div>
		<p id="copy">&copy; 2009 <a href="http://blog.pew.cc">Daniel Triendl</a></p>
	</body>