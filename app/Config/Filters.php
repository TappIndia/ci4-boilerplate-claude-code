<?php

// app/Config/Filters.php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public array $aliases = [
        'csrf'        => \CodeIgniter\Filters\CSRF::class,
        'toolbar'     => \CodeIgniter\Filters\DebugToolbar::class,
        'honeypot'    => \CodeIgniter\Filters\Honeypot::class,
        'invalidchars'=> \CodeIgniter\Filters\InvalidChars::class,
        'secureheaders' => \CodeIgniter\Filters\SecureHeaders::class,

        // Custom filters
        'auth'        => \App\Filters\AuthFilter::class,
        'api_auth'    => \App\Filters\ApiAuthFilter::class,
        'log_activity'=> \App\Filters\ActivityLogFilter::class,
    ];

    public array $globals = [
        'before' => [
            'honeypot',
            // 'csrf',  // enable in production
        ],
        'after' => [
            'toolbar',
            'secureheaders',
        ],
    ];

    public array $methods = [];

    public array $filters = [
        'log_activity' => ['before' => ['admin/*', 'api/*']],
    ];
}
