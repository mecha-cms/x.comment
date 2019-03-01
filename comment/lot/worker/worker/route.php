<?php

// Set a new comment!
$state = Extend::state('comment');
Route::set('%*%/' . $state['path'], function($path) use($language, $url, $state) {
    $page = PAGE . DS . $path;
    $comment = COMMENT . DS . $path;
    if (!HTTP::is('post') || !File::exist([
        $page . '.page',
        $page . '.archive'
    ])) {
        Message::error('comment_source');
    }
    extract(HTTP::post(), EXTR_SKIP);
    $type = $type ?? $state['comment']['type'] ?? null;
    $enter = Extend::exist('user') && Is::user();
    $status = $status ?? ($enter ? 1 : null);
    if (!$token || !Guardian::check($token, 'comment')) {
        Message::error('comment_token');
    }
    if (!$author) {
        Message::error('comment_void_field', $language->comment_author);
    } else {
        $author = strpos($author, '@') !== 0 ? To::text($author) : $author;
        if (gt($author, $state['max']['author'] ?? 0)) {
            Message::error('comment_max', $language->comment_author);
        } else if (lt($author, $state['min']['author'] ?? 0)) {
            Message::error('comment_min', $language->comment_author);
        }
    }
    if (!$enter) {
        if (!$email) {
            Message::error('comment_void_field', $language->comment_email);
        } else if (!Is::eMail($email)) {
            Message::error('comment_pattern_field', $language->comment_email);
        } else if (gt($email, $state['max']['email'] ?? 0)) {
            Message::error('comment_max', $language->comment_email);
        } else if (lt($email, $state['min']['email'] ?? 0)) {
            Message::error('comment_min', $language->comment_email);
        }
    }
    if ($link) {
        if (!Is::URL($link)) {
            Message::error('comment_pattern_field', $language->comment_link);
        } else if (gt($link, $state['max']['link'] ?? 0)) {
            Message::error('comment_max', $language->comment_link);
        } else if (lt($link, $state['min']['link'] ?? 0)) {
            Message::error('comment_min', $language->comment_link);
        }
    }
    if (!$content) {
        Message::error('comment_void_field', $language->comment_content);
    } else {
        $content = To::text((string) $content, HTML_WISE . ',img', true);
        if ($type === 'HTML' && strpos($content, '</p>') === false) {
            // Replace new line with `<br>` tag
            $content = '<p>' . str_replace(["\n\n", "\n"], ['</p><p>', '<br>'], $content) . '</p>';
        }
        // Permanently disable the `[[e]]` block(s) in comment
        /*
        if (Extend::exist('block')) {
            $union = Extend::state('block', 'union')[1][0] ?? [];
            $content = str_replace([
                $union[0] . 'e' . $union[1], // `[[e]]`
                $union[0] . $union[2] . 'e' . $union[1] // `[[/e]]`
            ], "", $content);
        }
        */
        // Temporarily disallow image(s) in comment to prevent XSS
        $content = preg_replace('#<img .*?>#i', '<!-- $0 -->', $content);
        if (gt($content, $state['max']['content'] ?? 0)) {
            Message::error('comment_max', $language->comment_content);
        } else if (lt($content, $state['min']['content'] ?? 0)) {
            Message::error('comment_min', $language->comment_content);
        }
    }
    // Check for duplicate comment
    if (Session::get(Comment::session . '.content') === $content) {
        Message::error('comment_duplicate');
    } else {
        // Block user by IP address
        if (!empty($state['x']['ip'])) {
            $ip = Get::IP();
            foreach ($state['x']['ip'] as $v) {
                if ($ip === $v) {
                    Message::error('comment_ip', $ip);
                    break;
                }
            }
        }
        // Block user by UA keyword(s)
        if (!empty($state['x']['agent'])) {
            $ua = Get::UA();
            foreach ($state['x']['agent'] as $v) {
                if (stripos($ua, $v) !== false) {
                    Message::error('comment_agent', $ua);
                    break;
                }
            }
        }
        // Check for spam keyword(s) in comment
        if (!empty($state['x']['query'])) {
            $s = $author . $email . $link . $content;
            foreach ($state['x']['query'] as $v) {
                if (stripos($s, $v) !== false) {
                    Message::error('comment_query', $v);
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
    if (!Message::$x) {
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
        if ($parent = HTTP::post('parent', false)) {
            File::put((new Date($parent))->slug)->saveTo($directory . DS . 'parent.data', 0600);
        }
        Hook::fire('on.comment.set', [null], new File($file));
        Message::success('comment_create');
        Session::set(Comment::session, $data);
        Session::reset('form');
        if ($x === 'draft') {
            Message::info('comment_save');
        } else {
            Guardian::kick(dirname($url->current) . '#' . candy($anchor[0], ['id' => sprintf('%u', $t)]));
        }
    } else {
        Session::set('form', HTTP::post());
    }
    Guardian::kick(dirname($url->current) . $url->query . '#' . $anchor[1]);
});