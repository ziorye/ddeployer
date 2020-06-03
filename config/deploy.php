<?php

return [
    /**
     * Gitlab's webhook secret token or Github's webhook secret
     */
    'secret_token' => env('SECRET_TOKEN', ''),

    /**
     * Commands to be executed for automatic deployment
     */
    'commands' => [
        'before' => [
            'php artisan down --message="Auto deployment in progress..."',
            'git fetch origin {$branch}',
            'git reset --hard origin/{$branch}',
        ],

        /**
         * If the changed code contains the file or path in the custom[key], the commands in the custom[value] will be executed
         * If custom[key] needs to specify multiple files or paths, separate them with commas
         */
        'custom' => [
            'composer.json,composer.lock' => ['composer install --no-ansi --no-interaction --no-dev --no-suggest --no-progress --prefer-dist'],
            'database/migrations' => ['php artisan migrate --force'],
        ],

        'after' => [
            /*
             * 'php artisan config:cache',
             * 'php artisan route:cache',
            */
            'php artisan up',
        ],
    ],

    /**
     * Check if [the branch you are on] matches [the branch you specified in the request parameter]
     */
    'extra_check' => false,
];
