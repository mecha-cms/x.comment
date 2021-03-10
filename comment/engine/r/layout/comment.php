<article class="comment comment-status:<?= $comment->status; ?>" id="<?= sprintf($c['anchor'][2], $comment->id); ?>">
  <figure class="comment-figure">
    <img class="comment-avatar" alt="" src="<?= strtr($comment->avatar(80), ['&' => '&amp;']); ?>" width="80" height="80">
  </figure>
  <header class="comment-header">
    <?= x\comment\hook('comment-header', [[
        'author' => [
            0 => 'h4',
            1 => (string) new HTML([
                0 => ($has_link = $comment->link) ? 'a' : 'span',
                1 => $comment->author,
                2 => [
                    'class' => 'comment-link',
                    'href' => $has_link,
                    'rel' => $has_link ? 'nofollow' : null,
                    'target' => $has_link ? '_blank' : null
                ]
            ]),
            2 => ['class' => 'comment-author']
        ],
        'meta' => [
            0 => 'p',
            1 => '<time class="comment-time" datetime="' . $comment->time->ISO8601 . '">' . $comment->time->{strtr($state->language, '-', '_')} . ' ' . $comment->time('%I:%M %p') . '</time>&#x20;<a class="comment-url" href="#' . sprintf($c['anchor'][2], $comment->id) . '" rel="nofollow"></a>',
            2 => ['class' => 'comment-meta']
        ]
    ], $page, $deep], $comment); ?>
  </header>
  <div class="comment-body">
    <?= x\comment\hook('comment-body', [[
        'content' => [
            0 => 'div',
            1 => $comment->content,
            2 => ['class' => 'comment-content']
        ]
    ], $page, $deep], $comment); ?>
  </div>
  <?php if ((1 === $type || true === $type) && $parent && $parent->name === $comment->name): ?>
    <?= self::get(__DIR__ . DS . 'comments.form.php', $lot); ?>
  <?php endif; ?>
  <footer class="comment-footer">
    <?php $tasks = $type ? x\comment\tasks(\Hook::fire('comment-tasks', [[], $page, $deep], $comment), [$page, $deep], $comment) : []; ?>
    <?php $tasks = !empty($tasks) ? [
        0 => 'ul',
        1 => '<li>' . implode('</li><li>', $tasks) . '</li>',
        2 => ['class' => 'comment-tasks']
    ] : null; ?>
    <?= x\comment\hook('comment-footer', [[
        'tasks' => $tasks
    ], $page, $deep], $comment); ?>
  </footer>
  <?php if ($deep < ($c['page']['deep'] ?? 0) && $comment->comments->count()): ++$deep; ?>
    <section class="comments" data-level="<?= $deep; ?>" id="<?= sprintf($c['anchor'][3], $comment->id); ?>">
      <?php foreach ($comment->comments(9999) as $v): ?>
      <?= self::get(__FILE__, array_replace($lot, [
          'comment' => $v,
          'deep' => $deep
      ])); ?>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>
</article>
