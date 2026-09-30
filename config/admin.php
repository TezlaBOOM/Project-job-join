<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Allowed CIDRs for Admin Panel
    |--------------------------------------------------------------------------
    |
    | The IP addresses and CIDR ranges that are permitted to access the /admin
    | area. Access from outside these ranges MUST return a 404 response.
    |
    */
    'allowed_cidrs' => array_filter(array_map('trim', explode(',', env('ADMIN_ALLOWED_CIDRS', '127.0.0.1,::1,10.0.0.0/8,172.16.0.0/12,192.168.0.0/16')))),

    /*
    |--------------------------------------------------------------------------
    | Max 2FA / Login attempts before lockout
    |--------------------------------------------------------------------------
    */
    'max_attempts' => (int) env('ADMIN_MAX_LOGIN_ATTEMPTS', 5),

    /*
    |--------------------------------------------------------------------------
    | 2FA Code expiration in minutes
    |--------------------------------------------------------------------------
    */
    'code_expires_minutes' => (int) env('ADMIN_2FA_EXPIRES_MINUTES', 10),

    /*
    |--------------------------------------------------------------------------
    | Enable 2FA Authentication
    |--------------------------------------------------------------------------
    |
    | When disabled, staff members log in directly with their password.
    | Default is false (disabled by default per user request).
    |
    */
    '2fa_enabled' => (bool) env('ADMIN_2FA_ENABLED', false),
];
