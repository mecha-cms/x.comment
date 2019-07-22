<?php

// Store comment state to registry…
$state = state('comment');
if (!empty($state['comment'])) {
    // Prioritize default state
    Config::over($state);
}