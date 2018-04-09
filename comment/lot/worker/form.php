<?php $_id = HTTP::get('parent', null); ?>
<?php $_parent = $_id ? new Comment(COMMENT . DS . $url->path . DS . (new Date($_id))->slug . '.page') : null; ?>
<?php $_user = Extend::exist('user') ? Is::user() : false; ?>
<form class="form-comment<?php echo $_parent ? ' on-reply' : ""; ?>" id="<?php echo $_state['anchor'][1]; ?>" action="<?php echo $url->current . '/' . $_state['path'] . $url->query('&amp;'); ?>" method="post">
  <?php echo $message; ?>
  <?php if ($_parent): ?>
  <h4><?php echo $language->comment_f_reply(HTML::a($_parent->author, implode($url->query . '#', explode('#', $_parent->url, 2)), false, ['rel' => 'nofollow']), true); ?></h4>
  <?php elseif ($_user): ?>
  <h4><?php echo $language->comment_f_as(HTML::a($user, $user->url, false, ['rel' => 'nofollow']), true); ?></h4>
  <?php endif; ?>
  <?php if ($_user): ?>
  <?php echo Form::hidden('author', $_user); ?>
  <?php else: ?>
  <p class="form-comment-input form-comment-input:author">
    <label for="form-comment-input:author"><?php echo $language->comment_author; ?></label>
    <span><?php echo Form::text('*author', null, $language->comment_f_author, ['class[]' => ['input', 'block'], 'id' => 'form-comment-input:author']); ?></span>
  </p>
  <p class="form-comment-input form-comment-input:email">
    <label for="form-comment-input:email"><?php echo $language->comment_email; ?></label>
    <span><?php echo Form::email('*email', null, $language->comment_f_email, ['class[]' => ['input', 'block'], 'id' => 'form-comment-input:email']); ?></span>
  </p>
  <p class="form-comment-input form-comment-input:link">
    <label for="form-comment-input:link"><?php echo $language->comment_link; ?></label>
    <span><?php echo Form::url('link', null, $language->comment_f_link, ['class[]' => ['input', 'block'], 'id' => 'form-comment-input:link']); ?></span>
  </p>
  <?php endif; ?>
  <div class="form-comment-textarea form-comment-textarea:content p">
    <label for="form-comment-textarea:content"><?php echo $language->comment_content; ?></label>
    <div><?php echo Form::textarea('*content', null, $language->comment_f_content, ['class[]' => ['textarea', 'block'], 'id' => 'form-comment-textarea:content']); ?></div>
  </div>
  <p class="form-comment-button form-comment-button:state">
    <label for="form-comment-button:state"></label>
    <span>
      <?php echo Form::submit('state', null, $language->comment_publish, ['class[]' => ['button', 'button-submit'], 'id' => 'form-comment-button:state']) . ($_state['level'] > 1 ? ' ' . HTML::a($language->comment_cancel, $url->current . '#' . $_state['anchor'][1], false, ['class[]' => ['button', 'button-reset', 'comment-a', 'comment-a:reset', 'comment-reply:x']]) : ""); ?> <span class="comment-user button">
        <?php if (!empty($_state['enter']) && Extend::exist('user')): ?>
        <?php echo HTML::a($_user ?: $language->log_in, Extend::state('user', 'path') . HTTP::query(['kick' => $url->path])); ?>
        <?php endif; ?>
      </span>
    </span>
  </p>
  <?php echo Form::hidden('path', $url->path); ?>
  <?php echo Form::hidden('parent', $_id); ?>
  <?php echo Form::hidden('token', $token); ?>
</form>