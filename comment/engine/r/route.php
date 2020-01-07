<?php namespace _\lot\x\comment;

// Set a new comment!
function route($any) {
    $active = null !== \State::get('x.user') && \Is::user();
    $state = \State::get('x.comment', true);
    $error = 0;
    if (\Request::is('Get') || !\is_file(\LOT . \DS . 'page' . \DS . $any . '.page')) {
        \Alert::error('You cannot write a comment here. This is usually due to the page data that is dynamically generated.');
        ++$error;
    }
    $default = \array_replace_recursive(
        (array) \State::get('x.page.page', true),
        (array) ($state['page'] ?? [])
    );
    $lot = \array_replace_recursive($default, \Post::get('comment'));
    $lot['status'] = $active ? 1 : 2;
    extract($lot, \EXTR_SKIP);
    global $url;
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
        $content = \To::text((string) $content, 'a,abbr,b,br,cite,code,del,dfn,em,i,img,ins,kbd,mark,q,span,strong,sub,sup,time,u,var', true);
        if (
            (
                !isset($type) ||
                'HTML' === $type ||
                'text/html' === $type
            ) &&
            false === \strpos($content, '</p>')
        ) {
            // Replace new line with `<br>` and `<p>` tag(s)
            $content = '<p>' . \strtr($content, [
                "\n\n" => '</p><p>',
                "\n" => '<br>'
            ]) . '</p>';
        }
        // Permanently disable the `[[e]]` block(s) in comment
        if (null !== \State::get('x.block')) {
            $e = \Block::$state[0];
            $content = \str_replace([
                $e[0] . 'e' . $e[1], // `[[e]]`
                $e[0] . $e[2] . 'e' . $e[1] // `[[/e]]`
            ], "", $content);
        }
        // Temporarily disallow image(s) in comment to prevent XSS
        if (false !== \strpos($content, '<img ')) {
            $content = \preg_replace('#<img(?:\s[^>]*)?>#i', '<!-- $0 -->', $content);
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
    } else {
        // Block user by IP address
        if (!empty($guard['x']['ip'])) {
            $ip = \Client::IP();
            foreach ($guard['x']['ip'] as $v) {
                if ($v === $ip) {
                    \Alert::error('Blocked IP address: %s', $ip);
                    ++$error;
                    break;
                }
            }
        }
        // Block user by UA keyword(s)
        if (!empty($guard['x']['ua'])) {
            $ua = \Client::UA();
            foreach ($guard['x']['ua'] as $v) {
                if (false !== \stripos($ua, $v)) {
                    \Alert::error('Blocked user agent: %s', $ua);
                    ++$error;
                    break;
                }
            }
        }
        // Check for spam keyword(s) in comment
        if (!empty($guard['x']['query'])) {
            $words = ($author ?? "") . ($email ?? "") . ($link ?? "") . ($content ?? "");
            foreach ($guard['x']['query'] as $v) {
                if (false !== \stripos($words, $v)) {
                    \Alert::error('Please choose another word: %s', $v);
                    ++$error;
                    break;
                }
            }
        }
    }
    // Store comment to file
    $t = \time();
    $anchor = $state['anchor'];
    $directory = \LOT . \DS . 'comment' . \DS . $any . \DS . \date('Y-m-d-H-i-s', $t);
    $file = $directory . '.' . ($x = $state['page']['x'] ?? 'page');
    if ($error > 0) {
        \Session::set('form.comment', $lot);
    } else {
        \Session::let('form.comment');
        foreach ($data = [
            'author' => $author,
            'email' => $email ?? false ?: false,
            'link' => $link ?? false ?: false,
            'status' => $status,
            'content' => $content
        ] as $k => $v) {
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
        \Hook::fire('on.comment.set', [$file]);
        \Alert::success('Comment created.');
        \Session::set('comment', $data);
        if ('draft' === $x) {
            \Alert::info('Your comment will be visible once approved by the author.');
        } else {
            \Guard::kick($any . $url->query('&', ['parent' => false]) . '#' . \sprintf($anchor[0], \sprintf('%u', $t)));
        }
    }
    \Guard::kick($any . $url->query . '#' . $anchor[1]);
}

\Route::set('.comment/*', 200, __NAMESPACE__ . "\\route");