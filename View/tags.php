<?php 
use View\HTML;
use Application\Uri;

$view->load('header');
?>
<div id="tagcloud" class="content-box">
<?php foreach ($tags as $tag): ?>
  <a href="<?php HTML::out(Uri::to('tags/' . $tag->getEncodedTag())); ?>" style="font-size: <?php HTML::out(100 + $tag->getScale($min, $div)); ?>%"><?php HTML::out($tag->getTag()); ?></a>
<?php endforeach; ?>
<?php if($count > 0 && count($tags) == $count): ?>
  <p class="text-right"><a href="<?php HTML::out(Uri::to('alltags')); ?>"><?php HTML::out(_('Show all tags')); ?></a>
<?php endif; ?>
</div>
<?php 
$view->load('footer');
?>