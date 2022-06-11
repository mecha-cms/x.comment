<?php

function comment(...$v) {
    return new Comment(...$v);
}

function comments(...$v) {
    return Comments::from(...$v);
}