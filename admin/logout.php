<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/includes/auth.php';

admin_logout();
session_boot();
flash('You have been signed out.', 'info');
redirect(url('admin/login.php'));
