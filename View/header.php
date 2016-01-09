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
    <meta name="google-signin-client_id" content="<?php HTML::out(Registry::getInstance()->config['google-signin-client_id']); ?>">
  </head>
  <body>
    <nav class="navbar navbar-inverse navbar-static-top">
      <div class="container">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
            <span class="sr-only"><?php HTML::out(_('Toggle navigation')); ?></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="<?php HTML::out(Uri::to('/')); ?>"><?php HTML::out($page_title); ?></a>
        </div>
        <div id="navbar" class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li><a href="<?php HTML::out(Uri::to('/')); ?>"><?php HTML::out(_('Upload')); ?></a></li>
            <li><a href="<?php HTML::out(Uri::to('search/')); ?>"><?php HTML::out(_('Search')); ?></a></li>
          </ul>
          <div class="navbar-form navbar-right">
<?php if ($user == null): ?>
      <div id="g-signin-button" class="g-signin2" data-onsuccess="onGooglePlatformSignIn" data-loginpage="<?php HTML::out(Uri::to('login/')); ?>"></div>
<?php else: ?>
      <button id="g-signout-button" class="btn btn-default" data-logoutpage="<?php HTML::out(Uri::to('logout/')); ?>"><?php HTML::out(_('Sign out')); ?></button>
<?php endif; ?>          
          </div>
        </div><!--/.navbar-collapse -->
      </div>
    </nav>  
  
    <div class="container">