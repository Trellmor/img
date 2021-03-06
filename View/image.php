<?php
use Application\Uri;
use View\HTML;

$view->load('header');
?>
<div class="row">
  <div class="col-sm-9 content-box">
    <h1 class="wordbreak"><?php HTML::out($image->getOriginalName()); ?></h1>
    <a href="<?php HTML::out(Uri::getBase() . $image->getLocation()); ?>" target="_blank">
      <img src="<?php HTML::out(Uri::getBase() .$image->getLocation()); ?>" class="img-responsive" alt="<?php HTML::out($image->getOriginalName()); ?>" />
    </a>
    <div class="imageinfo" class="row">
      <div class="col-sm-6">
        <table class="table">
          <tbody>
            <tr>
              <th scope="row"><?php HTML::out(_('Size')); ?></th>
              <td><?php HTML::out($image->getFormattedSize()); ?></td>
            </tr>
            <tr>
              <th scope="row"><?php HTML::out(_('Width')); ?></th>
              <td><?php HTML::out($image->getWidth() . ' px'); ?></td>
            </tr>
            <tr>
              <th scope="row"><?php HTML::out(_('Height')); ?></th>
              <td><?php HTML::out($image->getHeight() . ' px'); ?></td>
            </tr>
            <tr>
              <th scope="row"><?php HTML::out(_('Date')); ?></th>
              <td><?php HTML::out(date('r', $image->getTime())); ?></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
  <div class="col-sm-2 content-box sidebar">
<?php if (count($tags) > 0): ?>
    <div class="sidebar-module">
      <h4><?php HTML::out(_('Tags')); ?></h4>
<?php foreach ($tags as $tag): ?>
      <ol class="list-unstyled wordbreak">
        <li><a href="<?php HTML::out(Uri::to('tags/' . str_replace(' ', '_', $tag->getTag()))); ?>"><?php HTML::out($tag->getTag()); ?></a> (<?php HTML::out($tag->getCount()); ?>)
      </ol>
<?php endforeach; ?>
    </div>
<?php endif; ?>
<?php if ($image->getUploadId() != null): ?>
    <div class="sidebar-module">
      <h4>
        <a href="<?php HTML::out(Uri::to('album/' . $image->getUploadId())); ?>"><?php HTML::out(_('Album')); ?></a>
      </h4>
    </div>
<?php endif; ?>
    <div class="sidebar-module">
      <h4>
        <a href="<?php HTML::out(Uri::to('download/' . $image->getEncodedId()) . $image->getOriginalName()); ?>"><?php HTML::out(_('Download')); ?></a>
      </h4>
    </div>
<?php if ($deletelink != null): ?>
    <div class="sidebar-module">
      <h4>
        <a href="<?php HTML::out($deletelink); ?>"><?php HTML::out(_('Delete')); ?></a>
      </h4>
    </div>
<?php endif; ?>
  </div>
</div>
<?php
$view->load('footer');
