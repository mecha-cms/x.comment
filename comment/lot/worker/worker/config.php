<?php

// Store comment state to registry…
$state = Extend::state('comment');
if (!empty($state['comment'])) {
    Config::alt(['comment' => $state['comment']]);
}