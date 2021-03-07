<?php

$advance = State::get('x.user', true);
$author = $advance ? Is::user() : false;

?>
<form action="<?= $url . '/.comment' . substr($page->url, strlen($url . "")) . $url->query('&amp;'); ?>" class="comment-form<?= $parent ? ' is:reply' : ""; ?>" id="<?= $c['anchor'][0]; ?>" method="post" name="comment">
  <?= $alert; ?>
  <?php

  $tasks = [];
  $t = microtime();
  $guard = $state->x->comment->guard ?? null;
  foreach ([
      'author' => [
          'hint' => i('Anonymous'),
          'title' => i('Name'),
          'type' => 'text',
          'vital' => true
      ],
      'email' => [
          'hint' => S . i('hello') . S . '@' . S . $url->host . S,
          'title' => i('Email'),
          'type' => 'email',
          'vital' => true
      ],
      'link' => [
          'hint' => S . $url->protocol . S . $url->host . S,
          'title' => i('Link'),
          'type' => 'url'
      ]
  ] as $k => $v) {
      $id = 'f:' . crc32($k . $t);
      $tasks[$k] = [
          0 => 'p',
          1 => (new HTML([
              0 => 'label',
              1 => $v['title'] ?? "",
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
                  'placeholder' => $v['hint'] ?? null,
                  'required' => !empty($v['vital']),
                  'type' => $v['type'] ?? 'text'
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
          2 => ['for' => $id = 'f:' . crc32('content' . $t)]
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
      ])) . '<br><span>' . _\lot\x\comment\layout('comments:tasks.form', [[
          'create' => [
              0 => 'button',
              1 => i('Publish'),
              2 => [
                  'class' => 'button',
                  'id' => $id = 'f:' . crc32('x' . $t),
                  'type' => 'submit',
                  'value' => 1
              ]
          ],
          'cancel' => isset($c['page']['deep']) && $c['page']['deep'] > 0 ? [
              0 => 'a',
              1 => i('Cancel'),
              2 => [
                  'class' => 'button js:cancel',
                  'href' => $url->clean . $url->query('&amp;', ['parent' => false]) . '#' . $c['anchor'][0]
              ]
          ] : false,
          'enter' => $advance && !empty($c['guard']['user']) ? [
              0 => 'span',
              1 => (new HTML([
                  0 => 'a',
                  1 => $author ?: i('Log In'),
                  2 => [
                      'href' => $url . ($advance['guard']['path'] ?? $advance['path']) . $url->query('&amp;', ['kick' => trim($url->path, '/') . $url->query . '#' . $c['anchor'][0]])
                  ]
              ])),
              2 => ['class' => 'button is:user']
          ] : false
      ], $page, $deep ?? null], $comment ?? null, ' ') . '</span>',
      2 => []
  ];

  $tasks['parent'] = '<input name="comment[parent]" type="hidden" value="' . ($parent ? $parent->name : "") . '">';
  $tasks['token'] = '<input name="comment[token]" type="hidden" value="' . Guard::token('comment') . '">';

  if ($author) {
      unset($tasks['author'], $tasks['email'], $tasks['link']);
      $tasks = ['title' => [
          0 => 'h3',
          1 => i('Commenting as %s', '<a href="' . $user->url . '" rel="nofollow">' . $user . '</a>')
      ]] + $tasks;
      $tasks['author'] = '<input name="comment[author]" type="hidden" value="' . $author . '">';
  }

  ?>
  <?= _\lot\x\comment\layout('comments:form', [$tasks, $page, $deep ?? null], $comment ?? null); ?>
</form>
