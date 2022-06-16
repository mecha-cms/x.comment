<?php

namespace x\comment {
    // require __DIR__ . \D . 'engine' . \D . 'plug' . \D . 'comment.php'; (auto-loaded)
    require __DIR__ . \D . 'engine' . \D . 'plug' . \D . 'page.php';
    function asset($content) {
        if (!\class_exists("\\Asset")) {
            return $content;
        }
        \extract($GLOBALS, \EXTR_SKIP);
        if ($state->is('page')) {
            $z = \defined("\\TEST") && \TEST ? '.' : '.min.';
            \Asset::set(__DIR__ . \D . 'index' . $z . 'css', 10);
            \Asset::set(__DIR__ . \D . 'index' . $z . 'js', 10);
            $comments = $page->comments->count() ?? 0;
            $open = $page->state['x']['comment'] ?? $state->x->comment->page->x->state->comment ?? 1;
            \State::set([
                'can' => ['comment' => 1 === $open || true === $open],
                'has' => ['comments' => !!$comments]
            ]);
        }
        return $content;
    }
    // Need to set a priority before any asset(s) insertion task(s) because we use the `content` hook
    \Hook::set('content', __NAMESPACE__ . "\\asset", -1);
    // Extend user property to comment property
    if (isset($state->x->user)) {
        function email($email) {
            if ($email || 1 !== $this['status']) {
                return $email;
            }
            $user = $this['author'];
            if ($user && \is_string($user) && 0 === \strpos($user, '@')) {
                if (\is_file($user = \LOT . \D . 'user' . \D . \substr($user, 1) . '.page')) {
                    return (new \User($user))->email ?? $email;
                }
            }
            return $email;
        }
        function link($link) {
            if ($link || 1 !== $this['status']) {
                return $link;
            }
            $user = $this['author'];
            if ($user && \is_string($user) && 0 === \strpos($user, '@')) {
                if (\is_file($user = \LOT . \D . 'user' . \D . \substr($user, 1) . '.page')) {
                    $user = new \User($user);
                    return $user->link ?? $user->url ?? $link;
                }
            }
            return $link;
        }
        \Hook::set('comment.email', __NAMESPACE__ . "\\email", 0);
        \Hook::set('comment.link', __NAMESPACE__ . "\\link", 0);
    }
    if (\class_exists("\\Layout")) {
        $r = __DIR__ . \D . 'engine' . \D . 'y' . \D;
        !\Layout::path('comment') && \Layout::set('comment', $r . 'comment.php');
        !\Layout::path('comments') && \Layout::set('comments', $r . 'comments.php');
        !\Layout::path('form/comment') && \Layout::set('form/comment', $r . 'form' . \D . 'comment.php');
    }
    function hook($id, array $lot = [], $comment = null, $join = "") {
        $tasks = \Hook::fire($id, $lot, $comment);
        \array_shift($lot); // Remove the raw task(s)
        return \implode($join, \x\comment\tasks($tasks, $lot, $comment));
    }
    function tasks(array $in, array $lot = [], $comment = null) {
        $out = [];
        foreach ($in as $k => $v) {
            if (false === $v || null === $v) {
                continue;
            }
            if (\is_array($v)) {
                $out[$k] = new \HTML(\array_replace([false, "", []], $v));
            } else if (\is_callable($v)) {
                $out[$k] = \fire($v, $lot, $comment);
            } else {
                $out[$k] = $v;
            }
        }
        return $out;
    }
}

namespace x\comment\route {
    function set($content, $path) {
        \extract($GLOBALS, \EXTR_SKIP);
        $path = \trim($path ?? "", '/');
        $active = isset($state->x->user) && \Is::user();
        $guard = $state->x->comment->guard ?? [];
        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            \class_exists("\\Alert") && \Alert::error('Method not allowed.');
            \kick('/' . $path . $url->query . '#comment');
        }
        if (!\is_file(\LOT . \D . 'page' . \D . $path . '.page')) {
            \class_exists("\\Alert") && \Alert::error('You cannot write a comment here. This is usually due to the page data that is dynamically generated.');
            \kick('/' . $path . $url->query . '#comment');
        }
        $error = 0;
        $data_default = \array_replace_recursive(
            (array) \a($state->x->page->page ?? []),
            (array) \a($state->x->comment->page ?? [])
        );
        $data = \array_replace_recursive($data_default, (array) ($_POST['comment'] ?? []));
        if (empty($data['token']) || !\check($data['token'], 'comment')) {
            \class_exists("\\Alert") && \Alert::error('Invalid token.');
            \kick('/' . $path . $url->query . '#comment');
        }
        $data['status'] = $active ? 1 : 2; // Status data is hard-coded for security
        foreach (['author', 'email', 'link', 'content'] as $key) {
            if (!isset($data[$key])) {
                continue;
            }
            $title = \ucfirst($key);
            // Check for empty field(s)
            if (\Is::void($data[$key])) {
                if ('link' !== $key) { // `link` field is optional
                    \class_exists("\\Alert") && \Alert::error('Please fill out the %s field.', [$title]);
                    ++$error;
                }
            }
            // Check for field(s) value length
            if (isset($guard->max->{$key}) && \gt($data[$key], $guard->max->{$key})) {
                \class_exists("\\Alert") && \Alert::error('%s too long.', [$title]);
                ++$error;
            } else if (isset($guard->min->{$key}) && \lt($data[$key], $guard->min->{$key})) {
                if ('link' !== $key) { // `link` field is optional
                    \class_exists("\\Alert") && \Alert::error('%s too short.', [$title]);
                    ++$error;
                }
            }
        }
        // Sanitize comment author
        if (0 === $error && isset($data['author'])) {
            if (0 === \strpos((string) $data['author'], '@')) {
                $data['author'] = '@' . \To::kebab($data['author']);
            } else {
                $data['author'] = \To::text($data['author']);
            }
        }
        // Sanitize comment content
        if (0 === $error && isset($data['content'])) {
            // Temporarily disable PHP expression written in the comment body. Why? I don’t know!
            $data['content'] = \strtr($data['content'], [
                '<?php' => '&lt;?php',
                '<?=' => '&lt;?=',
                '?>' => '?&gt;'
            ]);
            // Temporarily disable the `[[e]]` block(s) written in the comment body
            if (isset($state->x->block) && false !== \strpos($data['content'], '[[/e]]')) {
                $data['content'] = \preg_replace_callback('/\[\[e\]\]([\s\S]*?)\[\[\/e\]\]/', static function($m) {
                    return '<pre><code>' . \htmlspecialchars($m[1]) . '</code></pre>';
                }, $data['content']);
            }
            // Implement default XSS filter to the comment with type of `HTML` or `text/html`
            if (!isset($data['type']) || 'HTML' === $data['type'] || 'text/html' === $data['type']) {
                $tags = 'a,abbr,b,br,cite,code,del,dfn,em,i,img,ins,kbd,mark,q,span,strong,sub,sup,time,u,var';
                $data['content'] = \strip_tags($data['content'], '<' . \strtr($tags, [',' => '><']) . '>');
                // Replace potential XSS via HTML attribute(s) into a `data-*` attribute(s)
                $data['content'] = \preg_replace_callback('/<(' . \strtr($tags, ',', '|') . ')(\s(?:[\p{L}\p{N}_:-]+=(?:"(?:[^"\\\]|\\\.)*"|\'(?:[^\'\\\]|\\\.)*\')|[^\/>])*?)?>/', function($m) {
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
                }, $data['content']);
                // Replace new line with `<br>` and `<p>` tag(s)
                $data['content'] = '<p>' . \strtr($data['content'], [
                    "\n\n" => '</p><p>',
                    "\n" => '<br>'
                ]) . '</p>';
            }
        }
        if (0 === $error && !$active) {
            // Check for email format
            if (!empty($data['email']) && !\Is::email($data['email'])) {
                \class_exists("\\Alert") && \Alert::error('Invalid %s format.', 'Email');
                ++$error;
            }
            // Check for link format
            if (!empty($data['link']) && !\Is::URL($data['link'])) {
                \class_exists("\\Alert") && \Alert::error('Invalid %s format.', 'Link');
                ++$error;
            }
        }
        // Check for duplicate comment
        if (isset($_SESSION['comment']['content']) && $data['content'] === $_SESSION['comment']['content']) {
            \class_exists("\\Alert") && \Alert::error('You have sent that comment already.');
            ++$error;
        }
        if ($error > 0) {
            $_SESSION['form']['comment'] = $data;
        // Store comment to file
        } else {
            unset($_SESSION['form']['comment']);
            $folder = \LOT . \D . 'comment' . \D . $path;
            if (!\is_dir($folder)) {
                \mkdir($folder, 0775, true);
            }
            $folder .= \D . \date('Y-m-d-H-i-s', $t = \time());
            $file = $folder . '.' . ($x = $state->x->comment->page->x ?? 'page');
            $values = [
                'author' => null,
                'email' => null,
                'link' => null,
                'status' => null,
                'type' => null,
                'content' => ""
            ];
            foreach ($data as $k => $v) {
                if (null === $v || !\array_key_exists($k, $values)) {
                    continue;
                }
                $values[$k] = $v;
            }
            foreach ($data_default as $k => $v) {
                if (isset($values[$k]) && $v === $values[$k]) {
                    unset($values[$k]);
                }
            }
            $values = \drop($values);
            \file_put_contents($file, \To::page($values));
            \chmod($file, 0600);
            if (isset($data['parent']) && !\Is::void($data['parent'])) {
                if (!\is_dir($folder)) {
                    \mkdir($folder, 0775, true);
                }
                \file_put_contents($parent = $folder . \D . 'parent.data', (new \Time($data['parent']))->name);
                \chmod($parent, 0600);
            }
            \class_exists("\\Alert") && \Alert::success('Comment created.');
            if ('draft' === $x) {
                \class_exists("\\Alert") && \Alert::info('Your comment will be visible once approved by the author.');
            }
            $_SESSION['comment'] = $values;
            \Hook::fire('on.comment.set', [$file]);
            if ('draft' !== $x) {
                \kick('/' . $path . $url->query(['parent' => null]) . '#comment:' . \sprintf('%u', $t));
            }
        }
        \kick('/' . $path . $url->query . '#comment');
    }
    $path = \trim($url->path ?? "", '/');
    $route = \trim($state->x->comment->route ?? 'comment', '/');
    // `/comment/article/lorem-ipsum`
    if (0 === \strpos($path, $route . '/')) {
        \Hook::set('route.page', function($content, $path, $query, $hash) use($route) {
            if ($path && \preg_match('/^\/' . \x($route) . '(\/.*)$/', $path, $m)) {
                return \Hook::fire('route.comment', [$content, $m[1], $query, $hash]);
            }
            return $content;
        }, 90);
        \Hook::set('route.comment', __NAMESPACE__ . "\\set", 100);
    // `/article/lorem-ipsum/comment/1`
    } else if (false !== \strpos($path . '/', '/' . $route . '/') && \preg_match('/\/' . \x($route) . '(?:\/([1-9]\d*))?$/', $path)) {
        \Hook::set('route.page', function($content, $path, $query, $hash) use($route) {
            if ($path && \preg_match('/^(.*?)\/' . \x($route) . '(?:\/([1-9]\d*))?$/', $path, $m)) {
                return \Hook::fire('route.page', [$content, $m[1], $query, $hash]);
            }
            return $content;
        }, 90);
    }
}

namespace x\comment\tasks {
    // Add comment reply link
    function reply($tasks, $page, $deep) {
        extract($GLOBALS, \EXTR_SKIP);
        $k = $page->state['x']['comment'] ?? $state->x->comment->page->state->x->comment ?? 1;
        if ($deep < ($state->x->comment->page->deep ?? 0) && (1 === $k || true === $k)) {
            $id = $this->name;
            $tasks['reply'] = [
                0 => 'a',
                1 => \i('Reply'),
                2 => [
                    'class' => 'js:reply',
                    'href' => $url->query([
                        'parent' => $id
                    ]) . '#comment',
                    'rel' => 'nofollow',
                    'title' => \To::text(i('Reply to %s', (string) $this->author))
                ]
            ];
        }
        return $tasks;
    }
    \Hook::set('comment-tasks', __NAMESPACE__ . "\\reply", 10);
}