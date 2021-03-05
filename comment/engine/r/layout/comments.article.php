<article class="comment comment-status:<?= $comment->status; ?>" id="<?= sprintf($c['anchor'][2], $comment->id); ?>">
  <figure class="comment-figure">
    <img class="comment-avatar" alt="" src="<?= strtr($comment->avatar(80), ['&' => '&amp;']); ?>" width="80" height="80">
  </figure>
  <header class="comment-header">
    <?php $header = (array) Hook::fire('comment:header', [[
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
    ]]); ?>
    <?= implode("", _\lot\x\comment\hooks($header, [], $comment)); ?>
  </header>
  <div class="comment-body">
    <?php $body = (array) Hook::fire('comment:body', [['content' => [
        0 => 'div',
        1 => $comment->content,
        2 => ['class' => 'comment-content']
    ]]]); ?>
    <?= implode("", _\lot\x\comment\hooks($body, [], $comment)); ?>
  </div>
  <?php if ((1 === $type || true === $type) && $parent && $parent->name === $comment->name): ?>
    <?= self::get(__DIR__ . DS . 'comments.form.php', $lot); ?>
  <?php endif; ?>
  <footer class="comment-footer">
    <?php $tasks = $type ? _\lot\x\comment\hooks(Hook::fire('comment:tasks', [[], $page, $deep], $comment), [$page], $comment) : []; ?>
    <?php $tasks = !empty($tasks) ? [
        0 => 'ul',
        1 => '<li>' . implode('</li><li>', $tasks) . '</li>',
        2 => ['class' => 'comment-tasks']
    ] : [
        0 => false,
        1 => "",
        2 => []
    ]; ?>
    <?php $footer = (array) Hook::fire('comment:footer', [['tasks' => $tasks]]); ?>
    <?= implode("", _\lot\x\comment\hooks($footer, [], $comment)); ?>
  </footer>
  <?php if ($deep < ($c['page']['deep'] ?? 0) && $comment->comments->count()): ++$deep; ?>
    <section class="comments" data-level="<?= $deep; ?>" id="<?= $c['anchor'][2]; ?>:<?= $comment->id; ?>">
      <?php foreach ($comment->comments(9999) as $v): ?>
      <?= self::get(__FILE__, array_replace($lot, [
          'comment' => $v,
          'deep' => $deep
      ])); ?>
      <?php endforeach; ?>
    </section>
  <?php endif; ?>
</article>
