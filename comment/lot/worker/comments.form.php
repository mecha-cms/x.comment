<?php

extract($lot);
$advance = Extend::exist('user');
$author = $advance ? Is::user() : false;

?>
<form class="form-comment<?php echo $reply ? ' on-reply' : ""; ?>" id="<?php echo $c['anchor'][1]; ?>" action="<?php echo $url->clean . '/' . $c['path'] . $url->query('&amp;'); ?>" method="post">
  <?php static::message(); ?>
  <?php if ($author): ?>
    <h4><?php echo $language->comment_hint_as(HTML::a($user, $user->url, false, ['rel' => 'nofollow']), true); ?></h4>
    <?php echo Form::hidden('author', $author); ?>
  <?php else: ?>
    <?php foreach (['author', 'email', 'link'] as $f): ?>
      <?php $r = $f !== 'link' ? '*' : ""; ?>
      <p class="form-comment-input form-comment-input:<?php echo $f; ?>">
        <label for="form-comment-input:<?php echo $f; ?>"><?php echo $language->{'comment_' . $f}; ?></label>
        <span><?php echo Form::text($r . $f, null, $language->{'comment_hint_' . $f}, ['class[]' => ['input', 'block'], 'id' => 'form-comment-input:' . $f]); ?></span>
      </p>
    <?php endforeach; ?>
  <?php endif; ?>
  <div class="form-comment-textarea form-comment-textarea:content p">
    <label for="form-comment-textarea:content"><?php echo $language->comment_content; ?></label>
    <div><?php echo Form::textarea('*content', null, To::text($reply ? $language->comment_hint_reply([$reply->author . ""], true) : $language->comment_hint_content), ['class[]' => ['textarea', 'block'], 'id' => 'form-comment-textarea:content']); ?></div>
  </div>
  <p class="form-comment-button form-comment-button:state">
    <label for="form-comment-button:state"></label>
    <span>
      <?php echo Form::submit('state', null, $language->comment_publish, ['class[]' => ['button', 'button-submit'], 'id' => 'form-comment-button:state']) . ($c['deep'] > 0 ? ' ' . HTML::a($language->comment_cancel, $url->clean . HTTP::query(['parent' => false]) . '#' . $c['anchor'][1], false, ['class[]' => ['button', 'button-reset', 'comment-a', 'comment-a:reset', 'comment-reply:x']]) : ""); ?><?php if (!empty($c['enter']) && $advance): ?> <span class="comment-user button">
        <?php $u = Extend::state('user'); ?>
        <?php echo HTML::a($author ?: $language->log_in, ($u['_path'] ?? $u['path']) . HTTP::query([
            'kick' => $url->path
        ]) . '#' . $c['anchor'][1]); ?>
      </span><?php endif; ?>
    </span>
  </p>
  <?php echo Form::hidden('path', $url->path); ?>
  <?php echo Form::hidden('parent', $reply ? $reply->slug : null); ?>
  <?php echo Form::hidden('token', Guardian::token('comment')); ?>
</form>