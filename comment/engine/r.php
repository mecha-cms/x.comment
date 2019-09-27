<?php namespace _\lot\x\comment;

// Build link(s) from array
function links(array $in, array $lot = []) {
    $out = [];
    foreach ($in as $v) {
        if (\is_array($v)) {
            $out[] = new \HTML(\array_replace(['a', "", []], $v));
        } else if (\is_callable($v)) {
            $out[] = \fire($v, $lot, $this);
        } else {
            $out[] = $v;
        }
    }
    return $out;
}