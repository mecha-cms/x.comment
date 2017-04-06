<?php

function fn_comment_url($s) {
    global $url;
    $path = Path::F(To::path(Path::D($s)), COMMENT);
    $id = (new Date(Path::N($s)))->unix;
    return $url . '/' . To::url($path) . '#comment-' . $id;
}

Hook::set('comment.url', 'fn_comment_url');

function fn_comments_path($path, $id) {
    if ($id === 'comments' && !$path) {
        return Path::D(__DIR__) . DS . 'lot' . DS . 'worker' . DS . 'comments.php';
    }
    return $path;
}

Hook::set('shield.get.path', 'fn_comments_path');

function fn_comments_set($path = "", $step = 1) {
    global $site, $language;
    $comments = $files = [];
    if ($folder = Folder::exist(COMMENT . DS . $path)) {
        $files = glob($folder . DS . '*.page');
        foreach ($files as $v) {
            $comments[$v] = new Comment($v);
        }
        asort($comments);
    }
    Lot::set('comments', $comments);
    Hook::set('page.comments', function() use($files, $language) {
        $i = count($files);
        return (object) [
            'i' => $i,
            'text' => $i . ' ' . $language->{$i === 1 ? 'comment' : 'comments'}
        ];
    });
}

Route::hook(['%*%/%i%', '%*%'], 'fn_comments_set');

// Apply the block filter(s) of `page.content` to the `comment.content`
if (function_exists('fn_block_x')) Hook::set('comment.content', 'fn_block_x', 0);
if (function_exists('fn_block')) Hook::set('comment.content', 'fn_block', 1);

// Apply the Markdown filter of `page.title` to the `comment.title` (if any)
// Apply the Markdown filter of `page.content` to the `comment.content`
if (function_exists('fn_markdown_span')) Hook::set('comment.title', 'fn_markdown_span', 2);
if (function_exists('fn_markdown')) Hook::set(['comment.description', 'comment.content'], 'fn_markdown', 2);

// Apply the user filter(s) of `page.author` to the `comment.author`
if (function_exists('fn_user')) Hook::set('comment.author', 'fn_user', 1);

// Set a new comment
Route::set('%*%/-comment', function($path) use($language, $url) {
    $page = PAGE . DS . $path;
    $comment = COMMENT . DS . $path;
    $state = Extend::state('comment');
    if (!Request::is('post') || !File::exist([
        $page . '.page',
        $page . '.archive'
    ])) return;
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
    }
    if (!$email) {
        Message::error('comment_void_field', $language->comment_email);
    } else if (!Is::email($email)) {
        Message::error('comment_pattern_field', $language->comment_email);
    }
    if ($link && !Is::url($link)) {
        Message::error('comment_pattern_field', $language->comment_url);
    }
    if (!$content) {
        Message::error('comment_void_field', $language->comment_content);
    } else {
        $content = strip_tags($content, '<' . str_replace(',', '><', HTML_WISE) . '>');
    }
    $id = time();
    $file = $comment . DS . date('Y-m-d-H-i-s', $id) . '.' . $state['x'];
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
        if ($state['x'] === 'draft') {
            Message::info('comment_save');
        } else {
            Guardian::kick(Path::D($url->current) . '#comment-' . $id);
        }
    }
    Guardian::kick(Path::D($url->current) . '#form-comment');
});