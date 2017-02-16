<?php

return [

    /**
     * These are the checks that can be performed on your servers. You can add your own
     * checks. The only requirement is that they should extend the
     * `Spatie\ServerMonitor\Checks\CheckDefinitions\CheckDefinition` class.
     */
    'checks' => [
        'diskspace' => Spatie\ServerMonitor\CheckDefiniations\Diskspace::class,
        'beanstalkd' => Spatie\ServerMonitor\CheckDefiniations\Beanstald::class,
    ],

    /*
     * The performance of the package process can be increased by allowing a high number
     * of concurrent ssh connections. Set this to a lower value if you're
     * getting weird errors running the check.
     */
    'concurrent_ssh_connections' => 5,

    'notifications' => [

        'notifications' => [
            Spatie\ServerMonitor\CheckSucceeded::class => ['log'],
            Spatie\ServerMonitor\CheckWarning::class => ['log', 'slack'],
            Spatie\ServerMonitor\CheckFailed::class => ['log', 'slack'],
        ],

        'mail' => [
            'to' => 'your@email.com',
        ],

        'slack' => [
            'webhook_url' => env('SERVER_MONITOR_SLACK_WEBHOOK_URL'),
        ],

        /*
         * Here you can specify the notifiable to which the notifications should be sent. The default
         * notifiable will use the variables specified in this config file.
         */
        'notifiable' => \Spatie\ServerMonitor\Notifications\Notifiable::class,

        /*
         * The date format used in notifications.
         */
        'date_format' => 'd/m/Y',
    ],

    /*
     * To add or modify behaviour to the `Check` model you can specify your
     * own model here. The only requirement is that they should
     * extend the `Check` model provided by this package.
     */
    'check_model' => Spatie\ServerMonitor\Models\Check::class,
];