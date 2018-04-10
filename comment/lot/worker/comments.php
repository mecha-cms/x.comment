<?php if ($site->is('$') || ($page->comments['x'] && !$page->comments['count'])): ?>
<!-- is in home page or (is comment disable and comment empty) -->
<?php else: Lot::set('_state', Extend::state('comment')); ?>
<section class="comments">
  <?php Shield::get('comments.header'); ?>
  <?php Shield::get('comments.body'); ?>
  <?php Shield::get('comments.footer'); ?>
</section>
<?php endif; ?>