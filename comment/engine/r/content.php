<?php

foreach (g(__DIR__ . DS . 'content', 'php') as $v) {
    Content::set(Path::N($v), $v);
}