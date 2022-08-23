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
        !\Layout::path('comments') && \Layout::set('comments', __DIR__ . \D . 'engine' . \D . 'y' . \D . 'comments.php');
    }
}

namespace x\comment\route {
    function set($content, $path) {
        \extract($GLOBALS, \EXTR_SKIP);
        $path = \trim($path ?? "", '/');
        $active = isset($state->x->user) && \Is::user();
        $anchor = $state->x->comment->anchor ?? [];
        $guard = $state->x->comment->guard ?? [];
        if ('GET' === $_SERVER['REQUEST_METHOD']) {
            \class_exists("\\Alert") && \Alert::error('Method not allowed.');
            \kick('/' . $path . $url->query . '#' . $anchor[0]);
        }
        if (!\is_file(\LOT . \D . 'page' . \D . $path . '.page')) {
            \class_exists("\\Alert") && \Alert::error('You cannot write a comment here. This is usually due to the page data that is dynamically generated.');
            \kick('/' . $path . $url->query . '#' . $anchor[0]);
        }
        $error = 0;
        $data_default = \array_replace_recursive(
            (array) \a($state->x->page->page ?? []),
            (array) \a($state->x->comment->page ?? [])
        );
        $data = \array_replace_recursive($data_default, (array) ($_POST['comment'] ?? []));
        if (empty($data['token']) || !\check($data['token'], 'comment')) {
            \class_exists("\\Alert") && \Alert::error('Invalid token.');
            \kick('/' . $path . $url->query . '#' . $anchor[0]);
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
                \kick('/' . $path . $url->query(['parent' => null]) . '#' . \sprintf($anchor[2], \sprintf('%u', $t)));
            }
        }
        \kick('/' . $path . $url->query . '#' . $anchor[0]);
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

namespace x\comment\y {
    function comment(array $data) {
        \extract($data, \EXTR_SKIP);
        \extract($GLOBALS, \EXTR_SKIP);
        $out = [
            0 => 'article',
            1 => [
                'figure' => \x\comment\y\comment_figure($data),
                'header' => \x\comment\y\comment_header($data),
                'body' => \x\comment\y\comment_body($data),
                'form' => (1 === $type || true === $type) && $parent && $parent->name === $comment->name ? \x\comment\y\form($data) : null,
                'footer' => \x\comment\y\comment_footer($data),
                'comments' => null
            ],
            2 => [
                'class' => 'comment comment-status:' . $comment->status,
                'id' => 'comment:' . $comment->id
            ]
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
                $out[1]['comments'][1][] = \x\comment\y\comment(\array_replace($data, [
                    'comment' => $v,
                    'deep' => $deep + 1
                ]));
            }
        }
        return \Hook::fire('layout.comment', [$out, $data], $comment);
    }
    function comment_avatar(array $data) {
        return [
            0 => 'img',
            1 => false,
            2 => [
                'alt' => "",
                'class' => 'comment-avatar',
                'height' => 100,
                'src' => $data['avatar'],
                'width' => 100
            ]
        ];
    }
    function comment_body(array $data) {
        return [
            0 => 'div',
            1 => [
                'content' => \x\comment\y\comment_content($data)
            ],
            2 => [
                'class' => 'comment-body'
            ]
        ];
    }
    function comment_content(array $data) {
        \extract($data, \EXTR_SKIP);
        return [
            0 => 'div',
            1 => $comment->content,
            2 => [
                'class' => 'comment-content'
            ]
        ];
    }
    function comment_figure(array $data) {
        \extract($data, \EXTR_SKIP);
        if ($avatar = $comment->avatar(100)) {
            return [
                0 => 'figure',
                1 => [
                    'avatar' => \x\comment\y\comment_avatar(\array_replace($data, [
                        'avatar' => $avatar
                    ]))
                ],
                2 => [
                    'class' => 'comment-figure'
                ]
            ];
        }
        return [];
    }
    function comment_footer(array $data) {
        return [
            0 => 'footer',
            1 => [
                'tasks' => \x\comment\y\comment_tasks($data)
            ],
            2 => [
                'class' => 'comment-footer'
            ]
        ];
    }
    function comment_header(array $data) {
        \extract($data, \EXTR_SKIP);
        return [
            0 => 'header',
            1 => [
                'author' => [
                    0 => 'h4',
                    1 => [
                        'link' => [
                            0 => ($link = $comment->link) ? 'a' : 'span',
                            1 => $comment->author,
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
                ],
                'meta' => [
                    0 => 'p',
                    1 => [
                        'time' => [
                            0 => 'time',
                            1 => $comment->time('%A, %B %d, %Y %I:%M %p'),
                            2 => [
                                'class' => 'comment-time',
                                'datetime' => $comment->time->ISO8601
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
                ]
            ],
            2 => [
                'class' => 'comment-header'
            ]
        ];
    }
    function comment_tasks(array $data) {
        \extract($data, \EXTR_SKIP);
        \extract($GLOBALS, \EXTR_SKIP);
        $out = [
            0 => 'ul',
            1 => [
                'reply' => null
            ],
            2 => [
                'class' => 'comment-tasks'
            ]
        ];
        $k = $page->state['x']['comment'] ?? $state->x->comment->page->state->x->comment ?? 1;
        if ($deep < ($state->x->comment->page->deep ?? 0) && (1 === $k || true === $k)) {
            $id = $comment->name;
            $out[1]['reply'] = [
                0 => 'li',
                1 => [
                    'link' => [
                        0 => 'a',
                        1 => \i('Reply'),
                        2 => [
                            'class' => 'js:reply',
                            'href' => $url->query([
                                'parent' => $id
                            ]) . '#comment',
                            'rel' => 'nofollow',
                            'title' => \To::text(\i('Reply to %s', (string) $comment->author))
                        ]
                    ]
                ]
            ];
        }
        return $out;
    }
    function comments(array $data) {
        \extract($data, \EXTR_SKIP);
        \extract($GLOBALS, \EXTR_SKIP);
        return \Hook::fire('layout.comments', [[
            0 => 'section',
            1 => [
                'header' => \x\comment\y\comments_header($data),
                'body' => \x\comment\y\comments_body($data),
                'footer' => \x\comment\y\comments_footer($data)
            ],
            2 => [
                'class' => 'comments comments:' . $k
            ]
        ], $data], $page);
    }
    function comments_body(array $data) {
        return [
            0 => 'div',
            1 => [
                'content' => \x\comment\y\comments_content($data)
            ],
            2 => [
                'class' => 'comments-body'
            ]
        ];
    }
    function comments_content(array $data) {
        \extract($data, \EXTR_SKIP);
        $out = [
            0 => 'section',
            1 => [],
            2 => [
                'class' => 'comments',
                'data-level' => 0,
                'id' => 'comments'
            ]
        ];
        if ($count > 0) {
            foreach ($page->comments($chunk ?? $count, ($part ?? (int) \ceil($count / ($chunk ?? $count))) - 1) as $comment) {
                $out[1][] = \x\comment\y\comment(\array_replace($data, [
                    'comment' => $comment,
                    'deep' => 0
                ]));
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
        return $out;
    }
    function comments_footer(array $data) {
        \extract($data, \EXTR_SKIP);
        $pager = \x\comment\y\comments_pager($data);
        $tasks = \x\comment\y\comments_tasks($data);
        return [
            0 => 'footer',
            1 => [
                'pager' => !empty($pager[1]) ? $pager : null,
                'tasks' => !empty($tasks[1]) ? $tasks : null,
                'form' => $type && 2 !== $type ? ($parent ? null : \x\comment\y\form($data)) : [
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
        ];
    }
    function comments_header(array $data) {
        \extract($data, \EXTR_SKIP);
        return [
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
        ];
    }
    function comments_pager(array $data) {
        \extract($data, \EXTR_SKIP);
        \extract($GLOBALS, \EXTR_SKIP);
        if ($chunk && $count > $chunk) {
            return [
                0 => 'nav',
                1 => (static function($current, $count, $chunk, $peek, $fn, $first, $prev, $next, $last) {
                    $begin = 1;
                    $end = (int) \ceil($count / $chunk);
                    $out = [];
                    if ($end <= 1) {
                        return $out;
                    }
                    if ($current <= $peek + $peek) {
                        $min = $begin;
                        $max = \min($begin + $peek + $peek, $end);
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
                                        'aria-disabled' => $current === $begin ? 'true' : null,
                                        'href' => $current === $begin ? null : $fn($current - 1),
                                        'rel' => $current === $begin ? null : 'prev',
                                        'title' => \i('Go to the %s comments', [\l($prev)])
                                    ]
                                ]
                            ]
                        ];
                        $out[] = ' ';
                    }
                    if ($first && $last) {
                        $out['steps'] = [
                            0 => 'span',
                            1 => []
                        ];
                        if ($min > $begin) {
                            $out['steps'][1][] = [
                                0 => 'a',
                                1 => (string) $begin,
                                2 => [
                                    'href' => $fn($begin),
                                    'rel' => 'prev',
                                    'title' => \i('Go to the %s comment', [\l($first)])
                                ]
                            ];
                            if ($min > $begin + 1) {
                                $out['steps'][1][] = ' ';
                                $out['steps'][1][] = [
                                    0 => 'span',
                                    1 => '&#x2026;',
                                    2 => [
                                        'aria-hidden' => 'true'
                                    ]
                                ];
                            }
                        }
                        for ($i = $min; $i <= $max; ++$i) {
                            $out['steps'][1][] = ' ';
                            $out['steps'][1][] = [
                                0 => 'a',
                                1 => (string) $i,
                                2 => [
                                    'aria-current' => $current === $i ? 'page' : null,
                                    'href' => $current === $i ? null : $fn($i),
                                    'rel' => $current >= $i ? 'prev' : 'next',
                                    'title' => \i('Go to comments %d' . ($current === $i ? ' (you are here)' : ""), [$i])
                                ]
                            ];
                        }
                        if ($max < $end) {
                            if ($max < $end - 1) {
                                $out['steps'][1][] = ' ';
                                $out['steps'][1][] = [
                                    0 => 'span',
                                    1 => '&#x2026;',
                                    2 => [
                                        'aria-hidden' => 'true'
                                    ]
                                ];
                            }
                            $out['steps'][1][] = ' ';
                            $out['steps'][1][] = [
                                0 => 'a',
                                1 => (string) $end,
                                2 => [
                                    'href' => $fn($end),
                                    'rel' => 'next',
                                    'title' => \i('Go to the %s comments', [\l($last)])
                                ]
                            ];
                        }
                    }
                    if ($next) {
                        $out[] = ' ';
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
                                        'title' => \i('Go to the %s comments', [\l($next)])
                                    ]
                                ]
                            ]
                        ];
                    }
                    return $out;
                })($part, $count, $chunk, 2, static function($i) use($c, $max, $page, $url) {
                    return $page->url . ($max === $i ? "" : '/' . \trim($c['route'] ?? 'comment', '/') . '/' . $i) . $url->query([
                        'parent' => null
                    ]) . '#comments';
                }, \i('First'), \i('Previous'), \i('Next'), \i('Last')),
                2 => [
                    'class' => 'comments-pager'
                ]
            ];
        }
        if ($part > 1) {
            return [
                0 => 'p',
                1 => \i('No more %s to load.', ['comments']),
                2 => [
                    'role' => 'status'
                ]
            ];
        }
        return [];
    }
    function comments_tasks(array $data) {
        return [
            0 => 'ul',
            1 => [],
            2 => [
                'class' => 'comments-tasks'
            ]
        ];
    }
    function form(array $data) {
        \extract($data, \EXTR_SKIP);
        \extract($GLOBALS, \EXTR_SKIP);
        $guard = (object) ($state->x->comment->guard ?? []);
        return [
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
                                        'placeholder' => \S . \i('hello') . \S . '@' . \S . $url->host . \S,
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
                                        'placeholder' => \S . $url->protocol . \S . $url->host . \S,
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
                                        'href' => $url->current([
                                            'parent' => null
                                        ], 'comment'),
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
                    $url . '/' => $url . '/' . \trim($state->x->comment->route ?? 'comment', '/') . '/'
                ]) . $url->query([
                    'parent' => null
                ]),
                'class' => 'form-comment' . ($parent ? ' is:reply' : ""),
                'id' => 'comment',
                'method' => 'post',
                'name' => 'comment'
            ]
        ];
    }
}