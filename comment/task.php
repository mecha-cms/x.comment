<?php

// Create a `comment` folder in `lot` if it is not there
$f = LOT . DS . 'comment';
if (!Folder::exist($f)) {
    Folder::create($f, 0755);
    Guardian::kick($url->current);
// Self destruct!
} else {
    unlink(__FILE__);
}