<?php

$advance = State::get('x.user', true);
$author = $advance ? Is::user() : false;

?>
<form action="<?= strtr($page->url, [$url . '/' => $url . '/.comment/']); ?>" class="comment-form<?= $reply ? ' is:reply' : ""; ?>" id="<?= $c['anchor'][1]; ?>" method="post" name="comment">
  <?= $alert; ?>
  <?php if ($author): ?>
  <h3><?= i('Commenting as %s', '<a href="' . $user->url . '" rel="nofollow">' . $user . '</a>'); ?></h3>
  <input name="comment[author]" type="hidden" value="<?= $author; ?>">
  <?php else: ?>
  <p>
    <label for="<?= $for = sprintf($c['anchor'][0], 'author'); ?>"><?= i('Name'); ?></label>
    <br>
    <span>
      <input class="input width" id="<?= $for; ?>" name="comment[author]" placeholder="<?= i('Anonymous'); ?>" required type="text">
    </span>
  </p>
  <p>
    <label for="<?= $for = sprintf($c['anchor'][0], 'email'); ?>"><?= i('Email'); ?></label>
    <br>
    <span>
      <input class="input width" id="<?= $for; ?>" name="comment[email]" placeholder="<?= S . i('hello') . S . '@' . S . $url->host . S; ?>" required type="email">
    </span>
  </p>
  <p>
    <label for="<?= $for = sprintf($c['anchor'][0], 'link'); ?>"><?= i('Link'); ?></label>
    <br>
    <span>
      <input class="input width" id="<?= $for; ?>" name="comment[link]" placeholder="<?= S . $url->protocol . S . $url->host . S; ?>" type="url">
    </span>
  </p>
  <?php endif; ?>
  <div class="p">
    <label for="<?= $for = sprintf($c['anchor'][0], 'content'); ?>"><?= i('Message'); ?></label>
    <br>
    <div>
      <textarea class="textarea width" id="<?= $for; ?>" name="comment[content]" placeholder="<?= To::text($reply ? i('Reply to %s', (string) $reply->author) : i('Message goes here...')); ?>" required></textarea>
    </div>
  </div>
  <p>
    <label><?= i('Actions'); ?></label>
    <br>
    <span>
      <button class="button" id="<?= $for = sprintf($c['anchor'][0], 'x'); ?>" type="submit"><?= i('Publish'); ?></button><?php if (isset($c['page']['deep']) && $c['page']['deep'] > 0): ?> <a class="button js:cancel" href="<?= $url->clean . $url->query('&amp;', ['parent' => false]) . '#' . $c['anchor'][1]; ?>" target="<?= $c['anchor'][1]; ?>"><?= i('Cancel'); ?></a><?php endif; ?><?php if ($advance && !empty($c['user'])): ?> <span class="button js:user">
        <a href="<?= $url . ($advance['guard']['path'] ?? $advance['path']) . $url->query('&amp;', ['kick' => trim($url->path, '/') . $url->query . '#' . $c['anchor'][1]]); ?>"><?= $author ?: i('Log In'); ?></a>
      </span><?php endif; ?>
    </span>
  </p>
  <input name="comment[parent]" type="hidden" value="<?= $reply ? $reply->name : ""; ?>">
  <input name="comment[token]" type="hidden" value="<?= Guard::token('comment'); ?>">
</form>
