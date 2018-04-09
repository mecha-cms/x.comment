<?php extract($lot); ?>
<li class="comment comment-status:<?php echo $comment->status; ?>" id="<?php echo __replace__($_state['anchor'][0], ['id' => $comment->id]); ?>">
  <figure class="comment-figure">
    <img class="comment-avatar" alt="" src="<?php echo $comment->avatar($url->protocol . 'www.gravatar.com/avatar/' . md5($comment->email) . '?s=70&amp;d=monsterid'); ?>" width="70" height="70">
  </figure>
  <header class="comment-header">
    <p class="comment-property">
      <time class="comment-time" datetime="<?php echo $comment->date->W3C; ?>"><?php echo $comment->date->{str_replace('-', '_', $site->language)} . ' ' . $comment->date->F4; ?></time>&#x20;
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
    <?php echo implode('<span class="comment-s"></span>', Hook::fire('page.a.comment', [
        $level < $_state['level'] ? [
            'reply' => HTML::a($language->comment_reply, HTTP::query(['parent' => $comment->date('U')]) . '#' . $_state['anchor'][1], false, [
                'class[]' => ['comment-a', 'comment-a:set', 'comment-reply:v'],
                'id' => 'parent:' . $comment->date('U'),
                'rel' => 'nofollow',
                'title' => $language->comment_f_reply(To::text($comment->author . ""), true)
            ])
        ] : [], $comment, $page])); ?>
  </footer>
  <?php if ($level < $_state['level'] && $comment->comments): ++$level; ?>
  <ul class="comments" data-level="<?php echo $level; ?>">
    <?php foreach ($comment->comments as $k => $reply): ?>
    <?php if (!is_numeric($k)) continue; ?>
    <?php Shield::get(__FILE__, [
        'comment' => $reply,
        'level' => $level
    ]); ?>
    <?php endforeach; ?>
  </ul>
  <?php endif; ?>
</li>