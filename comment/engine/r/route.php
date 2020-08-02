<?php namespace _\lot\x\comment;

function route($any) {
    $n = \State::get('x.comment.path') ?? '/comment';
    if (\File::exist([
        \LOT . \DS . 'page' . \DS . $any . $n . '.archive',
        \LOT . \DS . 'page' . \DS . $any . $n . '.page'
    ])) {
        \Route::fire('*', [$any . $n]);
    }
    \State::set([
        'is' => [
            'error' => false
        ]
    ]);
    \Route::fire('*', [$any]);
}

function set($any) {
    global $url;
    $active = null !== \State::get('x.user') && \Is::user();
    $state = \State::get('x.comment', true);
    $anchor = $state['anchor'];
    if (\Request::is('Get') || !\is_file(\LOT . \DS . 'page' . \DS . $any . '.page')) {
        \Alert::error('You cannot write a comment here. This is usually due to the page data that is dynamically generated.');
        \Guard::kick($any . $url->query . '#' . $anchor[1]);
    }
    $error = 0;
    $default = \array_replace_recursive(
        (array) \State::get('x.page.page', true),
        (array) ($state['page'] ?? [])
    );
    $lot = \array_replace_recursive($default, (array) \Post::get('comment'));
    $lot['status'] = $active ? 1 : 2;
    extract($lot, \EXTR_SKIP);
    if (empty($token) || !\Guard::check($token, 'comment')) {
        \Alert::error('Invalid token.');
        ++$error;
    }
    $guard = $state['guard'] ?? [];
    foreach (['author', 'email', 'link', 'content'] as $key) {
        if (!isset($lot[$key])) {
            continue;
        }
        $k = \ucfirst($key);
        // Check for empty field(s)
        if (\Is::void($lot[$key])) {
            if ('link' !== $key) { // `link` field is optional
                \Alert::error('Please fill out the %s field.', $k);
                ++$error;
            }
        }
        // Check for field(s) length
        if (isset($guard['max'][$key]) && \gt($lot[$key], $guard['max'][$key])) {
            \Alert::error('%s too long.', $k);
            ++$error;
        } else if (isset($guard['min'][$key]) && \lt($lot[$key], $guard['min'][$key])) {
            if ('link' !== $key) { // `link` field is optional
                \Alert::error('%s too short.', $k);
                ++$error;
            }
        }
    }
    if (0 === $error && isset($author)) {
        $author = 0 !== \strpos($author, '@') ? \To::text($author) : $author;
    }
    if (0 === $error && isset($content)) {
        // Permanently disable PHP expression written in the comment body. Why? I don’t know!
        $content = \strtr($content, [
            '<?php' => '&lt;?php',
            '<?=' => '&lt;?=',
            '?>' => '?&gt;'
        ]);
        // Permanently disable the `[[e]]` block(s) written in the comment body
        if (null !== \State::get('x.block')) {
            $e = \Block::$state[0];
            $content = \str_replace([
                $e[0] . 'e' . $e[1], // `[[e]]`
                $e[0] . $e[2] . 'e' . $e[1] // `[[/e]]`
            ], "", $content);
        }
        // Implement default XSS filter to the comment with type of `HTML` or `text/html`
        if (!isset($type) || 'HTML' === $type || 'text/html' === $type) {
            $tags = 'a,abbr,b,br,cite,code,del,dfn,em,i,img,ins,kbd,mark,q,span,strong,sub,sup,time,u,var';
            $content = \strip_tags($content, '<' . \strtr($tags, [',' => '><']) . '>');
            // Replace potential XSS via HTML attribute(s) into a `data-*` attribute(s)
            $content = \preg_replace_callback('/<(' . \strtr($tags, ',', '|') . ')(\s[^>]*)?>/', function($m) {
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
            }, $content);
            // Replace new line with `<br>` and `<p>` tag(s)
            $content = '<p>' . \strtr($content, [
                "\n\n" => '</p><p>',
                "\n" => '<br>'
            ]) . '</p>';
        }
    }
    if (0 === $error && !$active) {
        if (!empty($email) && !\Is::email($email)) {
            \Alert::error('Invalid %s format.', 'Email');
            ++$error;
        }
        if (!empty($link) && !\Is::URL($link)) {
            \Alert::error('Invalid %s format.', 'Link');
            ++$error;
        }
    }
    // Check for duplicate comment
    if ($content === \Session::get('comment.content')) {
        \Alert::error('You have sent that comment already.');
        ++$error;
    }
    // Store comment to file
    $t = \time();
    $directory = \LOT . \DS . 'comment' . \DS . $any . \DS . \date('Y-m-d-H-i-s', $t);
    $file = $directory . '.' . ($x = $state['page']['x'] ?? 'page');
    if ($error > 0) {
        \Session::set('form.comment', $lot);
    } else {
        \Session::let('form.comment');
        $data = [
            'author' => $author,
            'email' => ($email ?? false) ?: false,
            'link' => ($link ?? false) ?: false,
            'status' => $status,
            'type' => ($type ?? false) ?: false,
            'content' => $content
        ];
        foreach ($data as $k => $v) {
            if (!isset($v) || false === $v) {
                unset($data[$k]);
            }
        }
        foreach ($default as $k => $v) {
            if (isset($data[$k]) && $data[$k] === $v) {
                unset($data[$k]);
            }
        }
        (new \File($file))->set(\To::page($data))->save(0600);
        if (!\Is::void($parent)) {
            (new \File($directory . \DS . 'parent.data'))->set((new \Time($parent))->name)->save(0600);
        }
        \Alert::success('Comment created.');
        if ('draft' === $x) {
            \Alert::info('Your comment will be visible once approved by the author.');
        }
        \Hook::fire('on.comment.set', [$file]);
        \Session::set('comment', $data);
        if ('draft' !== $x) {
            \Guard::kick($any . $url->query('&', ['parent' => false]) . '#' . \sprintf($anchor[0], \sprintf('%u', $t)));
        }
    }
    \Guard::kick($any . $url->query . '#' . $anchor[1]);
}

\Route::set('.comment/*', 200, __NAMESPACE__ . "\\set");
\Route::set('*' . (\State::get('x.comment.path') ?? '/comment'), __NAMESPACE__ . "\\route", 10);
