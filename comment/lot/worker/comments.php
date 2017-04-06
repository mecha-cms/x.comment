<?php if ($page->comments->x && !$page->comments->i): ?>
<!-- comment disable and comment empty -->
<?php else: ?>
<?php $thread = Extend::state('comment', 'thread'); ?>
<section class="comments">
  <header class="comments-header">
    <h3><?php echo $page->comments->text; ?></h3>
  </header>
  <div class="comments-body">
    <?php if ($comments): ?>
    <ul class="comments">
      <?php foreach ($comments as $comment): ?>
      <?php if ($thread && $comment->parent) continue; ?>
      <?php if ($thread): ?>
      <?php $replys = []; ?>
      <?php foreach ($comments as $reply): ?>
      <?php if ($reply->parent && $reply->parent === Path::N($comment->path)): ?>
      <?php $replys[] = $reply; ?>
      <?php endif; ?>
      <?php endforeach; $i = count($replys); ?>
      <?php endif; ?>
      <li class="comment comment--status-<?php echo $comment->status; ?>" id="comment-<?php echo $comment->id; ?>">
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
        <?php if ($thread && $replys): ?>
        <ul class="comments replies" title="<?php echo $i . ' ' . $language->{$i === 1 ? 'reply' : 'replys'}; ?>">
          <?php foreach ($replys as $reply): ?>
          <li class="comment comment--status-<?php echo $reply->status; ?>" id="comment-<?php echo $reply->id; ?>">
            <figure class="comment-figure">
              <img class="comment-avatar" alt="" src="<?php echo $reply->avatar($url->protocol . 'www.gravatar.com/avatar/' . md5($reply->email) . '?s=70&amp;d=monsterid'); ?>" width="70" height="70">
            </figure>
            <header class="comment-header">
              <p class="comment-property">
                <time class="comment-time" datetime="<?php echo $reply->date->W3C; ?>"><?php echo $reply->date->{str_replace('-', '_', $site->language)} . ' ' . $reply->date->F4; ?></time>&#x20;
                <a class="comment-url" href="<?php echo $reply->url; ?>" rel="nofollow"></a>
              </p>
              <h4 class="comment-author">
                <?php if ($reply->link): ?>
                <a class="comment-link" href="<?php echo $reply->link; ?>" rel="nofollow" target="_blank"><?php echo $reply->author; ?></a>
                <?php else: ?>
                <span class="comment-link"><?php echo $reply->author; ?></span>
                <?php endif; ?>
              </h4>
            </header>
            <div class="comment-body"><?php echo $reply->content; ?></div>
            <footer class="comment-footer">
              <?php echo implode(' &#x00B7; ', Hook::fire('page.a.comment', [[], $reply, $replys, $page])); ?>
            </footer>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <footer class="comment-footer">
          <?php echo implode(' &#x00B7; ', Hook::fire('page.a.comment', [$thread && !$page->comments->x ? [HTML::a($language->comment_reply, HTTP::query(['parent' => $comment->id]) . '#form-comment', false, ['classes' => ['comment-parent', 'comment-reply-v'], 'id' => 'parent:' . $comment->id, 'title' => $language->comment_f_reply(To::text($comment->author . ""), true), 'rel' => 'nofollow'])] : [], $comment, $comments, $page])); ?>
        </footer>
      </li>
      <?php endforeach; ?>
    </ul>
    <?php else: ?>
    <p><?php echo $language->message_info_void($language->comments); ?></p>
    <?php endif; ?>
  </div>
  <?php if (!$page->comments->x): ?>
  <footer class="comments-footer">
    <?php $parent_id = Request::get('parent', null); ?>
    <?php $parent = $parent_id ? new Comment(COMMENT . DS . $url->path . DS . (new Date($parent_id))->slug . '.page') : null; ?>
    <?php if ($parent): ?>
    <h4><?php echo $language->comment_reply_to__(HTML::a($parent->author, $url->current . $url->query . '#comment-' . $parent->id, false, ['rel' => 'nofollow']), true); ?></h4>
    <?php endif; ?>
    <?php echo $message; ?>
    <form class="form-comment" id="form-comment" action="<?php echo $url->current; ?>/<?php echo Extend::state('comment', 'path', '-comment'); ?>" method="post">
      <?php echo Form::hidden('token', $token); ?>
      <p class="form-comment-input form-comment-input--author">
        <label for="form-comment-input:author"><?php echo $language->comment_author; ?></label>
        <span><?php echo Form::text('author', null, $language->comment_f_author, ['classes' => ['input', 'block'], 'id' => 'form-comment-input:author', 'required' => true]); ?></span>
      </p>
      <p class="form-comment-input form-comment-input--email">
        <label for="form-comment-input:email"><?php echo $language->comment_email; ?></label>
        <span><?php echo Form::email('email', null, $language->comment_f_email, ['classes' => ['input', 'block'], 'id' => 'form-comment-input:email', 'required' => true]); ?></span>
      </p>
      <p class="form-comment-input form-comment-input--link">
        <label for="form-comment-input:link"><?php echo $language->comment_link; ?></label>
        <span><?php echo Form::url('link', null, $language->comment_f_link, ['classes' => ['input', 'block'], 'id' => 'form-comment-input:link']); ?></span>
      </p>
      <div class="form-comment-input form-comment-input--content p">
        <label for="form-comment-input:content"><?php echo $language->comment_content; ?></label>
        <div><?php echo Form::textarea('content', null, $language->comment_f_content, ['classes' => ['textarea', 'block'], 'id' => 'form-comment-input:content', 'required' => true]); ?></div>
      </div>
      <?php echo Form::hidden('path', $url->path); ?>
      <?php echo Form::hidden('parent', $parent_id); ?>
      <p class="form-comment-button">
        <label></label>
        <span><?php echo Form::submit(null, null, $language->comment_publish, ['classes' => ['button', 'button-publish']]) . ($thread ? ' ' . HTML::a($language->comment_cancel, $url->current . '#form-comment', false, ['classes' => ['button', 'button-cancel', 'comment-reply-x']]) : ""); ?></span>
      </p>
    </form>
  </footer>
  <?php endif; ?>
</section>
<?php endif; ?>