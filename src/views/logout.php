<?php

require_once __DIR__ . '/../../src/auth/auth.php';

logout_user();
session_start();   // start a fresh session so we can flash
set_flash('success', 'You have been signed out.');
redirect('/login');
