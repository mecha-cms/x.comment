<div class="comments-body">
  <?php if (is_dir(LOT . DS . 'comment')): ?>
    <?= x\comment\hook('comments-body', [[
        'content' => self::get(__DIR__ . DS . 'comments.content.php', $lot)
    ], $page, null]); ?>
  <?php else: ?>
    <div class="comments-note">
      <p>Hi, this is just friendly reminder that you are currently using a paid extension within the scope of commercial use. If you want to use this extension feature on a layout/theme that you make for sale, then you need to provide financial support for about <strong>5 USD</strong> for each layout/theme variant you make.</p>
      <p>Don&rsquo;t worry, the number of product sales will not accumulate the financial support that needs to be provided, the layout/theme variants that you make will.</p>
      <p>However, since extensions are separate from layout, I have to make things clear. Your layout/theme will be deemed to contain a comment feature if:</p>
      <ol>
        <li>Your client wants a comment feature on your layout/theme and you make it happen.</li>
        <li>You say either in writing or verbally that the layout/theme you are selling contains a comment feature, even if you state that it requires a comment extension to install. This can be proven by the presence of <code>self::comments()</code> code which will insert the comment feature when available, or by the presence of CSS code to style the comments area in your layout/theme.</li>
        <li>You pack the layout/theme along with the content management system and the necessary extensions (including this extension) for your client to install.</p>
      </ol>
      <p>Using this comment feature on a layout/theme that is made for free will not be affected by this license.</p>
      <p>Your financial support will motivate me to continue developing this minimalist content management system. Support system and forums will always be available free of charge on <a href="https://github.com/mecha-cms/mecha/discussions" rel="nofollow" target="_blank">GitHub</a>.</p>
      <p><a class="button" href="https://paypal.me/tatautaufik" target="_blank">Donate for 5 USD</a></p>
      <p>Please add your intention in making a donation, which is to use this comment feature on a layout/theme that you are selling. And it will be even better if you show me your demo page or sales page of your layout/theme. I am very interested in seeing what works you have made using this content management system.</p>
      <p>To get rid of this message, make a test comment via the comment form below!</p>
    </div>
  <?php endif; ?>
</div>
