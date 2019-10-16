<?php

$advance = State::get('x.user', true);
$author = $advance ? Is::user() : false;

?>
<form class="form-comment<?= $reply ? ' is-reply' : ""; ?>" id="<?= $c['anchor'][1]; ?>" action="<?= $url . '/.comment' . ($url->path ?? State::get('path')) . $url->query('&amp;'); ?>" method="post">
  <?= $alert; ?>
  <?php if ($author): ?>
    <h4><?= i('Reply to %s', '<a href="' . $user->url . '" rel="nofollow">' . $user . '</a>'); ?></h4>
    <input name="author" type="hidden" value="<?= $author; ?>">
  <?php else: ?>
    <p class="form-comment-input form-comment-input:author p">
      <label for="form-comment-input:author"><?= i('Name'); ?></label>
      <span>
        <input class="input width" id="form-comment-input:author" name="author" placeholder="<?= i('Anonymous'); ?>" type="text" required>
      </span>
    </p>
    <p class="form-comment-input form-comment-input:email p">
      <label for="form-comment-input:email"><?= i('Email'); ?></label>
      <span>
        <input class="input width" id="form-comment-input:email" name="email" placeholder="<?= S . i('hello') . S . '@' . S $url->host . S; ?>" type="email" required>
      </span>
    </p>
    <p class="form-comment-input form-comment-input:link p">
      <label for="form-comment-input:link"><?= i('Link'); ?></label>
      <span>
        <input class="input width" id="form-comment-input:link" name="link" placeholder="<?= S . $url->protocol . S . $url->host . S; ?>" type="url">
      </span>
    </p>
  <?php endif; ?>
  <div class="form-comment-textarea form-comment-textarea:content p">
    <label for="form-comment-textarea:content"><?= i('Message'); ?></label>
    <div>
      <textarea class="textarea width" id="form-comment-textarea:content" name="content" placeholder="<?= To::text($reply ? i('Reply to %s', (string) $reply->author) : i('Message goes here...'); ?>" required></textarea>
    </div>
  </div>
  <p class="form-comment-button form-comment-button:x p">
    <label for="form-comment-button:x"></label>
    <span>
      <button class="button button-submit" id="form-comment-button:x" type="submit"><?= i('Publish'); ?></button><?php if ($c['deep'] > 0): ?> <a class="button button-let comment-a comment-a:let comment-reply:x" href="<?= $url->clean . $url->query('&amp;', ['parent' => false]) . '#' . $c['anchor'][1]; ?>"><?= i('Cancel'); ?></a><?php endif; ?><?php if ($advance && !empty($c['user'])): ?> <span class="comment-user button">
        <a href="<?= $url . ($advance['guard']['path'] ?? $advance['path']) . $url->query('&amp;', ['kick' => trim($url->path, '/')]) . '#' . $c['anchor'][1]; ?>"><?= $author ?: i('Log In'); ?></a>
      </span><?php endif; ?>
    </span>
  </p>
  <input name="parent" type="hidden" value="<?= $reply ? $reply->name : ""; ?>">
  <input name="token" type="hidden" value="<?= Guard::token('comment'); ?>">
</form>