<?php namespace x\comment\route;

function get($any) {
    if (\File::exist([
        \LOT . \DS . 'page' . \DS . $any . \DS . 'comment.archive',
        \LOT . \DS . 'page' . \DS . $any . \DS . 'comment.page'
    ])) {
        // Load native page if exists
        \Route::fire('*', [$any . '/comment']);
    }
    extract($GLOBALS, \EXTR_SKIP);
    $count = \q(\g(\LOT . \DS . 'comment' . \DS . $any, 'page'));
    $chunk = $state->x->comment->page->chunk ?? $count;
    $max = (int) \ceil($count / $chunk); // Get last comment page
    $current = $url['i'] ?? $max;
    // Check for invalid comment page offset
    if ($current < 1 || $current > $max) {
        // Redirect to the default comment page offset
        \Guard::kick('/' . $any . $url->query);
    }
    \State::set([
        'is' => [
            'error' => false
        ]
    ]);
    \Route::fire('*', [$any]);
}

function set($any) {
    extract($GLOBALS, \EXTR_SKIP);
    // Normalize current path
    $path = $state->x->comment->path ?? '/comment';
    $i = $url->i;
    if ($i && \substr($any, -strlen($path)) === $path) {
        $any = \dirname($any);
        $i = $path . $i;
    }
    $active = null !== ($state->x->user ?? null) && \Is::user();
    $anchor = $state->x->comment->anchor ?? [];
    if (\Request::is('Get')) {
        \Alert::error('Method not allowed.');
        \Guard::kick('/' . $any . $i . $url->query . '#' . $anchor[0]);
    }
    if (!\is_file(\LOT . \DS . 'page' . \DS . $any . '.page')) {
        \Alert::error('You cannot write a comment here. This is usually due to the page data that is dynamically generated.');
        \Guard::kick('/' . $any . $i . $url->query . '#' . $anchor[0]);
    }
    $error = 0;
    $data_default = \array_replace_recursive(
        (array) \a($state->x->page->page ?? []),
        (array) \a($state->x->comment->page ?? [])
    );
    $data = \array_replace_recursive($data_default, (array) \Post::get('comment'));
    $data['status'] = $active ? 1 : 2;
    if (empty($data['token']) || !\Guard::check($data['token'], 'comment')) {
        \Alert::error('Invalid token.');
        ++$error;
    }
    $guard = $state->x->comment->guard ?? [];
    foreach (['author', 'email', 'link', 'content'] as $key) {
        if (!isset($data[$key])) {
            continue;
        }
        $k = \ucfirst($key);
        // Check for empty field(s)
        if (\Is::void($data[$key])) {
            if ('link' !== $key) { // `link` field is optional
                \Alert::error('Please fill out the %s field.', $k);
                ++$error;
            }
        }
        // Check for field(s) length
        if (isset($guard->max->{$key}) && \gt($data[$key], $guard->max->{$key})) {
            \Alert::error('%s too long.', $k);
            ++$error;
        } else if (isset($guard->min->{$key}) && \lt($data[$key], $guard->min->{$key})) {
            if ('link' !== $key) { // `link` field is optional
                \Alert::error('%s too short.', $k);
                ++$error;
            }
        }
    }
    // Sanitize comment author
    if (0 === $error && isset($data['author'])) {
        if (0 === \strpos($data['author'], '@')) {
            $data['author'] = '@' . \To::kebab($data['author']);
        } else {
            $data['author'] = \To::text($data['author']);
        }
    }
    // Sanitize comment content
    if (0 === $error && isset($data['content'])) {
        // Permanently disable PHP expression written in the comment body. Why? I don’t know!
        $data['content'] = \strtr($data['content'], [
            '<?php' => '&lt;?php',
            '<?=' => '&lt;?=',
            '?>' => '?&gt;'
        ]);
        // Permanently disable the `[[e]]` block(s) written in the comment body
        if (isset($state->x->block)) {
            $e = \Block::$state[0];
            $data['content'] = \strtr($data['content'], [
                $e[0] . 'e' . $e[1] => "", // `[[e]]`
                $e[0] . $e[2] . 'e' . $e[1] => "" // `[[/e]]`
            ]);
        }
        // Implement default XSS filter to the comment with type of `HTML` or `text/html`
        if (!isset($data['type']) || 'HTML' === $data['type'] || 'text/html' === $data['type']) {
            $tags = 'a,abbr,b,br,cite,code,del,dfn,em,i,img,ins,kbd,mark,q,span,strong,sub,sup,time,u,var';
            $data['content'] = \strip_tags($data['content'], '<' . \strtr($tags, [',' => '><']) . '>');
            // Replace potential XSS via HTML attribute(s) into a `data-*` attribute(s)
            $data['content'] = \preg_replace_callback('/<(' . \strtr($tags, ',', '|') . ')(\s[^>]*)?>/', function($m) {
                if ('img' === $m[1]) {
                    // Temporarily disallow image(s) in comment to prevent XSS
                    return '&lt;' . $m[1] . $m[2] . '&gt;';
                }
                if (!empty($m[2])) {
                    // Replace `onerror` with `data-onerror`
                    $m[2] = \preg_replace('/(\s)on(\w+)=([\'"]?)/', '$1data-on$2=$3', $m[2]);
                    // Replace `javascript:*` value with `javascript:;`
                    $m[2] = \preg_replace([
                        '/="javascript:[^"]+"/',
                        '/=\'javascript:[^\']+\'/',
                        '/=javascript:[^\s>]+/'
                    ], '="javascript:;"', $m[2]);
                    return '<' . $m[1] . $m[2] . '>';
                }
                return '<' . $m[1] . '>';
            }, $data['content']);
            // Replace new line with `<br>` and `<p>` tag(s)
            $data['content'] = '<p>' . \strtr($data['content'], [
                "\n\n" => '</p><p>',
                "\n" => '<br>'
            ]) . '</p>';
        }
    }
    if (0 === $error && !$active) {
        // Check for email format
        if (!empty($data['email']) && !\Is::email($data['email'])) {
            \Alert::error('Invalid %s format.', 'Email');
            ++$error;
        }
        // Check for link format
        if (!empty($data['link']) && !\Is::URL($data['link'])) {
            \Alert::error('Invalid %s format.', 'Link');
            ++$error;
        }
    }
    // Check for duplicate comment
    if ($data['content'] === \Session::get('comment.content')) {
        \Alert::error('You have sent that comment already.');
        ++$error;
    }
    // Store comment to file
    $t = \time();
    $folder = \LOT . \DS . 'comment' . \DS . $any . \DS . \date('Y-m-d-H-i-s', $t);
    $file = $folder . '.' . ($x = $state->x->comment->page->x ?? 'page');
    if ($error > 0) {
        \Session::set('form.comment', $data);
    } else {
        \Session::let('form.comment');
        $values = [
            'author' => null,
            'email' => null,
            'link' => null,
            'status' => null,
            'type' => null,
            'content' => ""
        ];
        foreach ($data as $k => $v) {
            if (null === $v || !\array_key_exists($k, $values)) {
                continue;
            }
            $values[$k] = $v;
        }
        foreach ($data_default as $k => $v) {
            if (isset($values[$k]) && $v === $values[$k]) {
                unset($values[$k]);
            }
        }
        $values = \drop($values);
        (new \File($file))->set(\To::page($values))->save(0600);
        if (isset($data['parent']) && !\Is::void($data['parent'])) {
            (new \File($folder . \DS . 'parent.data'))->set((new \Time($data['parent']))->name)->save(0600);
        }
        \Alert::success('Comment created.');
        if ('draft' === $x) {
            \Alert::info('Your comment will be visible once approved by the author.');
        }
        \Hook::fire('on.comment.set', [$file]);
        \Session::set('comment', $values);
        if ('draft' !== $x) {
            \Guard::kick('/' . $any . $i . $url->query('&', [
                'parent' => false
            ]) . '#' . \sprintf($anchor[2], \sprintf('%u', $t)));
        }
    }
    \Guard::kick('/' . $any . $i . $url->query . '#' . $anchor[0]);
}

\Route::set('.comment/*', 200, __NAMESPACE__ . "\\set", 10);
\Route::set('*' . ($state->x->comment->path ?? '/comment'), __NAMESPACE__ . "\\get", 10);