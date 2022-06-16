<form action="<?= strtr($page->url, [
    $url . '/' => $url . '/' . trim($state->x->comment->route ?? 'comment', '/') . '/'
]) . $url->query([
    'parent' => null
]); ?>" class="form-comment<?= $parent ? ' is:reply' : ""; ?>" id="comment" method="post" name="comment">
  <?php

  $tasks = ['alert' => self::alert()];
  $guard = (object) ($state->x->comment->guard ?? []);

  foreach ([
      'author' => ['text', i('Name'), i('Anonymous'), true],
      'email' => ['email', i('Email'), S . i('hello') . S . '@' . S . $url->host . S, true],
      'link' => ['url', i('Link'), S . $url->protocol . S . $url->host . S]
  ] as $k => $v) {
      $id = 'f:' . substr(uniqid(), 6);
      $tasks[$k] = [
          0 => 'p',
          1 => (new HTML([
              0 => 'label',
              1 => $v[1] ?? "",
              2 => [
                  'for' => $id
              ]
          ])) . '<br><span>' . (new HTML([
              0 => 'input',
              1 => false,
              2 => [
                  'id' => $id,
                  'maxlength' => $guard->max->{$k} ?? null,
                  'minlength' => $guard->min->{$k} ?? null,
                  'name' => 'comment[' . $k . ']',
                  'placeholder' => $v[2] ?? null,
                  'required' => !empty($v[3]),
                  'type' => $v[0] ?? 'text'
              ]
          ])) . '</span>'
      ];
  }

  $tasks['content'] = [
      0 => 'p',
      1 => (new HTML([
          0 => 'label',
          1 => i('Message'),
          2 => [
              'for' => $id = 'f:' . substr(uniqid(), 6)
          ]
      ])) . '<br><span>' . (new HTML([
          0 => 'textarea',
          1 => "",
          2 => [
              'id' => $id,
              'maxlength' => $guard->max->content ?? null,
              'minlength' => $guard->min->content ?? null,
              'name' => 'comment[content]',
              'placeholder' => $parent ? To::text(i('Reply to %s', (string) $parent->author)) : i('Message goes here...'),
              'required' => true
          ]
      ])) . '</span>'
  ];

  $tasks['tasks'] = [
      0 => 'p',
      1 => (new HTML([
          0 => 'label',
          1 => i('Tasks')
      ])) . '<br><span role="group">' . x\comment\hook('comment-form-tasks', [[
          'publish' => [
              0 => 'button',
              1 => i('Publish'),
              2 => [
                  'id' => $id = 'f:' . substr(uniqid(), 6),
                  'type' => 'submit',
                  'value' => 1
              ]
          ],
          'cancel' => isset($c['page']['deep']) && $c['page']['deep'] > 0 ? [
              0 => 'a',
              1 => i('Cancel'),
              2 => [
                  'class' => 'js:cancel',
                  'href' => $url->current(['parent' => null], 'comment'),
                  'role' => 'button'
              ]
          ] : false,
      ], $page, $deep ?? null], $comment ?? null, ' ') . '</span>'
  ];

  $tasks['parent'] = '<input name="comment[parent]" type="hidden" value="' . ($parent ? $parent->name : "") . '">';
  $tasks['token'] = '<input name="comment[token]" type="hidden" value="' . token('comment') . '">';

  ?>
  <?= x\comment\hook('comment-form', [$tasks, $page, $deep ?? null], $comment ?? null); ?>
</form>