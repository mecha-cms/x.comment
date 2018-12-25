<?php if (!$page->comments->x): ?>
<footer class="comments-footer">
  <?php static::get('comments.form', ['c' => $lot['c']]); ?>
</footer>
<?php endif; ?>