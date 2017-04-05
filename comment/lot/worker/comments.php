<section class="comments">
  <header class="comments-header">
    <h3><?php echo $page->comments->text; ?></h3>
  </header>
  <div class="comments-body">
    <?php if ($comments): ?>
    <ul class="comments">
    <?php foreach ($comments as $comment): ?>
    <li class="comment comment--status-<?php echo $comment->status; ?>" id="comment-<?php echo $comment->id; ?>">
      <figure class="comment-avatar">
        <img alt="" src="<?php echo $url->protocol . 'www.gravatar.com/avatar/' . md5($comment->email) . '?s=60&amp;d=monsterid'; ?>" width="60" height="60">
      </figure>
      <header class="comment-header">
        <?php if ($comment->link): ?>
        <a class="comment-link" href="<?php echo $comment->link; ?>" rel="nofollow" target="_blank"><?php echo $comment->author; ?></a>
        <?php else: ?>
        <span class="comment-link"><?php echo $comment->author; ?></span>
        <?php endif; ?>
        <span class="comment-date">
          <time class="comment-time" datetime="<?php echo $comment->date->W3C; ?>"><?php echo $comment->date->{str_replace('-', '_', $site->language)}; ?></time>&#x20;
          <a class="comment-url" href="<?php echo $comment->url; ?>" rel="nofollow">#</a>
        </span>
      </header>
      <div class="comment-body"><?php echo $comment->content; ?></div>
      <footer class="comment-footer">
        <?php echo implode(' / ', Hook::fire('page.a.comment', [[HTML::a($language->comment_reply, HTTP::query(['parent' => $comment->id]) . '#form-comment', false, ['classes' => ['comment-parent'], 'id' => 'parent:' . $comment->id, 'title' => $language->comment_reply_to__(To::text($comment->author . ""), true)])]])); ?>
      </footer>
    </li>
    <?php endforeach; ?>
    </ul>
    <?php else: ?>
    <p><?php echo $language->message_info_void($language->comments); ?></p>
    <?php endif; ?>
  </div>
  <footer class="comments-footer">
    <?php echo $message; ?>
    <form class="form-comment" id="form-comment" action="<?php echo $url->current; ?>/<?php echo Extend::state('comment', 'path', '-comment'); ?>" method="post">
      <?php echo Form::hidden('token', $token); ?>
      <?php $parent_id = Request::get('parent', null); ?>
      <?php $parent = $parent_id ? new Comment(COMMENT . DS . $url->path . DS . (new Date($parent_id))->slug . '.page') : false; ?>
      <p class="f f-author">
        <label for="f-author"><?php echo $language->comment_author; ?></label>
        <span><?php echo Form::text('author', null, null, ['classes' => ['input', 'block'], 'id' => 'f-author']); ?></span>
      </p>
      <p class="f f-email">
        <label for="f-email"><?php echo $language->comment_email; ?></label>
        <span><?php echo Form::email('email', null, null, ['classes' => ['input', 'block'], 'id' => 'f-email']); ?></span>
      </p>
      <p class="f f-link">
        <label for="f-link"><?php echo $language->comment_url; ?></label>
        <span><?php echo Form::url('link', null, $url->protocol, ['classes' => ['input', 'block'], 'id' => 'f-link']); ?></span>
      </p>
      <div class="f f-content p">
        <label for="f-content"><?php echo $language->comment_content; ?></label>
        <div><?php echo Form::textarea('content', null, $parent ? $language->comment_reply_to__($parent->author . "", true) : null, ['classes' => ['textarea', 'block'], 'id' => 'f-content']); ?></div>
      </div>
      <?php echo Form::hidden('parent', $parent_id); ?>
      <p class="f">
        <label></label>
        <span><?php echo Form::submit('set', 1, $language->comment_publish, ['classes' => ['button', 'button-publish']]) . ' ' . HTML::a($language->comment_cancel, $url->current, false, ['classes' => ['button', 'button-cancel', 'comment-reply-x']]); ?></span>
      </p>
    </form>
  </footer>
</section>