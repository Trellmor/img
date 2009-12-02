<?php

error_reporting(E_ALL);

require_once('lib/functions.php');
require_once('lib/config.php');
require_once('lib/class.sqlite.php');

if (isset($_GET['i'])) {
	$db = new sqlite('lib/db.sqlite');
	$id = urlnumber_decode($_GET['i']);
	
	$row = $db->fetch($db->query("SELECT ROWID as id, location FROM images WHERE ROWID = '" . $id . "';"));
	if (!$row) {
		errorMsg('Image not found.');
	}
	
	$id = $row['id'];
	$name = $row['location'];
	$preview = dirname($name) . '/preview/' . basename($name);
	
	$res = $db->query("SELECT t.text FROM tags t, imagetags i WHERE t.ROWID = i.tag and i.image = '" . $id . "';");
	$tags = '';
	while ($row = $db->fetch($res)) {
		$tags .= $row['text'] . ', ';
	}
	$tags = substr($tags, 0, -2);
	
?>
<html>
	<head>
		<title>img.pew.cc - <?php echo basename($name) ?></title>
		<link rel="stylesheet" type="text/css" href="style.css" />
	</head>
	<body>
		<h1><a href="http://img.pew.cc">img.pew.cc</a></h1>
		<div id="content">
			<a id="preview" href="<?php echo $name; ?>"><img src="<?php echo $preview ?>" alt="" /></a>
			<p id="tags">Tags: <?php echo $tags ?><br /></p>
			<table>
				<thead>
					<tr>
						<th class="tabletext">&nbsp;</th>
						<th class="tablecode">Plain</th>
						<th class="tablecode">HTML</th>
						<th class="tablecode">BB code</th>
					</tr>
				</thead>
				<tbody><!--
					<tr>
						<td>Previewlink</td>
						<td><input type="text" size="15" readonly="readonly" value="<?php echo url() . '?i=' . $id; ?>" /></td>
						<td><input type="text" size="15" readonly="readonly" value="&lt;a href=&quot;<?php echo url() . '?i=' . $id; ?>&quot;&gt;<?php echo basename($name) ?> - img.pew.cc&lt;/a&gt;" /></td>
						<td><input type="text" size="15" readonly="readonly" value="[URL=<?php echo url() . '?i=' . $id; ?>]<?php echo basename($name) ?> - img.pew.cc[/URL]" /></td>
					</tr>-->
					<tr>
						<td>Preview</td>
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="<?php echo url() . $name; ?>" /></td>
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="&lt;a href=&quot;<?php echo url() . $name; ?>&quot;&gt;&lt;img src=&quot;<?php echo url() . $preview ?>&quot alt=&quot;<?php echo basename($name); ?> - img.pew.cc&quot; /&gt;&lt;/a&gt;" /></td>
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="[URL=<?php echo url() . $name; ?>][IMG]<?php echo url() . $preview; ?>[/IMG][/URL]" /></td>
					</tr>
					<tr>
						<td>Full</td>
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="<?php echo url() . $name ?>" /></td>
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="&lt;img src=&quot;<?php echo url() . $name; ?>&quot; alt=&quot;<?php echo basename($name) ?> - img.pew.cc&quot; /&gt;" /></td>
						<td><input onclick="this.select()" type="text" size="15" readonly="readonly" value="[IMG]<?php echo url() . $name ?>[/IMG]" /></td>
					</tr>
				</tbody>
			</table>
		</div>
	</body>
</html>
<?php

} else {

	$filetypes = '';
	foreach ($mime as $f) {
		$filetypes .= $f . ', ';
	}
	$filetypes = substr($filetypes, 0, -2);
?>
<html>
	<head>
		<link rel="stylesheet" type="text/css" href="style.css" />
		<title>img.pew.cc Image Hosting</title>
	</head>
	<body>
		<h1><a href="http://img.pew.cc">img.pew.cc</a></h1>
		<div id="content">
		<form action="upload.php" method="post" enctype="multipart/form-data">
			<input type="hidden" name="MAX_FILE_SIZE" value="<?php echo $maxsize; ?>" />
			<span class="text">File:</span><input type="file" size="40" name="image" /><br /><br />
			<span class="text">Tags:</span><input type="text" size="40" name="tags" /><br /><br />
			<span class="text">&nbsp;</span><input id="submit" type="Submit" name="submit" value="Upload" />
		</form>
		<p class="info">
			Maximum upload size: <?php echo byteConvert($maxsize) ?><br />
			Allowed file types: <?php echo $filetypes; ?>
		</p>
		</div>
		<p id="copy">&copy; 2009 <a href="http://blog.pew.cc">Daniel Triendl</a></p>
	</body>
</html>

<?php

}

?>