<?php
$view->load('header');
?>
<div class="alert <?php echo $message->getCSSLevel(); ?>"><?php echo nl2br($message->getMessage()); ?></div>
<?php
$view->load('footer');
?>