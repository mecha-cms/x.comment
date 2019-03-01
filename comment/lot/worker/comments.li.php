<?php extract($lot); ?>
<li class="comment comment-status:<?php echo $comment->status; ?>" id="<?php echo candy($c['anchor'][0], ['id' => $comment->id]); ?>">
  <figure class="comment-figure">
    <img class="comment-avatar" alt="" src="<?php echo $comment->avatar(70); ?>" width="70" height="70">
  </figure>
  <header class="comment-header">
    <p class="comment-property">
      <time class="comment-time" datetime="<?php echo $comment->time->ISO8601; ?>"><?php echo $comment->time->{strtr($config->language, '-', '_')} . ' ' . $comment->time('%h%:%m% %N%'); ?></time>&#x20;
      <a class="comment-url" href="<?php echo implode($url->query('&amp;') . '#', explode('#', $comment->url, 2)); ?>" rel="nofollow"></a>
    </p>
    <h4 class="comment-author">
      <?php if ($comment->link): ?>
      <a class="comment-link" href="<?php echo $comment->link; ?>" rel="nofollow" target="_blank"><?php echo $comment->author; ?></a>
      <?php else: ?>
      <span class="comment-link"><?php echo $comment->author; ?></span>
      <?php endif; ?>
    </h4>
  </header>
  <div class="comment-body"><?php echo $comment->content; ?></div>
  <?php if ($type && $reply && $reply->slug === $comment->slug): ?>
  <?php static::get('comments.form', $lot); ?>
  <?php endif; ?>
  <footer class="comment-footer">
    <?php

    $id = $comment->slug;
    $tools = $type ? fn\comment\tools(Hook::fire('page.a.comment', [$deep < $c['deep'] ? [
        'reply' => [
            0 => 'a',
            1 => $language->do_reply,
            2 => [
                'class' => 'comment-a comment-a:set comment-reply:v',
                'href' => HTTP::query(['parent' => $id]) . '#' . $c['anchor'][1],
                'id' => 'parent:' . $id,
                'rel' => 'nofollow',
                'title' => To::text($language->comment_hint_reply([$comment->author . ""], true))
            ]
        ],
    ] : [], $page], $comment), [$page], $comment) : [];

    ?>
    <?php if (!empty($tools)): ?>
    <ul class="comment-links">
      <li><?php echo implode('</li><li>', $tools); ?></li>
    </ul>
    <?php endif; ?>
  </footer>
  <?php if ($deep < $c['deep'] && $comment->comments->count): ++$deep; ?>
  <ul class="comments" data-level="<?php echo $deep; ?>">
    <?php foreach ($comment->comments(9999) as $v): ?>
    <?php static::get(__FILE__, extend($lot, [
        'comment' => $v,
        'deep' => $deep
    ], false)); ?>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</li>