<?php

$advance = State::get('x.user', true);
$author = $advance ? Is::user() : false;

?>
<form class="form-comment<?php echo $reply ? ' is-reply' : ""; ?>" id="<?php echo $c['anchor'][1]; ?>" action="<?php echo $url . '/.comment' . ($url->path ?? State::get('path')) . $url->query('&amp;'); ?>" method="post">
  <?php echo $alert; ?>
  <?php if ($author): ?>
    <h4><?php echo $language->commentAltAs('<a href="' . $user->url . '" rel="nofollow">' . $user . '</a>', true); ?></h4>
    <input name="author" type="hidden" value="<?php echo $author; ?>">
  <?php else: ?>
    <p class="form-comment-input form-comment-input:author p">
      <label for="form-comment-input:author"><?php echo $language->commentAuthor; ?></label>
      <span>
        <input class="input width" id="form-comment-input:author" name="author" placeholder="<?php echo $language->commentAltAuthor; ?>" type="text" required>
      </span>
    </p>
    <p class="form-comment-input form-comment-input:email p">
      <label for="form-comment-input:email"><?php echo $language->commentEmail; ?></label>
      <span>
        <input class="input width" id="form-comment-input:email" name="email" placeholder="<?php echo $language->commentAltEmail; ?>" type="email" required>
      </span>
    </p>
    <p class="form-comment-input form-comment-input:link p">
      <label for="form-comment-input:link"><?php echo $language->commentLink; ?></label>
      <span>
        <input class="input width" id="form-comment-input:link" name="link" placeholder="<?php echo $language->commentAltLink; ?>" type="url">
      </span>
    </p>
  <?php endif; ?>
  <div class="form-comment-textarea form-comment-textarea:content p">
    <label for="form-comment-textarea:content"><?php echo $language->commentContent; ?></label>
    <div>
      <textarea class="textarea width" id="form-comment-textarea:content" name="content" placeholder="<?php echo To::text($reply ? $language->commentAltReply([(string) $reply->author], true) : $language->commentAltContent); ?>" required></textarea>
    </div>
  </div>
  <p class="form-comment-button form-comment-button:x p">
    <label for="form-comment-button:x"></label>
    <span>
      <button class="button button-submit" id="form-comment-button:x" type="submit"><?php echo $language->doPublish; ?></button><?php if ($c['deep'] > 0): ?> <a class="button button-let comment-a comment-a:let comment-reply:x" href="<?php echo $url->clean . $url->query('&amp;', ['parent' => false]) . '#' . $c['anchor'][1]; ?>"><?php echo $language->doCancel; ?></a><?php endif; ?><?php if ($advance && !empty($c['user'])): ?> <span class="comment-user button">
        <a href="<?php echo $url . ($advance['_path'] ?? $advance['path']) . $url->query('&amp;', ['kick' => trim($url->path, '/')]) . '#' . $c['anchor'][1]; ?>"><?php echo $author ?: $language->doLogIn; ?></a>
      </span><?php endif; ?>
    </span>
  </p>
  <input name="parent" type="hidden" value="<?php echo $reply ? $reply->name : ""; ?>">
  <input name="token" type="hidden" value="<?php echo Guard::token('comment'); ?>">
</form>