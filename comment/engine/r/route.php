<?php namespace _\lot\x\comment;

// Set a new comment!
function route($form, $k) {
    $state = \state('comment');
    $guard = \state('comment:guard');
    $enter = \state('user') !== null && \Is::user();
    $error = $form['_error'] ?? 0;
    if ($k !== 'post' || !\is_file(PAGE . DS . $this[0] . '.page')) {
        \Alert::error('comment-for');
        ++$error;
    }
    $defaults = \array_replace_recursive(
        // Inherit to `page` configuration value
        (array) \Config::get('page', true),
        // Inherit to `comment` configuration value
        (array) \Config::get('comment', true)
    );
    $form = \array_replace_recursive($defaults, $form);
    $form['status'] = $enter ? 1 : 2;
    extract($form, \EXTR_SKIP);
    global $config, $language, $url;
    if (\Is::void($token) || !\Guard::check($token, 'comment')) {
        \Alert::error('comment-token');
        ++$error;
    }
    foreach (['author', 'email', 'link', 'content'] as $key) {
        if (!isset($form[$key])) {
            continue;
        }
        $k = 'comment' . \To::pascal($key);
        // Check for empty field(s)
        if (\Is::void($form[$key])) {
            if ($key !== 'link') { // `link` field is optional
                \Alert::error('comment-void-field', $language->{$k});
                ++$error;
            }
        }
        // Check for field(s) length
        if (isset($guard['max'][$key]) && \gt($form[$key], $guard['max'][$key])) {
            \Alert::error('comment-max', $language->{$k});
            ++$error;
        } else if (isset($guard['min'][$key]) && \lt($form[$key], $guard['min'][$key])) {
            if ($key !== 'link') { // `link` field is optional
                \Alert::error('comment-min', $language->{$k});
                ++$error;
            }
        }
    }
    if ($error === 0 && isset($author)) {
        $author = \strpos($author, '@') !== 0 ? \To::text($author) : $author;
    }
    if ($error === 0 && isset($content)) {
        $content = \To::text((string) $content, 'a,abbr,b,br,cite,code,del,dfn,em,i,img,ins,kbd,mark,q,span,strong,sub,sup,time,u,var', true);
        if ((!isset($type) || $type === 'HTML') && \strpos($content, '</p>') === false) {
            // Replace new line with `<br>` and `<p>` tag(s)
            $content = '<p>' . \str_replace(["\n\n", "\n"], ['</p><p>', '<br>'], $content) . '</p>';
        }
        // Permanently disable the `[[e]]` block(s) in comment
        if (\state('block') !== null) {
            $e = \Block::$config[0];
            $content = \str_replace([
                $e[0] . 'e' . $e[1], // `[[e]]`
                $e[0] . $e[2] . 'e' . $e[1] // `[[/e]]`
            ], "", $content);
        }
        // Temporarily disallow image(s) in comment to prevent XSS
        if (\strpos($content, '<img ') !== false) {
            $content = \preg_replace('#<img(?:\s[^>]*)?>#i', '<!-- $0 -->', $content);
        }
    }
    if ($error === 0 && !$enter) {
        if (!empty($email) && !\Is::email($email)) {
            \Alert::error('comment-pattern-field', $language->commentEmail);
            ++$error;
        }
        if (!empty($link) && !\Is::URL($link)) {
            \Alert::error('comment-pattern-field', $language->commentLink);
            ++$error;
        }
    }
    // Check for duplicate comment
    if (\Session::get('comment.content') === $content) {
        \Alert::error('comment-exist');
        ++$error;
    } else {
        // Block user by IP address
        if (!empty($guard['x']['ip'])) {
            $ip = \Get::IP();
            foreach ($guard['x']['ip'] as $v) {
                if ($ip === $v) {
                    \Alert::error('comment-ip', $ip);
                    ++$error;
                    break;
                }
            }
        }
        // Block user by UA keyword(s)
        if (!empty($guard['x']['ua'])) {
            $ua = \Get::UA();
            foreach ($guard['x']['ua'] as $v) {
                if (\stripos($ua, $v) !== false) {
                    \Alert::error('comment-ua', $ua);
                    ++$error;
                    break;
                }
            }
        }
        // Check for spam keyword(s) in comment
        if (!empty($guard['x']['query'])) {
            $any = ($author ?? "") . ($email ?? "") . ($link ?? "") . ($content ?? "");
            foreach ($guard['x']['query'] as $v) {
                if (\stripos($any, $v) !== false) {
                    \Alert::error('comment-query', $v);
                    ++$error;
                    break;
                }
            }
        }
    }
    // Store comment to file
    $t = \time();
    $anchor = $state['anchor'];
    $directory = COMMENT . DS . $this[0] . DS . \date('Y-m-d-H-i-s', $t);
    $file = $directory . '.' . ($x = $state['comment']['x'] ?? 'page');
    $this->status(200);
    if ($error > 0) {
        \Session::set('form', $form);
    } else {
        \Session::let('form');
        $data = [
            'author' => $author,
            'email' => $email ?: false,
            'link' => $link ?: false,
            'status' => $status,
            'content' => $content
        ];
        foreach ($defaults as $k => $v) {
            if (isset($data[$k]) && $data[$k] === $v) {
                unset($data[$k]);
            }
        }
        \Page::set($data)->saveTo($file, 0600);
        if (!\Is::void($parent)) {
            \File::set((new \Date($parent))->slug)->saveTo($directory . DS . 'parent.data', 0600);
        }
        \Hook::fire('on.comment.set', [null, null], new \File($file));
        \Alert::success('comment-create');
        \Session::set('comment', $data);
        if ($x === 'draft') {
            \Alert::info('comment-save');
        } else {
            \Guard::kick($this[0] . $url->query('&', ['parent' => false]) . '#' . \sprintf($anchor[0], \sprintf('%u', $t)));
        }
    }
    \Guard::kick($this[0] . $url->query . '#' . $anchor[1]);
}

\Route::set('.comment/*', 200, __NAMESPACE__ . "\\route");