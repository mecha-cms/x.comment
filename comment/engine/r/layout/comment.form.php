<?php

$i = $url->i;
$i = $i ? ($state->x->comment->path ?? '/comment') . $i : "";

?>
<form action="<?= $url . '/.comment/' . strtr($page->url, [
    $url . '/' => ""
]) . $i . $url->query('&amp;', [
    'parent' => false
]); ?>" class="comment-form<?= $parent ? ' is:reply' : ""; ?>" id="<?= $c['anchor'][0]; ?>" method="post" name="comment">
  <?= $alert; ?>
  <?php

  $tasks = [];
  $t = microtime();
  $guard = $state->x->comment->guard ?? null;
  foreach ([
      'author' => ['text', i('Name'), i('Anonymous'), true],
      'email' => ['email', i('Email'), S . i('hello') . S . '@' . S . $url->host . S, true],
      'link' => ['url', i('Link'), S . $url->protocol . S . $url->host . S]
  ] as $k => $v) {
      $id = 'the:' . dechex(crc32($k . $t));
      $tasks[$k] = [
          0 => 'p',
          1 => (new HTML([
              0 => 'label',
              1 => $v[1] ?? "",
              2 => ['for' => $id]
          ])) . '<br><span>' . (new HTML([
              0 => 'input',
              1 => false,
              2 => [
                  'class' => 'input width',
                  'id' => $id,
                  'maxlength' => $guard->max->{$k} ?? null,
                  'minlength' => $guard->min->{$k} ?? null,
                  'name' => 'comment[' . $k . ']',
                  'placeholder' => $v[2] ?? null,
                  'required' => !empty($v[3]),
                  'type' => $v[0] ?? 'text'
              ]
          ])) . '</span>',
          2 => []
      ];
  }

  $tasks['content'] = [
      0 => 'div',
      1 => (new HTML([
          0 => 'label',
          1 => i('Message'),
          2 => ['for' => $id = 'the:' . dechex(crc32('content' . $t))]
      ])) . '<br><div>' . (new HTML([
          0 => 'textarea',
          1 => "",
          2 => [
              'class' => 'textarea width',
              'id' => $id,
              'maxlength' => $guard->max->content ?? null,
              'minlength' => $guard->min->content ?? null,
              'name' => 'comment[content]',
              'placeholder' => $parent ? To::text(i('Reply to %s', (string) $parent->author)) : i('Message goes here...'),
              'required' => true
          ]
      ])) . '</div>',
      2 => ['class' => 'p']
  ];

  $tasks['tasks'] = [
      0 => 'p',
      1 => (new HTML([
          0 => 'label',
          1 => i('Tasks')
      ])) . '<br><span>' . x\comment\hook('comment-form-tasks', [[
          'publish' => [
              0 => 'button',
              1 => i('Publish'),
              2 => [
                  'class' => 'button',
                  'id' => $id = 'the:' . dechex(crc32('x' . $t)),
                  'type' => 'submit',
                  'value' => 1
              ]
          ],
          'cancel' => isset($c['page']['deep']) && $c['page']['deep'] > 0 ? [
              0 => 'a',
              1 => i('Cancel'),
              2 => [
                  'class' => 'button js:cancel',
                  'href' => $url->clean . $url->i . $url->query('&', [
                      'parent' => false
                  ]) . '#' . $c['anchor'][0]
              ]
          ] : false,
      ], $page, $deep ?? null], $comment ?? null, ' ') . '</span>'
  ];

  $tasks['parent'] = '<input name="comment[parent]" type="hidden" value="' . ($parent ? $parent->name : "") . '">';
  $tasks['token'] = '<input name="comment[token]" type="hidden" value="' . Guard::token('comment') . '">';

  ?>
  <?= x\comment\hook('comment-form', [$tasks, $page, $deep ?? null], $comment ?? null); ?>
</form>
