<?php
use Application\Uri;
use View\HTML;

$view->load('header');

$js[] = Uri::getBase() . 'js/plupload.full.min.js';
$js[] = Uri::getBase() . 'js/select2.min.js';
?>

<div class="row">
  <div class="col-sm-8 content-box">
    <div id="uploadform" data-uploadid="<?php HTML::out($uploadid) ?>" data-csrf="<?php HTML::out($csrf->getToken()); ?>">
      <h1><?php HTML::out(_('Upload')); ?></h1>
  
      <div class="form-group">
        <label ><?php HTML::out(_('Images')); ?></label>
        <div id="image-list"></div>
      </div>

      <div class="form-group">
        <button id="pickfiles" class="btn btn-default"><span class="glyphicon glyphicon-plus-sign" aria-hidden="true"></span> <?php HTML::out(_('Add images')) ?></button>
      </div>

      <div class="form-group">
        <label for="inputtags"><?php HTML::out(_('Tags')); ?></label>
        <select id="inputtags" class="form-control" multiple="multiple" name="inputtags" data-ajax--url="<?php HTML::out(Uri::to('tagsuggest')) ?>"></select>
      </div>

      <div class="form-group">
        <button id="upoad" class="btn btn-default"><span class="glyphicon glyphicon-upload" aria-hidden="true"></span> <?php HTML::out(_('Upload')) ?></button>
      </div>
    </div>

    <p><?php HTML::out('Upload files by using the "Add images" button, dragging images onto this window or pasting them with CTRL+V.'); ?></p>
  </div>
</div>

<div id="dropbox"><h1><?php HTML::out('Drop images here'); ?></h1></div>

<?php
$view->load('footer');
?>