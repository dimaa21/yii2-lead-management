<?php

require_once __DIR__ . '/env.php';

return [
    'adminEmail' => 'admin@northline.test',
    'senderEmail' => 'noreply@northline.test',
    'senderName' => 'Northline',
    // Mixed into the remember-me cookie signature. There is no auth_key column:
    // the key is derived from the password hash, so a password change logs other devices out.
    'authKeySalt' => env('AUTH_KEY_SALT', 'northline-dev-auth-salt'),
    'leadPageSize' => 10,
];
