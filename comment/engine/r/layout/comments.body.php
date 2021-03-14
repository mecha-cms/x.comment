<div class="comments-body">
  <?php if (is_dir(LOT . DS . 'comment')): ?>
    <?= x\comment\hook('comments-body', [[
        'content' => self::get(__DIR__ . DS . 'comments.content.php', $lot)
    ], $page, null]); ?>
  <?php else: ?>
    <p>Hi, this is just a friendly reminder that you are currently using a paid extension within the scope of commercial use. If you want to use this extension feature on a layout/theme that you make for sale, then you need to provide financial support for about 5 USD for each layout/theme product you make.</p>
    <p>Using this comment feature on a layout/theme that is made for free will not be affected by this license.</p>
    <p>Don&rsquo;t worry, the number of product sales will not accumulate the financial support that needs to be provided, the layout/theme variants that you create will.</p>
    <p>Your financial support will motivate me to continue developing this minimalist content management system. Support system and forums will always be available free of charge on <a href="https://github.com/mecha-cms/mecha/discussions" rel="nofollow" target="_blank">GitHub</a>.</p>
    <p><a class="button" href="https://paypal.me/tatautaufik" target="_blank">Donate for 5 USD</a></p>
    <p>To get rid of this message, make a test comment via the comment form below!</p>
  <?php endif; ?>
</div>
