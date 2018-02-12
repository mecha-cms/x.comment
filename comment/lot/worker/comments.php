<?php if ($url->path === "" || ($page->comments->x && !$page->comments->i)): ?>
<!-- is home page or (comment disable and comment empty) -->
<?php else:

$_state = Extend::state('comment');
$_thr = $_state['thread'];
$_anchor = $_state['anchor'];

?>
<section class="comments">
  <header class="comments-header">
    <h3><?php echo $page->comments->text; ?></h3>
  </header>
  <div class="comments-body">
    <?php if ($comments): ?>
    <ul class="comments">
      <?php foreach ($comments as $comment): ?>
      <?php if ($_thr && $comment->parent) continue; ?>
      <?php if ($_thr): ?>
      <?php if ($comment->parent) continue; ?>
      <?php $replys = []; ?>
      <?php foreach ($comments as $reply): ?>
      <?php if ($reply->parent && $reply->parent === Path::N($comment->path)): ?>
      <?php $replys[] = $reply; ?>
      <?php endif; ?>
      <?php endforeach; $_i = count($replys); ?>
      <?php endif; ?>
      <li class="comment comment-status:<?php echo $comment->status; ?>" id="<?php echo __replace__($_anchor[0], ['id' => $comment->id]); ?>">
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
        <?php if ($_thr && $replys): ?>
        <ul class="comments replies" data-title="<?php echo $_i . ' ' . $language->{$_i === 1 ? 'reply' : 'replys'}; ?>">
          <?php foreach ($replys as $reply): ?>
          <li class="comment comment-status:<?php echo $reply->status; ?>" id="<?php echo __replace__($_anchor[0], ['id' => $reply->id]); ?>">
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
              <?php echo implode('<span class="comment-s"></span>', Hook::fire('page.a.comment', [[], $reply, $replys, $page])); ?>
            </footer>
          </li>
          <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        <footer class="comment-footer">
          <?php echo implode('<span class="comment-s"></span>', Hook::fire('page.a.comment', [$_thr && !$page->comments->x ? ['reply' => HTML::a($language->comment_reply, HTTP::query(['parent' => $comment->id]) . '#' . $_anchor[1], false, ['class[]' => ['comment-a', 'comment-a:reply', 'comment-reply:v'], 'id' => 'parent:' . $comment->id, 'title' => $language->comment_f_reply(To::text($comment->author . ""), true), 'rel' => 'nofollow'])] : [], $comment, $comments, $page])); ?>
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
    <?php $_id = Request::get('parent', null); ?>
    <?php $parent = $_id ? new Comment(COMMENT . DS . $url->path . DS . (new Date($_id))->slug . '.page') : null; ?>
    <?php if ($parent): ?>
    <h4><?php echo $language->reply_to__(HTML::a($parent->author, implode(HTTP::query() . '#', explode('#', $parent->url, 2)), false, ['rel' => 'nofollow']), true); ?></h4>
    <?php endif; ?>
    <form class="form-comment<?php echo $parent ? ' on-reply' : ""; ?>" id="<?php echo $_anchor[1]; ?>" action="<?php echo $url->current . '/' . $_state['path'] . HTTP::query(); ?>" method="post">
      <?php echo $message; ?>
      <?php echo Form::hidden('token', $token); ?>
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
      <div class="form-comment-textarea form-comment-textarea:content p">
        <label for="form-comment-textarea:content"><?php echo $language->comment_content; ?></label>
        <div><?php echo Form::textarea('*content', null, $language->comment_f_content, ['class[]' => ['textarea', 'block'], 'id' => 'form-comment-textarea:content']); ?></div>
      </div>
      <?php echo Form::hidden('path', $url->path); ?>
      <?php echo Form::hidden('parent', $_id); ?>
      <p class="form-comment-button form-comment-button:state">
        <label for="form-comment-button:state"></label>
        <span><?php echo Form::submit('state', null, $language->comment_publish, ['class[]' => ['button', 'button-submit', 'set'], 'id' => 'form-comment-button:state']) . ($_thr ? ' ' . HTML::a($language->comment_cancel, $url->current . '#' . $_anchor[1], false, ['class[]' => ['button', 'button-reset', 'comment-a', 'comment-a:reset', 'comment-reply:x']]) : ""); ?></span>
      </p>
    </form>
  </footer>
  <?php endif; ?>
</section>
<?php endif; ?>