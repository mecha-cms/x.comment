<article class="comment comment-status:<?= $comment->status; ?>" id="comment:<?= $comment->id; ?>">
  <?php if ($avatar = $comment->avatar(100)): ?>
    <figure class="comment-figure">
      <img class="comment-avatar" alt="" src="<?= strtr($avatar, ['&' => '&amp;']); ?>" width="100" height="100">
    </figure>
  <?php endif; ?>
  <header class="comment-header">
    <?= x\comment\hook('comment-header', [[
        'author' => [
            0 => 'h4',
            1 => (string) new HTML([
                0 => ($link = $comment->link) ? 'a' : 'span',
                1 => $comment->author,
                2 => [
                    'class' => 'comment-link',
                    'href' => $link,
                    'rel' => $link ? 'nofollow' : null,
                    'target' => $link ? '_blank' : null
                ]
            ]),
            2 => ['class' => 'comment-author']
        ],
        'meta' => [
            0 => 'p',
            1 => '<time class="comment-time" datetime="' . $comment->time->ISO8601 . '">' . $comment->time('%A, %B %d, %Y %I:%M %p') . '</time>&#x20;<a class="comment-url" href="#comment:' . $comment->id . '" rel="nofollow"></a>',
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
    <?= self::form('comment', $lot); ?>
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
  <?php if ($deep < ($c['page']['deep'] ?? 0) && ($count = $comment->comments->count() ?? 0)): ++$deep; ?>
    <section class="comments" data-level="<?= $deep; ?>" id="comments:<?= $comment->id; ?>">
      <?php foreach ($comment->comments($count) as $v): ?>
        <?= self::comment(array_replace($lot, [
            'comment' => $v,
            'deep' => $deep
        ])); ?>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>
</article>