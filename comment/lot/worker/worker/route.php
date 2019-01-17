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
    $token = HTTP::post('token', false);
    $author = HTTP::post('author', false);
    $email = HTTP::post('email', false);
    $link = HTTP::post('link', false);
    $type = HTTP::post('type', $state['comment']['type']);
    $enter = Extend::exist('user') && Is::user();
    $status = HTTP::post('status', $enter ? 1 : false);
    $content = HTTP::post('content', false);
    if (!$token || !Guardian::check($token, 'comment')) {
        Message::error('comment_token');
    }
    if (!$author) {
        Message::error('comment_void_field', $language->comment_author);
    } else {
        $author = strpos($author, '@') !== 0 ? To::text($author) : $author;
        if (gt($author, $state['max']['author'])) {
            Message::error('comment_max', $language->comment_author);
        } else if (lt($author, $state['min']['author'])) {
            Message::error('comment_min', $language->comment_author);
        }
    }
    if (!$enter) {
        if (!$email) {
            Message::error('comment_void_field', $language->comment_email);
        } else if (!Is::eMail($email)) {
            Message::error('comment_pattern_field', $language->comment_email);
        } else if (gt($email, $state['max']['email'])) {
            Message::error('comment_max', $language->comment_email);
        } else if (lt($email, $state['min']['email'])) {
            Message::error('comment_min', $language->comment_email);
        }
    }
    if ($link) {
        if (!Is::URL($link)) {
            Message::error('comment_pattern_field', $language->comment_link);
        } else if (gt($link, $state['max']['link'])) {
            Message::error('comment_max', $language->comment_link);
        } else if (lt($link, $state['min']['link'])) {
            Message::error('comment_min', $language->comment_link);
        }
    }
    if (!$content) {
        Message::error('comment_void_field', $language->comment_content);
    } else {
        $content = To::text($content, HTML_WISE . ',img', true);
        if ($state['comment']['type'] === 'HTML' && strpos($content, '</p>') === false) {
            // Replace new line with `<br>` tag
            $content = '<p>' . str_replace(["\n\n", "\n"], ['</p><p>', '<br>'], $content) . '</p>';
        }
        // Permanently disable the `[[e]]` block(s) in comment
        if (Extend::exist('block')) {
            $union = Extend::state('block', 'union')[1][0] ?? [];
            $content = str_replace([
                $union[0] . 'e' . $union[1], // `[[e]]`
                $union[0] . $union[2] . 'e' . $union[1] // `[[/e]]`
            ], "", $content);
        }
        // Temporarily disallow image(s) in comment to prevent XSS
        $content = preg_replace('#<img .*?>#i', '<!-- $0 -->', $content);
        if (gt($content, $state['max']['content'])) {
            Message::error('comment_max', $language->comment_content);
        } else if (lt($content, $state['min']['content'])) {
            Message::error('comment_min', $language->comment_content);
        }
    }
    // Check for duplicate comment
    if (Session::get('comment.content') === $content) {
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
    $id = sprintf('%u', (string) time());
    $anchor = $state['anchor'];
    $directory = $comment . DS . date('Y-m-d-H-i-s', $id);
    $file = $directory . '.' . $state['comment']['state'];
    if (!Message::$x) {
        $data = [
            'author' => $author,
            'email' => $email,
            'link' => $link,
            'type' => $type,
            'status' => $status,
            'content' => $content
        ];
        foreach ((array) Config::get('comment', [], true) as $k => $v) {
            if (isset($data[$k]) && $data[$k] === $v) {
                unset($data[$k]);
            }
        }
        Page::set($data)->saveTo($file, 0600);
        if ($parent = HTTP::post('parent', "", false)) {
            File::put((new Date($parent))->slug)->saveTo($directory . DS . 'parent.data', 0600);
        }
        Hook::fire('on.comment.set', [null], new File($file));
        Message::success('comment_create');
        Session::set('comment', $data);
        if ($state['comment']['state'] === 'draft') {
            Message::info('comment_save');
        } else {
            Guardian::kick(Path::D($url->current) . '#' . candy($anchor[0], ['id' => $id]));
        }
    } else {
        Session::set(Form::session, HTTP::post());
    }
    Guardian::kick(Path::D($url->current) . $url->query . '#' . $anchor[1]);
});