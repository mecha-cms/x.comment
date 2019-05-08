<?php

// Set a new comment!
$state = Extend::state('comment');
Route::set('*/.comment', function($r, $k) {
    $page = PAGE . $this[0];
    $comment = COMMENT . $this[0];
    $errors = 0;
    if ($k !== 'POST' || !File::exist([
        $page . '.page',
        $page . '.archive'
    ])) {
        $this->message::error('comment-source');
        ++$errors;
    }
    extract($r, EXTR_SKIP);
    $type = $type ?? $state['comment']['type'] ?? null;
    $enter = Extend::exist('user') && Is::user();
    $status = $status ?? ($enter ? 1 : null);
    if (!isset($token) || !Guard::check($token, 'comment')) {
        $this->message::error('comment-token');
        ++$errors;
    }
    if (!isset($author) || trim($author) === "") {
        $this->message::error('comment-void-field', $this->language->commentAuthor);
        ++$errors;
    } else {
        $author = strpos($author, '@') !== 0 ? To::text($author) : $author;
        if (gt($author, $state['max']['author'] ?? 0)) {
            $this->message::error('comment-max', $this->language->commentAuthor);
            ++$errors;
        } else if (lt($author, $state['min']['author'] ?? 0)) {
            $this->message::error('comment-min', $this->language->commentAuthor);
            ++$errors;
        }
    }
    if (!$enter) {
        if (!isset($email) || trim($email) === "") {
            $this->message::error('comment-void-field', $this->language->commentMail);
            ++$errors;
        } else if (!Is::mail($email)) {
            $this->message::error('comment-pattern-field', $this->language->commentMail);
            ++$errors;
        } else if (gt($email, $state['max']['email'] ?? 0)) {
            $this->message::error('comment-max', $this->language->commentMail);
            ++$errors;
        } else if (lt($email, $state['min']['email'] ?? 0)) {
            $this->message::error('comment-min', $this->language->commentMail);
            ++$errors;
        }
    }
    if ($link) {
        if (!Is::URL($link)) {
            $this->message::error('comment-pattern-field', $this->language->commentLink);
            ++$errors;
        } else if (gt($link, $state['max']['link'] ?? 0)) {
            $this->message::error('comment-max', $this->language->commentLink);
            ++$errors;
        } else if (lt($link, $state['min']['link'] ?? 0)) {
            $this->message::error('comment-min', $this->language->commentLink);
            ++$errors;
        }
    }
    if (!isset($content) || trim($content) === "") {
        $this->message::error('comment-void-field', $this->language->commentContent);
        ++$errors;
    } else {
        $content = To::text((string) $content, HTML_WISE . ',img', true);
        if ($type === 'HTML' && strpos($content, '</p>') === false) {
            // Replace new line with `<br>` and `<p>` tag(s)
            $content = '<p>' . str_replace(["\n\n", "\n"], ['</p><p>', '<br>'], $content) . '</p>';
        }
        // Permanently disable the `[[e]]` block(s) in comment
        if (Extend::exist('block')) {
            $e = Block::$config[0];
            $content = str_replace([
                $e[0] . 'e' . $e[1], // `[[e]]`
                $e[0] . $e[2] . 'e' . $e[1] // `[[/e]]`
            ], "", $content);
        }
        // Temporarily disallow image(s) in comment to prevent XSS
        $content = preg_replace('#<img(?:\s[^>]*)?>#i', '<!-- $0 -->', $content);
        if (gt($content, $state['max']['content'] ?? 0)) {
            $this->message::error('comment-max', $this->language->commentContent);
            ++$errors;
        } else if (lt($content, $state['min']['content'] ?? 0)) {
            $this->message::error('comment-min', $this->language->commentContent);
            ++$errors;
        }
    }
    // Check for duplicate comment
    if (Session::get('comment.content') === $content) {
        $this->message::error('comment-duplicate');
        ++$errors;
    } else {
        // Block user by IP address
        if (!empty($state['x']['ip'])) {
            $ip = Get::IP();
            foreach ($state['x']['ip'] as $v) {
                if ($ip === $v) {
                    $this->message::error('comment-i-p', $ip);
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
                    $this->message::error('comment-u-a', $ua);
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
                    $this->message::error('comment-query', $v);
                    ++$errors;
                    break;
                }
            }
        }
    }
    $t = time();
    $anchor = $state['anchor'];
    $directory = $comment . DS . date('Y-m-d-H-i-s', $t);
    $x = $state['comment']['x'] ?? 'page';
    $file = $directory . '.' . $x;
    $this->status(200);
    if ($errors > 0) {
        Session::set('form', $r);
    } else {
        $data = [
            'author' => $author ?: false,
            'email' => $email ?: false,
            'link' => $link ?: false,
            'type' => $type ?: false,
            'status' => $status ?? false,
            'content' => $content ?? false
        ];
        foreach ((array) Comment::$data as $k => $v) {
            if (isset($data[$k]) && $data[$k] === $v) {
                unset($data[$k]);
            }
        }
        Page::set($data)->saveTo($file, 0600);
        if (isset($parent) && trim($parent) !== "") {
            File::put((new Date($parent))->slug)->saveTo($directory . DS . 'parent.data', 0600);
        }
        Hook::fire('on.comment.set', [null], new File($file));
        $this->message::success('comment-create');
        Session::set('comment', $data);
        Session::let('form');
        if ($x === 'draft') {
            $this->message::info('comment-save');
        } else {
            $this->kick(dirname($this->url->clean) . $this->url->query('&', ['parent' => false]) . '#' . sprintf($anchor[0], sprintf('%u', $t)));
        }
    }
    $this->kick(dirname($this->url->clean) . $this->url->query . '#' . $anchor[1]);
});