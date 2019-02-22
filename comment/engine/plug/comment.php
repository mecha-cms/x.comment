<?php

// Based on `.\lot\extend\page\engine\plug\page.php`
Comment::_('time', "fn\\page\\time");
Comment::_('update', "fn\\page\\update");

Comment::$data = Config::get('comment', true);