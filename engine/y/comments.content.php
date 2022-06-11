<section class="comments" data-level="0" id="<?= $c['anchor'][1]; ?>">
  <?php if ($count > 0): ?>
    <?php foreach ($page->comments($chunk ?? $count, ($part ?? (int) ceil($count / ($chunk ?? $count))) - 1) as $comment): ?>
      <?= self::comment(array_replace($lot, [
          'comment' => $comment,
          'deep' => 0
      ])); ?>
    <?php endforeach; ?>
  <?php else: ?>
    <p role="status">
      <?= i('No %s yet.', ['comments']); ?>
    </p>
  <?php endif; ?>
</section>