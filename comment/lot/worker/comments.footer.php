<?php if (!$page->comments->x): ?>
<footer class="comments-footer">
  <?php Shield::get('comments.form', ['c' => $lot['c']]); ?>
</footer>
<?php endif; ?>