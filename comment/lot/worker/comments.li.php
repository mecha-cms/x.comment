<?php extract($lot); ?>
<li class="comment comment-status:<?php echo $comment->status; ?>" id="<?php echo candy($c['anchor'][0], ['id' => $comment->id]); ?>">
  <figure class="comment-figure">
    <img class="comment-avatar" alt="" src="<?php echo $comment->avatar(70); ?>" width="70" height="70">
  </figure>
  <header class="comment-header">
    <p class="comment-property">
      <time class="comment-time" datetime="<?php echo $comment->time->W3C; ?>"><?php echo $comment->time->{strtr($site->language, '-', '_')} . ' ' . $comment->time->pattern('%h%:%m% %N%'); ?></time>&#x20;
      <a class="comment-url" href="<?php echo $comment->url; ?>" rel="nofollow"></a>
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
  <footer class="comment-footer">
    <?php

    $pid = $comment->time->format('Y-m-d-H-i-s');
    $tools = fn\comment\tools(Hook::fire('page.a.comment', [$level < $c['level'] ? [
        'reply' => [$language->do_reply, HTTP::query([
            'parent' => $pid
        ]) . '#' . $c['anchor'][1], false, [
            'class[]' => ['comment-a', 'comment-a:set', 'comment-reply:v'],
            'id' => 'parent:' . $pid,
            'rel' => 'nofollow',
            'title' => $language->comment_hint_reply(To::text($comment->author . ""), true)
        ]],
    ] : [], $page], $comment), [$page], $comment);

    ?>
    <?php if (!empty($tools)): ?>
    <ul class="comment-links">
      <li><?php echo implode('</li><li>', $tools); ?></li>
    </ul>
    <?php endif; ?>
  </footer>
  <?php if ($level < $c['level'] && $comment->replys->count): ++$level; ?>
  <ul class="comments" data-level="<?php echo $level; ?>">
    <?php foreach ($comment->replys as $reply): ?>
    <?php Shield::get(__FILE__, [
        'c' => $c,
        'comment' => $reply,
        'level' => $level
    ]); ?>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</li>