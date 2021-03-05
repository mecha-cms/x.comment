<?php if ($count > 0): ?>
<section class="comments" data-level="0" id="<?= $c['anchor'][1]; ?>">
    <?php foreach ($page->comments($c['page']['chunk'] ?? 9999, ($url['i'] ?? (int) ceil(count($page->comments->lot) / $c['page']['chunk'] ?? 9999)) - 1) as $comment): ?>
    <?= self::get(__DIR__ . DS . 'comments.article.php', array_replace($lot, [
        'comment' => $comment,
        'deep' => 0
    ])); ?>
    <?php endforeach; ?>
  </section>
  <?= self::get(__DIR__ . DS . 'comments.pager.php', array_replace($lot, [
      'page' => $page
  ])); ?>
<?php else: ?>
  <p><?= i('No comments yet.'); ?></p>
<?php endif; ?>
