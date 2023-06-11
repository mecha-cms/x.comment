<?php

namespace {
    function comment(...$lot) {
        return \Comment::from(...$lot);
    }
    function comments(...$lot) {
        return \Comments::from(...$lot);
    }
}

namespace x\comment {
    // Extend user property to comment property
    if (isset($state->x->user)) {
        function comment__email($email) {
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
        function comment__link($link) {
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
        \Hook::set('comment.email', __NAMESPACE__ . "\\comment__email", 0);
        \Hook::set('comment.link', __NAMESPACE__ . "\\comment__link", 0);
    }
    function content($content) {
        if (!\class_exists("\\Asset")) {
            return $content;
        }
        \extract($GLOBALS, \EXTR_SKIP);
        if (!$state->is('page')) {
            return $content;
        }
        $z = \defined("\\TEST") && \TEST ? '.' : '.min.';
        \Asset::set(__DIR__ . \D . 'index' . $z . 'css', 10);
        \Asset::set(__DIR__ . \D . 'index' . $z . 'js', 10);
        $comments = $page->comments ? $page->comments->count() : 0;
        $open = (int) ($page->state['x']['comment'] ?? $state->x->comment->page->x->state->comment ?? 1);
        \State::set([
            'can' => ['comment' => 1 === $open],
            'has' => ['comments' => !!$comments]
        ]);
        return $content;
    }
    // Set the comment state as quickly as possible, but as close as possible to the response body
    \Hook::set('content', __NAMESPACE__ . "\\content", -1);
    function route__comment($content, $path, $query) {
        \extract($GLOBALS, \EXTR_SKIP);
        $can_alert = \class_exists("\\Alert");
        $path = \trim($path ?? "", '/');
        $active = isset($state->x->user) && \Is::user();
        $guard = $state->x->comment->guard ?? [];
        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            $can_alert && \Alert::error('Method not allowed.');
            \kick('/' . $path . $query . '#comment');
        }
        if (!\is_file(\LOT . \D . 'page' . \D . $path . '.page')) {
            $can_alert && \Alert::error('You cannot write a comment here. This is usually due to the page data that is dynamically generated.');
            \kick('/' . $path . $query . '#comment');
        }
        $error = 0;
        $data_default = \array_replace_recursive(
            (array) \a($state->x->page->page ?? []),
            (array) \a($state->x->comment->page ?? [])
        );
        $data = \array_replace_recursive($data_default, (array) ($_POST['comment'] ?? []));
        if (empty($data['token']) || !\check($data['token'], 'comment')) {
            $can_alert && \Alert::error('Invalid token.');
            \kick('/' . $path . $query . '#comment');
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
                    $can_alert && \Alert::error('Please fill out the %s field.', [$title]);
                    ++$error;
                }
            }
            // Check for field(s) value length
            if (isset($guard->max->{$key}) && \gt($data[$key], $guard->max->{$key})) {
                $can_alert && \Alert::error('%s too long.', [$title]);
                ++$error;
            } else if (isset($guard->min->{$key}) && \lt($data[$key], $guard->min->{$key})) {
                if ('link' !== $key) { // `link` field is optional
                    $can_alert && \Alert::error('%s too short.', [$title]);
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
                $data['content'] = \preg_replace_callback('/\[\[e\]\]([\s\S]*?)\[\[\/e\]\]/', static function ($m) {
                    return '<pre><code>' . \htmlspecialchars($m[1]) . '</code></pre>';
                }, $data['content']);
            }
            // Implement default XSS filter to the comment with type of `HTML` or `text/html`
            if (!isset($data['type']) || 'HTML' === $data['type'] || 'text/html' === $data['type']) {
                $tags = 'a,abbr,b,br,cite,code,del,dfn,em,i,img,ins,kbd,mark,q,span,strong,sub,sup,time,u,var';
                $data['content'] = \strip_tags($data['content'], \explode(',', $tags));
                // Replace potential XSS via HTML attribute(s) into a `data-*` attribute(s)
                $data['content'] = \preg_replace_callback('/<(' . \strtr($tags, ',', '|') . ')(\s(?:"[^"]*"|\'[^\']*\'|[^>])*)?>/', static function ($m) {
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
                            '/=javascript:[^\s\/>]+/'
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
                $can_alert && \Alert::error('Invalid %s format.', 'Email');
                ++$error;
            }
            // Check for link format
            if (!empty($data['link']) && !\Is::URL($data['link'])) {
                $can_alert && \Alert::error('Invalid %s format.', 'Link');
                ++$error;
            }
        }
        // Check for duplicate comment
        if (isset($_SESSION['comment']['content']) && $data['content'] === $_SESSION['comment']['content']) {
            $can_alert && \Alert::error('You have sent that comment already.');
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
            $can_alert && \Alert::success('Comment created.');
            if ('draft' === $x) {
                $can_alert && \Alert::info('Your comment will be visible once approved by the author.');
            }
            $_SESSION['comment'] = $values;
            \Hook::fire('on.comment.set', [$file]);
            if ('draft' !== $x) {
                \kick('/' . $path . $url->query(['parent' => null]) . '#comment:' . \sprintf('%u', $t));
            }
        }
        \kick('/' . $path . $query . '#comment');
    }
    function route__page($content, $path, $query, $hash) {
        \extract($GLOBALS, \EXTR_SKIP);
        $path = \trim($path ?? $state->route ?? 'index', '/');
        $route = \trim($state->x->comment->route ?? 'comment', '/');
        // `/comment/article/lorem-ipsum`
        if (0 === \strpos($path, $route . '/')) {
            if (\preg_match('/^' . \x($route) . '(\/.*)$/', $path, $m)) {
                return \Hook::fire('route.comment', [$content, $m[1], $query, $hash]);
            }
            return $content;
        }
        // `/article/lorem-ipsum/comment/1`
        if (false !== \strpos($path . '/', '/' . $route . '/')) {
            if (\preg_match('/^(.*?)\/' . \x($route) . '(?:\/[1-9]\d*)$/', $path, $m)) {
                // Map route `/article/lorem-ipsum/comment/1` to route `/article/lorem-ipsum`. Pagination offset and comment
                // route will be ignored in this case because route `/article/lorem-ipsum/comment/123` is now an alias for
                // route `/article/lorem-ipsum/123`. Maintaining the pagination offset will give the impression that we are
                // going to page `123` which is not what we meant. The comment pagination offset will be taken care of
                // else-where using the current route value which now contains `/comment/123`.
                [$any, $path] = $m;
                $folder = \LOT . \D . 'page' . \D . \strtr($path, ['/' => \D]);
                $file = \exist([
                    $folder . '.archive',
                    $folder . '.page'
                ], 1);
                \State::set([
                    'is' => [
                        'error' => $file ? false : 404,
                        'page' => !!$file,
                        'pages' => false
                    ],
                    'has' => [
                        'page' => !!$file,
                        'pages' => false
                    ]
                ]);
                return \Hook::fire('route.page', [$content, '/' . $path, $query, $hash]);
            }
            return $content;
        }
        return $content;
    }
    \Hook::set('route.comment', __NAMESPACE__ . "\\route__comment", 100);
    \Hook::set('route.page', __NAMESPACE__ . "\\route__page", 90);
    function y__comment(array $lot) {
        \extract($lot, \EXTR_SKIP);
        $out = [
            0 => 'article',
            1 => [
                'figure' => \x\comment\y__comment_figure($lot) ?: null,
                'header' => \x\comment\y__comment_header($lot) ?: null,
                'body' => \x\comment\y__comment_body($lot) ?: null,
                'form' => (1 === $status || true === $status) && $parent && $parent->name === $comment->name ? \x\comment\y__form__comment($lot) : null,
                'footer' => \x\comment\y__comment_footer($lot) ?: null,
                'comments' => null
            ],
            2 => [
                'class' => 'comment comment-status:' . $comment->status,
                'id' => 'comment:' . $comment->id
            ],
            // These key(s) will be ignored by `HTML` class but can be used by other hook(s) as a reference.
            'level' => $deep + 1,
            'parent' => $parent ? $parent->name : null,
            'self' => $comment->name,
            'status' => $comment->status
        ];
        if ($deep < ($c['page']['deep'] ?? 0) && ($count = $comment->comments->count() ?? 0)) {
            $out[1]['comments'] = [
                0 => 'section',
                1 => [],
                2 => [
                    'class' => 'comments',
                    'data-level' => $deep + 1,
                    'id' => 'comments:' . $comment->id
                ]
            ];
            foreach ($comment->comments($count) as $v) {
                $out[1]['comments'][1][$v->path] = \x\comment\y__comment(\array_replace_recursive($lot, [
                    'comment' => $v,
                    'deep' => $deep + 1
                ])) ?: null;
            }
        }
        return \Hook::fire('y.comment', [$out, $lot], $comment);
    }
    function y__comment_author(array $lot) {
        \extract($lot, \EXTR_SKIP);
        return \Hook::fire('y.comment-author', [[
            0 => 'h4',
            1 => [
                'link' => [
                    0 => ($link = $comment->link) ? 'a' : 'span',
                    1 => (string) $comment->author,
                    2 => [
                        'class' => 'comment-link',
                        'href' => $link,
                        'rel' => $link ? 'nofollow' : null,
                        'target' => $link ? '_blank' : null
                    ]
                ]
            ],
            2 => [
                'class' => 'comment-author'
            ]
        ], $lot], $comment);
    }
    function y__comment_avatar(array $lot) {
        \extract($lot, \EXTR_SKIP);
        $avatar = $comment->avatar(100, 100, 100);
        return \Hook::fire('y.comment-avatar', [$avatar ? [
            0 => 'img',
            1 => false,
            2 => [
                'alt' => "",
                'class' => 'comment-avatar',
                'height' => 100,
                'src' => $avatar,
                'width' => 100
            ]
        ] : [], $lot], $comment);
    }
    function y__comment_body(array $lot) {
        \extract($lot, \EXTR_SKIP);
        return \Hook::fire('y.comment-body', [[
            0 => 'div',
            1 => [
                'content' => \x\comment\y__comment_content($lot) ?: null
            ],
            2 => [
                'class' => 'comment-body'
            ]
        ], $lot], $comment);
    }
    function y__comment_content(array $lot) {
        \extract($lot, \EXTR_SKIP);
        return \Hook::fire('y.comment-content', [[
            0 => 'div',
            1 => $comment->content,
            2 => [
                'class' => 'comment-content'
            ]
        ], $lot], $comment);
    }
    function y__comment_figure(array $lot) {
        \extract($lot, \EXTR_SKIP);
        $avatar = \x\comment\y__comment_avatar($lot) ?: null;
        return \Hook::fire('y.comment-figure', [$avatar ? [
            0 => 'figure',
            1 => [
                'avatar' => $avatar
            ],
            2 => [
                'class' => 'comment-figure'
            ]
        ] : [], $lot], $comment);
    }
    function y__comment_footer(array $lot) {
        \extract($lot, \EXTR_SKIP);
        return \Hook::fire('y.comment-footer', [[
            0 => 'footer',
            1 => [
                'tasks' => \x\comment\y__comment_tasks($lot) ?: null
            ],
            2 => [
                'class' => 'comment-footer'
            ]
        ], $lot], $comment);
    }
    function y__comment_header(array $lot) {
        \extract($lot, \EXTR_SKIP);
        return \Hook::fire('y.comment-header', [[
            0 => 'header',
            1 => [
                'author' => \x\comment\y__comment_author($lot) ?: null,
                'meta' => \x\comment\y__comment_meta($lot) ?: null
            ],
            2 => [
                'class' => 'comment-header'
            ]
        ], $lot], $comment);
    }
    function y__comment_meta(array $lot) {
        \extract($lot, \EXTR_SKIP);
        return \Hook::fire('y.comment-meta', [[
            0 => 'p',
            1 => [
                'time' => [
                    0 => 'time',
                    1 => $comment->time('%A, %B %d, %Y %I:%M %p'),
                    2 => [
                        'class' => 'comment-time',
                        'datetime' => $comment->time->format('c')
                    ]
                ],
                'space' => '&#x20;',
                'url' => [
                    0 => 'a',
                    1 => "",
                    2 => [
                        'class' => 'comment-url',
                        'href' => '#comment:' . $comment->id,
                        'rel' => 'nofollow'
                    ]
                ]
            ],
            2 => [
                'class' => 'comment-meta'
            ]
        ], $lot], $comment);
    }
    function y__comment_tasks(array $lot) {
        \extract($lot, \EXTR_SKIP);
        $out = [
            0 => 'ul',
            1 => [
                'reply' => null
            ],
            2 => [
                'class' => 'comment-tasks'
            ]
        ];
        if ($deep < ($state->x->comment->page->deep ?? 0) && (1 === $status || true === $status)) {
            $id = $comment->name;
            $out[1]['reply'] = [
                0 => 'li',
                1 => [
                    'link' => [
                        0 => 'a',
                        1 => \i('Reply'),
                        2 => [
                            'class' => 'js:reply',
                            'href' => \To::query(\array_replace($_GET, [
                                'parent' => $id
                            ])) . '#comment',
                            'rel' => 'nofollow',
                            'title' => \To::text(\i('Reply to %s', (string) $comment->author))
                        ]
                    ]
                ]
            ];
        }
        return \Hook::fire('y.comment-tasks', [$out, $lot], $comment);
    }
    function y__comments(array $lot) {
        \extract($lot, \EXTR_SKIP);
        return \Hook::fire('y.comments', [[
            0 => 'section',
            1 => [
                'header' => \x\comment\y__comments_header($lot) ?: null,
                'body' => \x\comment\y__comments_body($lot) ?: null,
                'footer' => \x\comment\y__comments_footer($lot) ?: null
            ],
            2 => [
                'class' => 'comments status:' . $k,
                'id' => 'comments'
            ]
        ], $lot], $page);
    }
    function y__comments_body(array $lot) {
        \extract($lot, \EXTR_SKIP);
        return \Hook::fire('y.comments-body', [[
            0 => 'div',
            1 => [
                'content' => \x\comment\y__comments_content($lot) ?: null
            ],
            2 => [
                'class' => 'comments-body'
            ]
        ], $lot], $page);
    }
    function y__comments_content(array $lot) {
        \extract($lot, \EXTR_SKIP);
        $out = [
            0 => 'section',
            1 => [],
            2 => [
                'class' => 'comments',
                'data-level' => 0,
                'id' => 'comments:' . $page->id
            ]
        ];
        if ($count > 0) {
            foreach ($page->comments($chunk ?? $count, ($part ?? (int) \ceil($count / ($chunk ?? $count))) - 1) as $comment) {
                $out[1][$comment->path] = \x\comment\y__comment(\array_replace_recursive($lot, [
                    'comment' => $comment,
                    'deep' => 0
                ])) ?: null;
            }
        } else {
            $out[1][] = [
                0 => 'p',
                1 => \i('No %s yet.', ['comments']),
                2 => [
                    'role' => 'status'
                ]
            ];
        }
        return \Hook::fire('y.comments-content', [$out, $lot], $page);
    }
    function y__comments_footer(array $lot) {
        \extract($lot, \EXTR_SKIP);
        $pager = \x\comment\y__comments_pager($lot) ?: null;
        $tasks = \x\comment\y__comments_tasks($lot) ?: null;
        return \Hook::fire('y.comments-footer', [[
            0 => 'footer',
            1 => [
                'pager' => !empty($pager[1]) ? $pager : null,
                'tasks' => !empty($tasks[1]) ? $tasks : null,
                'form' => $status && 2 !== $status ? ($parent ? null : (\x\comment\y__form__comment($lot) ?: null)) : [
                    0 => 'p',
                    1 => \i('%s are closed.', ['Comments']),
                    2 => [
                        'role' => 'status'
                    ]
                ]
            ],
            2 => [
                'class' => 'comments-footer'
            ]
        ], $lot], $page);
    }
    function y__comments_header(array $lot) {
        \extract($lot, \EXTR_SKIP);
        return \Hook::fire('y.comments-header', [[
            0 => 'header',
            1 => [
                'title' => [
                    0 => 'h3',
                    1 => $page->comments->title ?? null
                ]
            ],
            2 => [
                'class' => 'comments-header'
            ]
        ], $lot], $page);
    }
    function y__comments_pager(array $lot) {
        \extract($lot, \EXTR_SKIP);
        $out = [];
        if ($chunk && $count > $chunk) {
            $out = [
                0 => 'nav',
                1 => (static function ($current, $count, $chunk, $peek, $fn, $first, $prev, $next, $last) {
                    $start = 1;
                    $end = (int) \ceil($count / $chunk);
                    $out = [];
                    if ($end <= 1) {
                        return $out;
                    }
                    if ($current <= $peek + $peek) {
                        $min = $start;
                        $max = \min($start + $peek + $peek, $end);
                    } else if ($current > $end - $peek - $peek) {
                        $min = $end - $peek - $peek;
                        $max = $end;
                    } else {
                        $min = $current - $peek;
                        $max = $current + $peek;
                    }
                    if ($prev) {
                        $out['prev'] = [
                            0 => 'span',
                            1 => [
                                0 => [
                                    0 => 'a',
                                    1 => $prev,
                                    2 => [
                                        'aria-disabled' => $current === $start ? 'true' : null,
                                        'href' => $current === $start ? null : $fn($current - 1),
                                        'rel' => $current === $start ? null : 'prev',
                                        'title' => \i('Go to the %s comments.', [\l($prev)])
                                    ]
                                ]
                            ]
                        ];
                    }
                    if ($first && $last) {
                        $out['data'] = [
                            0 => 'span',
                            1 => []
                        ];
                        if ($min > $start) {
                            $out['data'][1][$start] = [
                                0 => 'a',
                                1 => (string) $start,
                                2 => [
                                    'href' => $fn($start),
                                    'rel' => 'prev',
                                    'title' => \i('Go to the %s comments.', [\l($first)])
                                ]
                            ];
                            if ($min > $start + 1) {
                                $out['data'][1]['<'] = [
                                    0 => 'span',
                                    1 => '&#x2026;',
                                    2 => [
                                        'aria-hidden' => 'true'
                                    ]
                                ];
                            }
                        }
                        for ($i = $min; $i <= $max; ++$i) {
                            $out['data'][1][$i] = [
                                0 => 'a',
                                1 => (string) $i,
                                2 => [
                                    'aria-current' => $current === $i ? 'page' : null,
                                    'href' => $current === $i ? null : $fn($i),
                                    'rel' => $current >= $i ? 'prev' : 'next',
                                    'title' => \i('Go to comments %d.' . ($current === $i ? ' (you are here)' : ""), [$i])
                                ]
                            ];
                        }
                        if ($max < $end) {
                            if ($max < $end - 1) {
                                $out['data'][1]['>'] = [
                                    0 => 'span',
                                    1 => '&#x2026;',
                                    2 => [
                                        'aria-hidden' => 'true'
                                    ]
                                ];
                            }
                            $out['data'][1][$end] = [
                                0 => 'a',
                                1 => (string) $end,
                                2 => [
                                    'href' => $fn($end),
                                    'rel' => 'next',
                                    'title' => \i('Go to the %s comments.', [\l($last)])
                                ]
                            ];
                        }
                    }
                    if ($next) {
                        $out['next'] = [
                            0 => 'span',
                            1 => [
                                0 => [
                                    0 => 'a',
                                    1 => $next,
                                    2 => [
                                        'aria-disabled' => $current === $end ? 'true' : null,
                                        'href' => $current === $end ? null : $fn($current + 1),
                                        'rel' => $current === $end ? null : 'next',
                                        'title' => \i('Go to the %s comments.', [\l($next)])
                                    ]
                                ]
                            ]
                        ];
                    }
                    return $out;
                })($part, $count, $chunk, 2, static function ($i) use ($c, $max, $page) {
                    return $page->url . ($max === $i ? "" : '/' . \trim($c['route'] ?? 'comment', '/') . '/' . $i) . \To::query(\array_replace($_GET, [
                        'parent' => null
                    ])) . '#comments';
                }, 'First', 'Previous', 'Next', 'Last'),
                2 => [
                    'class' => 'comments-pager'
                ]
            ];
        }
        if ($part > 1) {
            $out = [
                0 => 'p',
                1 => \i('No more %s to load.', ['comments']),
                2 => [
                    'role' => 'status'
                ]
            ];
        }
        return \Hook::fire('y.comments-pager', [$out, $lot], $page);
    }
    function y__comments_tasks(array $lot) {
        \extract($lot, \EXTR_SKIP);
        return \Hook::fire('y.comments-tasks', [[
            0 => 'ul',
            1 => [],
            2 => [
                'class' => 'comments-tasks'
            ]
        ], $lot], $page);
    }
    function y__form__comment(array $lot) {
        \extract($lot, \EXTR_SKIP);
        $guard = (object) ($state->x->comment->guard ?? []);
        $host = $_SERVER['HTTP_HOST'];
        $protocol = 'http' . (!empty($_SERVER['HTTPS']) && 'off' !== $_SERVER['HTTPS'] || 443 === ((int) $_SERVER['SERVER_PORT']) ? 's' : "") . '://';
        return \Hook::fire('y.form.comment', [[
            0 => 'form',
            1 => [
                'alert' => \class_exists("\\Layout") ? \Layout::alert() : null,
                'author' => [
                    0 => 'p',
                    1 => [
                        0 => [
                            0 => 'label',
                            1 => \i('Name'),
                            2 => [
                                'for' => $id = 'f:' . \substr(\uniqid(), 6)
                            ]
                        ],
                        1 => [
                            0 => 'br',
                            1 => false
                        ],
                        2 => [
                            0 => 'span',
                            1 => [
                                0 => [
                                    0 => 'input',
                                    1 => false,
                                    2 => [
                                        'id' => $id,
                                        'maxlength' => $guard->max->author ?? null,
                                        'minlength' => $guard->min->author ?? null,
                                        'name' => 'comment[author]',
                                        'placeholder' => \i('Anonymous'),
                                        'required' => true,
                                        'type' => 'text'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'email' => [
                    0 => 'p',
                    1 => [
                        0 => [
                            0 => 'label',
                            1 => \i('Email'),
                            2 => [
                                'for' => $id = 'f:' . \substr(\uniqid(), 6)
                            ]
                        ],
                        1 => [
                            0 => 'br',
                            1 => false
                        ],
                        2 => [
                            0 => 'span',
                            1 => [
                                0 => [
                                    0 => 'input',
                                    1 => false,
                                    2 => [
                                        'id' => $id,
                                        'maxlength' => $guard->max->email ?? null,
                                        'minlength' => $guard->min->email ?? null,
                                        'name' => 'comment[email]',
                                        'placeholder' => \S . \i('hello') . \S . '@' . \S . $host . \S,
                                        'required' => true,
                                        'type' => 'email'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'link' => [
                    0 => 'p',
                    1 => [
                        0 => [
                            0 => 'label',
                            1 => \i('Link'),
                            2 => [
                                'for' => $id = 'f:' . \substr(\uniqid(), 6)
                            ]
                        ],
                        1 => [
                            0 => 'br',
                            1 => false
                        ],
                        2 => [
                            0 => 'span',
                            1 => [
                                0 => [
                                    0 => 'input',
                                    1 => false,
                                    2 => [
                                        'id' => $id,
                                        'maxlength' => $guard->max->link ?? null,
                                        'minlength' => $guard->min->link ?? null,
                                        'name' => 'comment[link]',
                                        'placeholder' => \S . $protocol . \S . $host . \S,
                                        'type' => 'url'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'content' => [
                    0 => 'p',
                    1 => [
                        0 => [
                            0 => 'label',
                            1 => \i('Message'),
                            2 => [
                                'for' => $id = 'f:' . \substr(\uniqid(), 6)
                            ]
                        ],
                        1 => [
                            0 => 'br',
                            1 => false
                        ],
                        2 => [
                            0 => 'span',
                            1 => [
                                0 => [
                                    0 => 'textarea',
                                    1 => "",
                                    2 => [
                                        'id' => $id,
                                        'maxlength' => $guard->max->content ?? null,
                                        'minlength' => $guard->min->content ?? null,
                                        'name' => 'comment[content]',
                                        'placeholder' => $parent ? \To::text(\i('Reply to %s', (string) $parent->author)) : \i('Message goes here...'),
                                        'required' => true
                                    ]
                                ]
                            ]
                        ]
                    ]
                ],
                'tasks' => [
                    0 => 'p',
                    1 => [
                        0 => [
                            0 => 'label',
                            1 => \i('Tasks')
                        ],
                        1 => [
                            0 => 'br',
                            1 => false
                        ],
                        2 => [
                            0 => 'span',
                            1 => [
                                'publish' => [
                                    0 => 'button',
                                    1 => \i('Publish'),
                                    2 => [
                                        'id' => $id = 'f:' . \substr(\uniqid(), 6),
                                        'type' => 'submit',
                                        'value' => 1
                                    ]
                                ],
                                'cancel' => isset($c['page']['deep']) && $c['page']['deep'] > 0 ? [
                                    0 => 'a',
                                    1 => \i('Cancel'),
                                    2 => [
                                        'class' => 'js:cancel',
                                        'href' => \To::query(\array_replace($_GET, [
                                            'parent' => null
                                        ])) . '#comment',
                                        'role' => 'button'
                                    ]
                                ] : null
                            ],
                            2 => [
                                'role' => 'group'
                            ]
                        ]
                    ]
                ],
                'parent' => [
                    0 => 'input',
                    1 => false,
                    2 => [
                        'name' => 'comment[parent]',
                        'type' => 'hidden',
                        'value' => $parent ? $parent->name : null
                    ]
                ],
                'token' => [
                    0 => 'input',
                    1 => false,
                    2 => [
                        'name' => 'comment[token]',
                        'type' => 'hidden',
                        'value' => \token('comment')
                    ]
                ]
            ],
            2 => [
                'action' => \strtr($page->url, [
                    '://' . $host . '/' => '://' . $host . '/' . \trim($state->x->comment->route ?? 'comment', '/') . '/'
                ]) . \To::query($_GET, [
                    'parent' => null
                ]),
                'class' => 'form-comment' . ($parent ? ' is:reply' : ""),
                'id' => 'comment',
                'method' => 'post',
                'name' => 'comment'
            ]
        ], $lot], $page);
    }
    if (\class_exists("\\Layout")) {
        !\Layout::path('comments') && \Layout::set('comments', __DIR__ . \D . 'engine' . \D . 'y' . \D . 'comments.php');
    }
}