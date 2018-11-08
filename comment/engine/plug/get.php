<?php

// Based on `.\lot\extend\page\engine\plug\get.php`
Get::_('comments', ["fn\\get\\pages", [COMMENT, 'page', [-1, 'time'], null]]);