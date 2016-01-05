<?php 
use Application\Uri;
use View\HTML;

$view->load('header');

$js[] = Uri::getBase() . 'view/js/jquery.blueimp-gallery.min.js';
?>
<div class="row">
  <div class="col-sm-9">
    <div class="row">
<?php foreach ($images as $image): ?>
      <div class="col-md-3 col-sm-6">
        <div class="thumbnail">
          <a href="<?php HTML::out(Uri::getBase() . $image->getLocation()); ?>" data-gallery="#blueimp-gallery" 
              title="<?php HTML::out($image->getOriginalName()); ?>">
            <img src="<?php HTML::out(Uri::getBase() . $image->getPreview()); ?>" alt="<?php HTML::out($image->getOriginalName()); ?>" />
          </a>
          <div class="caption text-center small">
            <a href="<?php HTML::out(Uri::to('image/' . $image->getEncodedId())); ?>"><?php HTML::out(_('Details')); ?></a>
          </div>
        </div>
      </div>
<?php endforeach; ?>
    </div>
<?php if ($page > 0): ?>
    <ul class="pager">
<?php if (count($images) == $pagelimit): ?>
      <li class="next"><a href="<?php HTML::out($nextpage); ?>"><?php HTML::out(_('Next')); ?> &rarr;</a></li>
<?php endif;?>

<?php if ($page > 1): ?>
      <li class="previous"><a href="<?php HTML::out($prevpage); ?>">&larr; <?php HTML::out(_('Previous')); ?></a></li>
<?php endif;?>  
    </ul>
<?php endif; ?>
  </div>
  <div class="col-sm-3 sidebar">
    <h4><?php HTML::out(_('Tags')); ?></h4>
<?php foreach ($tags as $tag): ?>
    <ol class="list-unstyled">
      <li><a href="<?php HTML::out(Uri::to('tags/' . str_replace(' ', '_', $tag->getTag()))); ?>"><?php HTML::out($tag->getTag()); ?></a> (<?php HTML::out($tag->getCount()); ?>)
    </ol>
<?php endforeach; ?>
  </div>
</div>
<!-- The Gallery as lightbox dialog, should be a child element of the document body -->
<div id="blueimp-gallery" class="blueimp-gallery blueimp-gallery-controls">
  <div class="slides"></div>
  <h3 class="title"></h3>
  <a class="prev"><span class="glyphicon glyphicon-chevron-left" aria-hidden="true"></span></a>
  <a class="next"><span class="glyphicon glyphicon-chevron-right" aria-hidden="true"></span></a>
  <a class="close"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></a>
  <a class="play-pause">
    <span class="glyphicon glyphicon-play play" aria-hidden="true"></span>
    <span class="glyphicon glyphicon-pause pause" aria-hidden="true"></span>
  </a>
  <ol class="indicator"></ol>
</div>
<?php 
$view->load('footer');
?>