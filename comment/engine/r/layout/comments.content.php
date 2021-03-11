<section class="comments" data-level="0" id="<?= $c['anchor'][1]; ?>">
  <?php if ($count > 0): ?>
    <?php foreach ($page->comments($c['page']['chunk'] ?? $count, ($url['i'] ?? (int) ceil($count / ($c['page']['chunk'] ?? $count))) - 1) as $comment): ?>
    <?= self::get(__DIR__ . DS . 'comment.php', array_replace($lot, [
        'comment' => $comment,
        'deep' => 0
    ])); ?>
    <?php endforeach; ?>
  <?php else: ?>
    <p><?= i('No comments yet.'); ?></p>
  <?php endif; ?>
</section>
