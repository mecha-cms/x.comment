<?php

namespace _\lot\x\comment\a {
    // Add comment reply link
    function reply($a, $page, $deep) {
        $state = \State::get('x.comment', true);
        if ($deep < ($state['page']['deep'] ?? 0)) {
            $id = $this->name;
            $a['reply'] = [
                0 => 'a',
                1 => \i('Reply'),
                2 => [
                    'class' => 'comment-link comment-reply:v',
                    'href' => $GLOBALS['url']->query('&', ['parent' => $id]) . '#' . $state['anchor'][1],
                    'id' => 'parent:' . $id,
                    'rel' => 'nofollow',
                    'title' => \To::text(i('Reply to %s', (string) $this->author))
                ]
            ];
        }
        return $a;
    }
    \Hook::set('comment.a', __NAMESPACE__ . "\\reply", 0);
}

namespace _\lot\x\comment {
    // Build link(s) from array
    function a(array $in, array $lot = [], $comment) {
        $out = [];
        foreach ($in as $v) {
            if (\is_array($v)) {
                $out[] = new \HTML(\array_replace(['a', "", []], $v));
            } else if (\is_callable($v)) {
                $out[] = \fire($v, $lot, $comment);
            } else {
                $out[] = $v;
            }
        }
        return $out;
    }
}
