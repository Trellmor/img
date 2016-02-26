<?php
use View\HTML;
use Application\Uri;

$view->load('header');

$js[] = Uri::getBase() . 'js/select2.min.js';
?>

<div class="row">
  <div id="searchform" class="col-sm-8 content-box">
    <h1><?php HTML::out(_('Search')); ?></h1>
    <form action="<?php HTML::out(Uri::to('search/')); ?>" method="post">
      <div class="form-group">
        <label for="inputtags"><?php HTML::out(_('Tags')); ?></label>
        <select id="inputtags" class="form-control" multiple="multiple" name="tags[]" data-ajax--url="<?php HTML::out(Uri::to('tagsuggest')) ?>"></select>
      </div>
      <button type="submit" class="btn btn-default"><?php HTML::out(_('Search')); ?></button>
    </form>
  </div>
</div>
<?php
$view->load('footer');
