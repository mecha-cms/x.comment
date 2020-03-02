<li class="comment comment-status:<?= $comment->status; ?>" id="<?= sprintf($c['anchor'][0], $comment->id); ?>">
  <figure class="comment-figure">
    <img class="comment-avatar" alt="" src="<?= strtr($comment->avatar(70), ['&' => '&amp;']); ?>" width="70" height="70">
  </figure>
  <header class="comment-header">
    <p class="comment-meta">
      <time class="comment-time" datetime="<?= $comment->time->ISO8601; ?>"><?= $comment->time->{strtr($state->language, '-', '_')} . ' ' . $comment->time('%I:%M %p'); ?></time>&#x20;
      <a class="comment-url" href="#<?= sprintf($c['anchor'][0], $comment->id); ?>" rel="nofollow"></a>
    </p>
    <h4 class="comment-author">
      <?php if ($comment->link): ?>
      <a class="comment-link" href="<?= $comment->link; ?>" rel="nofollow" target="_blank"><?= $comment->author; ?></a>
      <?php else: ?>
      <span class="comment-link"><?= $comment->author; ?></span>
      <?php endif; ?>
    </h4>
  </header>
  <div class="comment-body"><?= $comment->content; ?></div>
  <?php if ($type && $reply && $reply->name === $comment->name): ?>
  <?= self::get(__DIR__ . DS . 'comments.form.php', $lot); ?>
  <?php endif; ?>
  <footer class="comment-footer">
    <?php $links = $type ? _\lot\x\comment\a(Hook::fire('comment.a', [[], $page, $deep], $comment), [$page], $comment) : []; ?>
    <?php if (!empty($links)): ?>
    <ul class="comment-links">
      <li><?= implode('</li><li>', $links); ?></li>
    </ul>
    <?php endif; ?>
  </footer>
  <?php if ($deep < ($c['page']['deep'] ?? 0) && $comment->comments->count()): ++$deep; ?>
  <ul class="comments" data-level="<?= $deep; ?>">
    <?php foreach ($comment->comments(9999) as $v): ?>
    <?= self::get(__FILE__, array_replace($lot, [
        'comment' => $v,
        'deep' => $deep
    ])); ?>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</li>
