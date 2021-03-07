<header class="comments-header">
  <?= _\lot\x\comment\layout('comments:header', [[
      'title' => [
          0 => 'h3',
          1 => $page->comments->title
      ]
  ], $page, null]); ?>
</header>
