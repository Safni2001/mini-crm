<?php

return [
    /*
    |--------------------------------------------------------------------------
    | File Upload Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure the file upload settings for the application.
    |
    */

    'company_logos' => [
        'disk' => env('UPLOAD_DISK', 'public'),
        'directory' => 'logos',
        'max_size_kb' => 2048, // 2MB
        'min_dimensions' => [
            'width' => 100,
            'height' => 100,
        ],
        'max_dimensions' => [
            'width' => 2000,
            'height' => 2000,
        ],
        'allowed_mimes' => ['jpeg', 'png', 'jpg', 'gif'],
        'optimize' => env('OPTIMIZE_IMAGES', true),
        'resize_max' => [
            'width' => 800,
            'height' => 800,
        ],
        'quality' => 85,
    ],

    /*
    |--------------------------------------------------------------------------
    | Storage URLs
    |--------------------------------------------------------------------------
    |
    | Configuration for generating public URLs for uploaded files.
    |
    */

    'url_generator' => [
        'default_disk' => env('UPLOAD_DISK', 'public'),
        'placeholder_logo' => '/images/placeholder-logo.png',
        'cache_urls' => env('CACHE_FILE_URLS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | File Cleanup
    |--------------------------------------------------------------------------
    |
    | Configuration for automatic file cleanup and maintenance.
    |
    */

    'cleanup' => [
        'delete_orphaned' => env('DELETE_ORPHANED_FILES', false),
        'cleanup_schedule' => 'daily',
        'retention_days' => 30,
    ],
];