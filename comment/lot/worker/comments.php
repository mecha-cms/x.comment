<?php if ($config->is('$') || ($page->comments->x && !$page->comments->count)): ?>
<!-- Is in home page or (is comment disable and comment empty) -->
<?php else: $c = ['c' => Extend::state('comment')]; ?>
<section class="comments"<?php echo !empty($c['c']['anchor'][2]) ? ' id="' . $c['c']['anchor'][2] . '"' : ""; ?>>
  <?php self::get('comments.header', $c); ?>
  <?php self::get('comments.body', $c); ?>
  <?php self::get('comments.footer', $c); ?>
</section>
<?php endif; ?>