<?php
use Application\Uri;
use View\HTML;
use Application\Registry;
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
	
	<title><?php HTML::out($page_title); ?></title>

    <!-- Bootstrap -->
    <link href="<?php echo Uri::getBase(); ?>css/bootstrap.min.css" rel="stylesheet">
    
    <link href="<?php echo Uri::getBase(); ?>css/blueimp-gallery.min.css" rel="stylesheet">
    
    <link href="<?php echo Uri::getBase(); ?>css/select2.min.css" rel="stylesheet">
    <link href="<?php echo Uri::getBase(); ?>css/select2-bootstrap.min.css" rel="stylesheet">
    
    <link href="<?php echo Uri::getBase(); ?>css/site.min.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
    <meta name="google-signin-client_id" content="<?php HTML::out(Registry::getInstance()->config['google-signin']['client_id']); ?>">
  </head>
  <body>
<?php $view->load('navbar'); ?>
  
    <div class="container">