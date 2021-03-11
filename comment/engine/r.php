<?php

namespace x\comment\tasks {
    // Add comment reply link
    function reply($tasks, $page, $deep) {
        extract($GLOBALS, \EXTR_SKIP);
        $k = $page->get('state.comment') ?? $state->x->comment->page->state->comment ?? 1;
        if ($deep < ($state->x->comment->page->deep ?? 0) && (1 === $k || true === $k)) {
            $id = $this->name;
            $tasks['reply'] = [
                0 => 'a',
                1 => \i('Reply'),
                2 => [
                    'class' => 'js:reply',
                    'href' => $url->query('&', [
                        'parent' => $id
                    ]) . '#' . $state->x->comment->anchor[0],
                    'rel' => 'nofollow',
                    'title' => \To::text(i('Reply to %s', (string) $this->author))
                ]
            ];
        }
        return $tasks;
    }
    \Hook::set('comment-tasks', __NAMESPACE__ . "\\reply", 10);
}

namespace x\comment {
    function hook($id, array $lot = [], $comment = null, $join = "") {
        $tasks = \Hook::fire($id, $lot, $comment);
        \array_shift($lot); // Remove the raw task(s)
        return \implode($join, \x\comment\tasks($tasks, $lot, $comment));
    }
    function tasks(array $in, array $lot = [], $comment = null) {
        $out = [];
        foreach ($in as $k => $v) {
            if (null === $v || false === $v) {
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
