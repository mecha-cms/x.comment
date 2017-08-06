<?php

// Set a new comment
$state = Extend::state('comment');
Route::set('%*%/' . $state['path'], function($path) use($language, $url, $state) {
    $page = PAGE . DS . $path;
    $comment = COMMENT . DS . $path;
    if (!Request::is('post') || !File::exist([
        $page . '.page',
        $page . '.archive'
    ])) {
        Guardian::kick($path);
    }
    $token = Request::post('token', false);
    $author = Request::post('author', false);
    $email = Request::post('email', false);
    $link = Request::post('link', false);
    $type = Request::post('type', $state['page']['type']);
    $status = Request::post('status', $state['page']['status']);
    $content = Request::post('content', false);
    if (!$token || Session::get(Guardian::$config['session']['token']) !== $token) {
        Message::error('comment_token');
    }
    if (!$author) {
        Message::error('comment_void_field', $language->comment_author);
    } else {
        $author = To::text($author);
        if (Is::gt($author, $state['max']['author'])) {
            Message::error('comment_max', $language->comment_author);
        } else if (Is::lt($author, $state['min']['author'])) {
            Message::error('comment_min', $language->comment_author);
        }
    }
    if (!$email) {
        Message::error('comment_void_field', $language->comment_email);
    } else if (!Is::email($email)) {
        Message::error('comment_pattern_field', $language->comment_email);
    } else if (Is::gt($email, $state['max']['email'])) {
        Message::error('comment_max', $language->comment_email);
    } else if (Is::lt($email, $state['min']['email'])) {
        Message::error('comment_min', $language->comment_email);
    }
    if ($link) {
        if (!Is::url($link)) {
            Message::error('comment_pattern_field', $language->comment_link);
        } else if (Is::gt($link, $state['max']['link'])) {
            Message::error('comment_max', $language->comment_link);
        } else if (Is::lt($link, $state['min']['link'])) {
            Message::error('comment_min', $language->comment_link);
        }
    }
    if (!$content) {
        Message::error('comment_void_field', $language->comment_content);
    } else {
        $content = To::text($content, HTML_WISE . ',img', true);
        if ($state['page']['type'] === 'HTML' && strpos($content, '</p>') === false) {
            // Replace new line with `<br>` tag
            $content = '<p>' . str_replace("\n", '<br>', To::text($content, HTML_WISE_I . ',img', true)) . '</p>';
        }
        // Permanently disable the `[[e]]` block(s) in comment
        if (Extend::exist('block')) {
            $u = Extend::state('block', 'union', [1 => [0 => ['[[', ']]', '/']]])[1][0];
            $content = str_replace([$u[0] . 'e' . $u[1], $u[0] . $u[2] . 'e' . $u[1]], "", $content);
        }
        // Temporarily disallow image(s) in comment to prevent XSS
        $content = preg_replace('#<img .*?>#i', '<!-- $0 -->', $content);
        if (Is::gt($content, $state['max']['content'])) {
            Message::error('comment_max', $language->comment_content);
        } else if (Is::lt($content, $state['min']['content'])) {
            Message::error('comment_min', $language->comment_content);
        }
    }
    // Check for duplicate comment
    if (Session::get('comment.content') === $content) {
        Message::error('comment_duplicate');
    } else {
        // Block user by IP address
        if (!empty($state['user_ip_x'])) {
            $ip = Get::ip();
            foreach ($state['user_ip_x'] as $v) {
                if ($ip === $v) {
                    Message::error('comment_user_ip_x', $ip);
                    break;
                }
            }
        }
        // Block user by UA keyword(s)
        if (!empty($state['user_agent_x'])) {
            $ua = Get::ua();
            foreach ($state['user_agent_x'] as $v) {
                if (stripos($ua, $v) !== false) {
                    Message::error('comment_user_agent_x', $ua);
                    break;
                }
            }
        }
        // Check for spam keyword(s) in comment
        if (!empty($state['query_x'])) {
            $s = $author . $email . $link . $content;
            foreach ($state['query_x'] as $v) {
                if (stripos($s, $v) !== false) {
                    Message::error('comment_query_x', $v);
                    break;
                }
            }
        }
    }
    $id = time();
    $anchor = $state['anchor'];
    $file = $comment . DS . date('Y-m-d-H-i-s', $id) . '.' . $state['page']['state'];
    Hook::fire('on.comment.set', [$file, null]);
    if (!Message::$x) {
        $data = [
            'author' => $author,
            'email' => $email,
            'link' => $link,
            'type' => $type,
            'status' => $status,
            'content' => $content
        ];
        Page::data($data)->saveTo($file, 0600);
        if ($s = Request::post('parent')) {
            File::write((new Date($s))->slug)->saveTo(Path::F($file) . DS . 'parent.data', 0600);
        }
        Message::success('comment_create');
        Session::set('comment', $data);
        if ($state['page']['state'] === 'draft') {
            Message::info('comment_save');
        } else {
            Guardian::kick(Path::D($url->current) . '#' . __replace__($anchor[0], ['id' => $id]));
        }
    } else {
        Request::save('post');
    }
    Guardian::kick(Path::D($url->current) . '#' . $anchor[1]);
});