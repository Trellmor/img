<?php

error_reporting(E_ALL);

header('Content-Type: text/html; charset=UTF-8');

require_once('lib/functions.php');
require_once('lib/config.php');
require_once('lib/class.sqlite.php');

$db = new sqlite('lib/db.sqlite');

if (isset($_GET['tag'])) {
	
	$sql = "SELECT
 i.ROWID as id,
 i.location as name,
 i.original_name,
 t.text
FROM
 images i,
 tags t,
 imagetags it
WHERE
 t.tag = '" . $db->escape(urldecode($_GET['tag'])) . "' and
 it.tag = t.ROWID and
 i.ROWID = it.image;";
	
	$res = $db->query($sql);
	$images = '';
	$tag_text = '';
	while ($row = $db->fetch($res)) {
		$tag_text = $row['text'];
		$preview = dirname($row['name']) . '/preview/' . basename($row['name']);
		$images .= '<div class="previewimage"><a href="' . $row['name'] . '" class="lightbox" rel="lightbox.tag"><img src="' . $preview . '" alt="' . htmlentities($row['original_name']) . '" /></a><br />' . "\n";
		$images .= '<a href="index.php?i=' . urlnumber_encode($row['id']) . '">Show</a></div>' . "\n";
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
   "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" href="style.css" />
		<title>img.pew.cc - Image Hosting</title>
		<script type="text/javascript" src="lightbox/prototype.js"></script>
		<script type="text/javascript" src="lightbox/scriptaculous.js?load=effects,builder"></script>
		<script type="text/javascript" src="lightbox/lightbox.js"></script>
		<link rel="stylesheet" href="lightbox/lightbox.css" type="text/css" media="screen" />
	</head>
	<body>
		<h1><a href="http://img.pew.cc">img.pew.cc</a></h1>
		<div id="content">
			<h2><?php echo one_wordwrap(htmlentities($tag_text), 5, '<wbr />'); ?></h2>
			<?php echo $images; ?>
			<br style="clear: both;" />
		</div>
		<p id="copy">&copy; 2009 <a href="http://blog.pew.cc">Daniel Triendl</a></p>
	</body>
</html>
<?php
} else {

	$sql = "SELECT tag, text, count FROM tags ORDER BY count DESC";
	$sql .= (isset($_GET['tags']) && $_GET['tags'] == 'all') ? ';' : ' LIMIT 100;';
	
	$res = $db->query($sql);
	$tags = array();
	$texts = array();
	while ($row = $db->fetch($res)) {
		$tags[$row['tag']] = $row['count'];
		$texts[$row['tag']] = htmlentities($row['text']);
	}
	
	// $tags is the array
	       
	ksort($tags);
	       
	$max_size = 32; // max font size in pixels
	$min_size = 12; // min font size in pixels
	       
	// largest and smallest array values
	$max_qty = max(array_values($tags));
	$min_qty = min(array_values($tags));
	       
	// find the range of values
	//$spread = $max_qty - $min_qty;
	//if ($spread == 0) { // we don't want to divide by zero
	//	$spread = 1;
	//}
	       
	// set the font-size increment
	//$step = ($max_size - $min_size) / ($spread);
	       
	// loop through the tag array
	$cloud = '';
	foreach ($tags as $tag => $count) {
		// calculate font-size
		// find the $value in excess of $min_qty
		// multiply by the font-size increment ($size)
		// and add the $min_size set above
		//$size = round($min_size + (($count - $min_qty) * $step));
		
		// Logarythmic tag list
		$weight = (log($count)-log($min_qty)) / (log($max_qty) - log($min_qty));
		$size = $min_size + round(($max_size - $min_size) * $weight);
	    
		$cloud .= '<a href="browse.php?tag=' . urlencode($tag) . '" class="tags" style="font-size: ' . $size . 'px">' . $texts[$tag] . '</a> ';
	}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" 
   "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<meta http-equiv="content-type" content="application/xhtml+xml; charset=UTF-8" />
		<link rel="stylesheet" type="text/css" href="style.css" />
		<title>img.pew.cc - Image Hosting</title>
	</head>
	<body>
		<h1><a href="http://img.pew.cc">img.pew.cc</a></h1>
		<div id="content">
			<p><?php echo $cloud; ?></p>
			<br style="clear: both;" />
			<p id="browse"><a href="browse.php?tags=all">Show all tags</a></p>
		</div>
		<p id="copy">&copy; 2009 <a href="http://blog.pew.cc">Daniel Triendl</a></p>
	</body>
</html>
<?php

}

?>