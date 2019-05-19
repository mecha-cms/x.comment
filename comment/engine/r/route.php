<?php

// Set a new comment!
$state = extend('comment');
Route::set('*/.comment', function($form, $k) use($config, $language, $state, $url) {
    $errors = 0;
    if ($k !== 'POST' || !is_file(PAGE . DS . $this[0] . '.page')) {
        Message::error('comment-source');
        ++$errors;
    }
    $enter = extend('user') !== null && Is::user();
    $form['comment'] = array_replace_recursive($state['comment'], $form['comment'], [
        'status' => $form['comment']['status'] ?? ($enter ? 1 : false)
    ]);
    if (!isset($form['token']) || !$this->check($form['token'], 'comment')) {
        Message::error('comment-token');
        ++$errors;
    }
    extract($form['comment'], EXTR_SKIP);
    if (!isset($author) || trim($author) === "") {
        Message::error('comment-void-field', $language->commentAuthor);
        ++$errors;
    } else {
        $author = strpos($author, '@') !== 0 ? To::text($author) : $author;
        if (gt($author, $state['max']['author'] ?? 0)) {
            Message::error('comment-max', $language->commentAuthor);
            ++$errors;
        } else if (lt($author, $state['min']['author'] ?? 0)) {
            Message::error('comment-min', $language->commentAuthor);
            ++$errors;
        }
    }
    if (!$enter) {
        if (!isset($email) || trim($email) === "") {
            Message::error('comment-void-field', $language->commentMail);
            ++$errors;
        } else if (!Is::eMail($email)) {
            Message::error('comment-pattern-field', $language->commentMail);
            ++$errors;
        } else if (gt($email, $state['max']['email'] ?? 0)) {
            Message::error('comment-max', $language->commentMail);
            ++$errors;
        } else if (lt($email, $state['min']['email'] ?? 0)) {
            Message::error('comment-min', $language->commentMail);
            ++$errors;
        }
        if (!empty($link)) {
            if (!Is::URL($link)) {
                Message::error('comment-pattern-field', $language->commentLink);
                ++$errors;
            } else if (gt($link, $state['max']['link'] ?? 0)) {
                Message::error('comment-max', $language->commentLink);
                ++$errors;
            } else if (lt($link, $state['min']['link'] ?? 0)) {
                Message::error('comment-min', $language->commentLink);
                ++$errors;
            }
        }
    }
    if (!isset($content) || trim($content) === "") {
        Message::error('comment-void-field', $language->commentContent);
        ++$errors;
    } else {
        $content = To::text((string) $content, HTML_WISE . ',img', true);
        if ((!isset($type) || $type === 'HTML') && strpos($content, '</p>') === false) {
            // Replace new line with `<br>` and `<p>` tag(s)
            $content = '<p>' . str_replace(["\n\n", "\n"], ['</p><p>', '<br>'], $content) . '</p>';
        }
        // Permanently disable the `[[e]]` block(s) in comment
        if (extend('block') !== null) {
            $e = Block::$config[0];
            $content = str_replace([
                $e[0] . 'e' . $e[1], // `[[e]]`
                $e[0] . $e[2] . 'e' . $e[1] // `[[/e]]`
            ], "", $content);
        }
        // Temporarily disallow image(s) in comment to prevent XSS
        $content = preg_replace('#<img(?:\s[^>]*)?>#i', '<!-- $0 -->', $content);
        if (gt($content, $state['max']['content'] ?? 0)) {
            Message::error('comment-max', $language->commentContent);
            ++$errors;
        } else if (lt($content, $state['min']['content'] ?? 0)) {
            Message::error('comment-min', $language->commentContent);
            ++$errors;
        }
    }
    // Check for duplicate comment
    if (Session::get('comment.content') === $content) {
        Message::error('comment-exist');
        ++$errors;
    } else {
        // Block user by IP address
        if (!empty($state['x']['ip'])) {
            $ip = Get::IP();
            foreach ($state['x']['ip'] as $v) {
                if ($ip === $v) {
                    Message::error('comment-i-p', $ip);
                    ++$errors;
                    break;
                }
            }
        }
        // Block user by UA keyword(s)
        if (!empty($state['x']['ua'])) {
            $ua = Get::UA();
            foreach ($state['x']['ua'] as $v) {
                if (stripos($ua, $v) !== false) {
                    Message::error('comment-u-a', $ua);
                    ++$errors;
                    break;
                }
            }
        }
        // Check for spam keyword(s) in comment
        if (!empty($state['x']['query'])) {
            $s = $author . $email . $link . $content;
            foreach ($state['x']['query'] as $v) {
                if (stripos($s, $v) !== false) {
                    Message::error('comment-query', $v);
                    ++$errors;
                    break;
                }
            }
        }
    }
    $t = time();
    $anchor = $state['anchor'];
    $directory = COMMENT . DS . $this[0] . DS . date('Y-m-d-H-i-s', $t);
    $x = $state['comment']['x'] ?? 'page';
    $file = $directory . '.' . $x;
    $this->status(200);
    if ($errors > 0) {
        Session::set('form', $form);
    } else {
        Session::let('form');
        $form['comment']['author'] = $author;
        $form['comment']['email'] = $email;
        $form['comment']['link'] = $link ?: false;
        $form['comment']['content'] = $content;
        foreach ((array) Comment::$data as $k => $v) {
            if (isset($form['comment'][$k]) && $form['comment'][$k] === $v) {
                unset($form['comment'][$k]);
            }
        }
        if (isset($form['comment:data']['parent']) && trim($form['comment:data']['parent']) !== "") {
            $form['comment:data']['parent'] = Date::from($form['comment:data']['parent'])->slug;
        }
        Page::set($form['comment'])->saveTo($file, 0600);
        if (!empty($form['comment:data'])) {
            foreach ($form['comment:data'] as $k => $v) {
                File::set($v)->saveTo($directory . DS . $k . '.data', 0600);
            }
        }
        Hook::fire('on.comment.set', [null, null], new File($file));
        Message::success('comment-create');
        Session::set('comment', $form['comment']);
        if ($x === 'draft') {
            Message::info('comment-save');
        } else {
            $this->kick(dirname($url->clean) . $url->query('&', ['parent' => false]) . '#' . sprintf($anchor[0], sprintf('%u', $t)));
        }
    }
    $this->kick(dirname($url->clean) . $url->query . '#' . $anchor[1]);
});