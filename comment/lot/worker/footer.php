  <?php if (!$page->comments['x']): ?>
  <footer class="comments-footer">
  <?php Shield::get(__DIR__ . DS . 'form.php'); ?>
  </footer>
  <?php endif; ?>