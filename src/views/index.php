<?php

require_once __DIR__ . '/../../config/Config.php';
require_once __DIR__ . '/../../src/helper/helper.php';
require_once __DIR__ . '/../../src/auth/auth.php';

if (is_logged_in()) {
    redirect('/dashboard');
} else {
    redirect('/login');
}
