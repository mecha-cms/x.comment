<?php

namespace _\lot\x\comment\tasks {
    // Add comment reply link
    function reply($tasks, $page, $deep) {
        $state = \State::get('x.comment', true);
        $k = $page->get('state.comment') ?? $state['page']['state']['comment'] ?? 1;
        if ($deep < ($state['page']['deep'] ?? 0) && (1 === $k || true === $k)) {
            $id = $this->name;
            $tasks['reply'] = [
                0 => 'a',
                1 => \i('Reply'),
                2 => [
                    'class' => 'comment-link js:reply',
                    'data-parent' => $id,
                    'href' => $GLOBALS['url']->query('&', ['parent' => $id]) . '#' . $state['anchor'][0],
                    'rel' => 'nofollow',
                    'title' => \To::text(i('Reply to %s', (string) $this->author))
                ]
            ];
        }
        return $tasks;
    }
    \Hook::set('comment:tasks', __NAMESPACE__ . "\\reply", 10);
}

namespace _\lot\x\comment {
    function layout($hook, array $lot = [], $comment = null, $join = "") {
        $tasks = \array_shift($lot);
        $tasks = \_\lot\x\comment\tasks((array) $tasks, $lot, $comment);
        \array_unshift($lot, $tasks);
        return implode($join, (array) \Hook::fire($hook, $lot, $comment));
    }
    function tasks(array $in, array $lot = [], $comment = null) {
        $out = [];
        foreach ($in as $v) {
            if (null === $v || false === $v) {
                continue;
            }
            if (\is_array($v)) {
                $out[] = new \HTML(\array_replace([false, "", []], $v));
            } else if (\is_callable($v)) {
                $out[] = \fire($v, $lot, $comment);
            } else {
                $out[] = $v;
            }
        }
        return $out;
    }
}
