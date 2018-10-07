<?php $_id = HTTP::get('parent', null); ?>
<?php $_parent = $_id ? new Comment(COMMENT . DS . $url->path . DS . (new Date($_id))->slug . '.page') : null; ?>
<?php $_extend_user = Extend::exist('user'); ?>
<?php $_user = $_extend_user ? Is::user() : false; ?>
<form class="form-comment<?php echo $_parent ? ' on-reply' : ""; ?>" id="<?php echo $_state['anchor'][1]; ?>" action="<?php echo $url->clean . '/' . $_state['path'] . $url->query('&amp;'); ?>" method="post">
  <?php echo $message; ?>
  <?php if ($_parent): ?>
  <h4><?php echo $language->comment_f_reply(HTML::a($_parent->author, implode($url->query . '#', explode('#', $_parent->url, 2)), false, ['rel' => 'nofollow']), true); ?></h4>
  <?php elseif ($_user): ?>
  <h4><?php echo $language->comment_f_as(HTML::a($user, $user->url, false, ['rel' => 'nofollow']), true); ?></h4>
  <?php endif; ?>
  <?php if ($_user): ?>
    <?php echo Form::hidden('author', $_user); ?>
  <?php else: ?>
    <?php foreach (['author', 'email', 'link'] as $_field): ?>
      <?php $_r = $_field !== 'link' ? '*' : ""; ?>
      <p class="form-comment-input form-comment-input:<?php echo $_field; ?>">
        <label for="form-comment-input:<?php echo $_field; ?>"><?php echo $language->{'comment_' . $_field}; ?></label>
        <span><?php echo Form::text($_r . $_field, null, $language->{'comment_f_' . $_field}, ['class[]' => ['input', 'block'], 'id' => 'form-comment-input:' . $_field]); ?></span>
      </p>
    <?php endforeach; ?>
  <?php endif; ?>
  <div class="form-comment-textarea form-comment-textarea:content p">
    <label for="form-comment-textarea:content"><?php echo $language->comment_content; ?></label>
    <div><?php echo Form::textarea('*content', null, $language->comment_f_content, ['class[]' => ['textarea', 'block'], 'id' => 'form-comment-textarea:content']); ?></div>
  </div>
  <p class="form-comment-button form-comment-button:state">
    <label for="form-comment-button:state"></label>
    <span>
      <?php echo Form::submit('state', null, $language->comment_publish, ['class[]' => ['button', 'button-submit'], 'id' => 'form-comment-button:state']) . ($_state['level'] > 1 ? ' ' . HTML::a($language->comment_cancel, $url->clean . '#' . $_state['anchor'][1], false, ['class[]' => ['button', 'button-reset', 'comment-a', 'comment-a:reset', 'comment-reply:x']]) : ""); ?><?php if (!empty($_state['enter']) && $_extend_user): ?> <span class="comment-user button">
        <?php $_c = Extend::state('user'); ?>
        <?php echo HTML::a($_user ?: $language->log_in, (isset($_c['_path']) ? $_c['_path'] : $_c['path']) . HTTP::query([
            'kick' => $url->path
        ]) . '#' . $_state['anchor'][1]); ?>
      </span><?php endif; ?>
    </span>
  </p>
  <?php echo Form::hidden('path', $url->path); ?>
  <?php echo Form::hidden('parent', $_id); ?>
  <?php echo Form::hidden('token', $token); ?>
</form>