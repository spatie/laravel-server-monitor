---
title: Customizing notifications
weight: 3
---

This package leverages [Laravel's native notification capabilites](https://laravel.com/docs/5.4/notifications) to send out [notifications](https://docs.spatie.be/laravel-server-monitor/v1/monitoring-basics/notifications-and-events). 

```php
'notifications' => [
   Spatie\ServerMonitor\Notifications\Notifications\CheckSucceeded::class => [],
   Spatie\ServerMonitor\Notifications\Notifications\CheckRestored::class => ['slack'],
   Spatie\ServerMonitor\Notifications\Notifications\CheckWarning::class => ['slack'],
   Spatie\ServerMonitor\Notifications\Notifications\CheckFailed::class => ['slack'],
],
```

Notice that the config keys are fully qualified class names of the `Notification` classes. All notifications have support for `slack` and `mail` out of the box. If you want to add support for more channels or just want to change the text of the notifications you can specify your own notification classes in the config file. When creating custom notifications, it's  best to extend the default ones shipped with this package.
