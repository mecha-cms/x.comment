<?php

extract($lot);
$advance = Extend::exist('user');
$author = $advance ? Is::user() : false;

?>
<form class="form-comment<?php echo $reply ? ' on-reply' : ""; ?>" id="<?php echo $c['anchor'][1]; ?>" action="<?php echo $url->clean . '/' . $c['path'] . $url->query('&amp;'); ?>" method="post">
  <?php static::message(); ?>
  <?php if ($author): ?>
    <h4><?php echo $language->comment_hint_as('<a href="' . $user->url . '" rel="nofollow">' . $user . '</a>', true); ?></h4>
    <input name="author" type="hidden" value="<?php echo $author; ?>">
  <?php else: ?>
    <p class="form-comment-input form-comment-input:author">
      <label for="form-comment-input:author"><?php echo $language->comment_author; ?></label>
      <span>
        <input class="input block" id="form-comment-input:author" name="author" placeholder="<?php echo $language->comment_hint_author; ?>" type="text" required>
      </span>
    </p>
    <p class="form-comment-input form-comment-input:email">
      <label for="form-comment-input:email"><?php echo $language->comment_email; ?></label>
      <span>
        <input class="input block" id="form-comment-input:email" name="email" placeholder="<?php echo $language->comment_hint_email; ?>" type="email" required>
      </span>
    </p>
    <p class="form-comment-input form-comment-input:link">
      <label for="form-comment-input:link"><?php echo $language->comment_link; ?></label>
      <span>
        <input class="input block" id="form-comment-input:link" name="link" placeholder="<?php echo $language->comment_hint_link; ?>" type="url">
      </span>
    </p>
  <?php endif; ?>
  <div class="form-comment-textarea form-comment-textarea:content p">
    <label for="form-comment-textarea:content"><?php echo $language->comment_content; ?></label>
    <div>
      <textarea class="textarea block" id="form-comment-textarea:content" name="content" placeholder="<?php echo To::text($reply ? $language->comment_hint_reply([$reply->author . ""], true) : $language->comment_hint_content); ?>" required></textarea>
    </div>
  </div>
  <p class="form-comment-button form-comment-button:x">
    <label for="form-comment-button:x"></label>
    <span>
      <button class="button button-submit" id="form-comment-button:x" type="submit"><?php echo $language->comment_publish; ?></button><?php if ($c['deep'] > 0): ?> <a class="button button-reset comment-a comment-a:reset comment-reply:x" href="<?php echo $url->clean . $url->query('&amp;', ['parent' => false]) . '#' . $c['anchor'][1]; ?>"><?php echo $language->comment_cancel; ?></a><?php endif; ?><?php if (!empty($c['enter']) && $advance): ?> <span class="comment-user button">
        <?php $u = Extend::state('user'); ?>
        <a href="<?php echo $url . '/' . ($u['_path'] ?? $u['path']) . $url->query('&amp;', ['kick' => trim($url->path, '/')]) . '#' . $c['anchor'][1]; ?>"><?php echo $author ?: $language->log_in; ?></a>
      </span><?php endif; ?>
    </span>
  </p>
  <input name="path" type="hidden" value="<?php echo $url->path; ?>">
  <input name="parent" type="hidden" value="<?php echo $reply ? $reply->slug : ""; ?>">
  <input name="token" type="hidden" value="<?php echo Guard::token('comment'); ?>">
</form>