<?php
use Application\Uri;
use View\HTML;
?>
    <nav id="navbar" class="navbar navbar-inverse navbar-static-top" data-reload="<?php HTML::out(Uri::to('partial/navbar/')); ?>">
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
        <div class="navbar-collapse collapse">
          <ul class="nav navbar-nav">
            <li><a href="<?php HTML::out(Uri::to('/')); ?>"><?php HTML::out(_('Upload')); ?></a></li>
            <li><a href="<?php HTML::out(Uri::to('search/')); ?>"><?php HTML::out(_('Search')); ?></a></li>
            <li><a href="<?php HTML::out(Uri::to('tags/')); ?>"><?php HTML::out(_('Tags')); ?></a></li>
<?php if ($user != null): ?>
            <li><a href="<?php HTML::out(Uri::to('user/' . $user->getUser())); ?>"><?php HTML::out(_('My Images')); ?></a></li>
<?php endif; ?>            
          </ul>
          <div class="navbar-form navbar-right">
<?php if ($user == null): ?>
            <div id="g-signin-button" data-loginpage="<?php HTML::out(Uri::to('login/')); ?>"></div>
<?php else: ?>
            <button id="g-signout-button" class="btn btn-default" data-logoutpage="<?php HTML::out(Uri::to('logout/')); ?>"><?php HTML::out(_('Sign out')); ?></button>
<?php endif; ?>
          </div>
        </div><!--/.navbar-collapse -->
      </div>
    </nav> 