<?php namespace _\comment;

// Build tool(s) from array
function tools(array $in, array $lot = []) {
    $out = [];
    foreach ($in as $v) {
        if (\is_array($v)) {
            $a = new \HTML;
            $a[0] = $v[0] ?? 'a';
            $a[1] = $v[1] ?? "";
            $a[2] = $v[2] ?? [];
            $out[] = $a;
        } else if (\is_callable($v)) {
            $out[] = \fire($v, $lot, $this, \Content::class);
        } else {
            $out[] = $v;
        }
    }
    return $out;
}