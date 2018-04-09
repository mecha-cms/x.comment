<?php if ($site->is('$') || ($page->comments['x'] && !$page->comments['count'])): ?>
<!-- is in home page or (is comment disable and comment empty) -->
<?php else: Lot::set('_state', Extend::state('comment')); ?>
<section class="comments">
  <?php Shield::get(__DIR__ . DS . 'header.php'); ?>
  <?php Shield::get(__DIR__ . DS . 'body.php'); ?>
  <?php Shield::get(__DIR__ . DS . 'footer.php'); ?>
</section>
<?php endif; ?>