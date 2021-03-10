<header class="comments-header">
  <?= x\comment\hook('comments:header', [[
      'title' => [
          0 => 'h3',
          1 => $page->comments->title
      ]
  ], $page, null]); ?>
</header>
