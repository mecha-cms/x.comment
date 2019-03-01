<?php

if (!is_dir($dir = LOT . DS . basename(__DIR__))) {
    mkdir($dir, 0755, true);
    header('Refresh: 0');
    exit;
} else if (!defined('DEBUG') || !DEBUG) {
    unlink(__FILE__);
}