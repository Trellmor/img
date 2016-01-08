<?php
use Application\Uri;
use View\HTML;
?>
    </div>
    <footer class="footer navbar-inverse">
      <div class="container">
        <div class="row">
          <div class="col-xs-8 text-left small">
            &copy; 2009 - <?php HTML::out(date('Y')); ?> by <a class="navbar-link" href="http://pew.cc">Daniel Triendl</a>
          </div>
          <div class="col-xs-4 text-right small">
            <a class="navbar-link" href="https://github.com/Trellmor/img/issues/new"><?php HTML::out(_('Report a bug')); ?></a>
          </div>        
        </div>
      </div>
    </footer>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
<?php foreach ($js as $src): ?>
    <script src="<?php echo $src ?>"></script>
<?php endforeach; ?>
    <script src="https://apis.google.com/js/platform.js?onload=onGooglePlatformLoaded" async defer></script>
  </body>
</html>