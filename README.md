**PACKAGE IN DEVELOPMENT, DO NOT USE YET**

# An easy to use powerful server monitor

[![Latest Version on Packagist](https://img.shields.io/packagist/v/spatie/laravel-server-monitor.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-server-monitor)
[![Build Status](https://img.shields.io/travis/spatie/laravel-server-monitor/master.svg?style=flat-square)](https://travis-ci.org/spatie/laravel-server-monitor)
[![SensioLabsInsight](https://img.shields.io/sensiolabs/i/f28c2e98-ba1f-468a-9a5c-7ef4fe41a78a.svg?style=flat-square)](https://insight.sensiolabs.com/projects/f28c2e98-ba1f-468a-9a5c-7ef4fe41a78a)
[![Quality Score](https://img.shields.io/scrutinizer/g/spatie/laravel-server-monitor.svg?style=flat-square)](https://scrutinizer-ci.com/g/spatie/laravel-server-monitor)
[![Total Downloads](https://img.shields.io/packagist/dt/spatie/laravel-server-monitor.svg?style=flat-square)](https://packagist.org/packages/spatie/laravel-server-monitor)

TO DO: add description

## Postcardware

You're free to use this package (it's [MIT-licensed](LICENSE.md)), but if it makes it to your production environment we highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using.

Our address is: Spatie, Samberstraat 69D, 2060 Antwerp, Belgium.

The best postcards will get published on the open source page on our website.

## Installation

You can install this package via composer using this command:

```bash
composer require spatie/laravel-server-monitor
```

Next, you must install the service provider:

```php
// config/app.php
'providers' => [
    ...
    Spatie\ServerMonitor\ServerMonitorServiceProvider::class,
];
```

You can publish the migrations with:
```bash
php artisan vendor:publish --provider="Spatie\ServerMonitor\ServerMonitorServiceProvider" --tag="migrations"
```

After the migration has been published you can create the `hosts`  and `checks `tables by running the migrations:

```bash
php artisan migrate
```

You must publish the config-file with:
```bash
php artisan vendor:publish --provider="Spatie\ServerMonitor\ServerMonitorServiceProvider" --tag="config"
```

This is the contents of the published config file:

```php
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
```

## Usage

Coming soon...

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email freek@spatie.be instead of using the issue tracker.

## Credits

- [Freek Van der Herten](https://github.com/freekmurze)
- [All Contributors](../../contributors)

## About Spatie

Spatie is a webdesign agency based in Antwerp, Belgium. You'll find an overview of all our open source projects [on our website](https://spatie.be/opensource).

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
