<?php if ($site->is('$') || ($page->comments->x && !$page->comments->count)): ?>
<!-- Is in home page or (is comment disable and comment empty) -->
<?php else: $c = ['c' => Extend::state('comment')]; ?>
<section class="comments">
  <?php Shield::get('comments.header', $c); ?>
  <?php Shield::get('comments.body', $c); ?>
  <?php Shield::get('comments.footer', $c); ?>
</section>
<?php endif; ?>