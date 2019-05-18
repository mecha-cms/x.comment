<?php

$advance = Extend::exist('user');
$author = $advance ? Is::user() : false;

?>
<form class="form-comment<?php echo $reply ? ' on-reply' : ""; ?>" id="<?php echo $c['anchor'][1]; ?>" action="<?php echo $url->clean . '/.comment' . $url->query('&amp;'); ?>" method="post">
  <?php echo $message; ?>
  <?php if ($author): ?>
    <h4><?php echo $language->commentPlaceholderAs('<a href="' . $user->url . '" rel="nofollow">' . $user . '</a>', true); ?></h4>
    <input name="comment[author]" type="hidden" value="<?php echo $author; ?>">
  <?php else: ?>
    <p class="form-comment-input form-comment-input:author">
      <label for="form-comment-input:author"><?php echo $language->commentAuthor; ?></label>
      <span>
        <input class="input width" id="form-comment-input:author" name="comment[author]" placeholder="<?php echo $language->commentPlaceholderAuthor; ?>" type="text" required>
      </span>
    </p>
    <p class="form-comment-input form-comment-input:email">
      <label for="form-comment-input:email"><?php echo $language->commentMail; ?></label>
      <span>
        <input class="input width" id="form-comment-input:email" name="comment[email]" placeholder="<?php echo $language->commentPlaceholderMail; ?>" type="email" required>
      </span>
    </p>
    <p class="form-comment-input form-comment-input:link">
      <label for="form-comment-input:link"><?php echo $language->commentLink; ?></label>
      <span>
        <input class="input width" id="form-comment-input:link" name="comment[link]" placeholder="<?php echo $language->commentPlaceholderLink; ?>" type="url">
      </span>
    </p>
  <?php endif; ?>
  <div class="form-comment-textarea form-comment-textarea:content p">
    <label for="form-comment-textarea:content"><?php echo $language->commentContent; ?></label>
    <div>
      <textarea class="textarea width" id="form-comment-textarea:content" name="comment[content]" placeholder="<?php echo To::text($reply ? $language->commentPlaceholderReply([$reply->author . ""], true) : $language->commentPlaceholderContent); ?>" required></textarea>
    </div>
  </div>
  <p class="form-comment-button form-comment-button:x">
    <label for="form-comment-button:x"></label>
    <span>
      <button class="button button-submit" id="form-comment-button:x" type="submit"><?php echo $language->doPublish; ?></button><?php if ($c['deep'] > 0): ?> <a class="button button-reset comment-a comment-a:reset comment-reply:x" href="<?php echo $url->clean . $url->query('&amp;', ['parent' => false]) . '#' . $c['anchor'][1]; ?>"><?php echo $language->doCancel; ?></a><?php endif; ?><?php if (!empty($c['enter']) && $advance): ?> <span class="comment-user button">
        <?php $u = Extend::state('user'); ?>
        <a href="<?php echo $url . '/' . ($u['_path'] ?? $u['path']) . $url->query('&amp;', ['kick' => trim($url->path, '/')]) . '#' . $c['anchor'][1]; ?>"><?php echo $author ?: $language->doLogIn; ?></a>
      </span><?php endif; ?>
    </span>
  </p>
  <input name="comment:data[parent]" type="hidden" value="<?php echo $reply ? $reply->slug : ""; ?>">
  <input name="token" type="hidden" value="<?php echo token('comment'); ?>">
</form>