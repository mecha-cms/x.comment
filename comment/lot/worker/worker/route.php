<?php

// Set a new comment
Route::set('%*%/-comment', function($path) use($language, $url) {
    $page = PAGE . DS . $path;
    $comment = COMMENT . DS . $path;
    $state = Extend::state('comment');
    if (!Request::is('post') || !File::exist([
        $page . '.page',
        $page . '.archive'
    ])) {
        Shield::abort();
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
        $author = strip_tags($author);
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
        $content = strip_tags($content, '<' . str_replace(',', '><', HTML_WISE) . '><img>');
        // Temporarily disallow image(s) in comment to prevent XSS
        $content = preg_replace('#<img .*?>#i', '<!-- $0 -->', $content);
        if (Is::gt($content, $state['max']['content'])) {
            Message::error('comment_max', $language->comment_content);
        } else if (Is::lt($content, $state['min']['content'])) {
            Message::error('comment_min', $language->comment_content);
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
    $id = time();
    $file = $comment . DS . date('Y-m-d-H-i-s', $id) . '.' . $state['page']['state'];
    Hook::NS('on.comment.set', [$file]);
    if (!Message::$x) {
        Page::data([
            'author' => $author,
            'email' => $email,
            'link' => $link,
            'type' => $type,
            'status' => $status,
            'content' => $content
        ])->saveTo($file, 0600);
        if ($s = Request::post('parent', false)) {
            File::write((new Date($s))->slug)->saveTo(Path::F($file) . DS . 'parent.data', 0600);
        }
        Message::success('comment_create');
        if ($state['page']['state'] === 'draft') {
            Message::info('comment_save');
        } else {
            Guardian::kick(Path::D($url->current) . '#comment-' . $id);
        }
    } else {
        Request::save('post');
    }
    Guardian::kick(Path::D($url->current) . '#form-comment');
});