---
title: High level overview
weight: 4
---

This package can perform health checks on all your servers. It does this by ssh'ing into them and performing certain commands. It'll interpret the output returned by the command to determine if the check failed or not.

Let's illustrate this with the `memcached` check provided out of the box. This verifies if [Memcached](https://memcached.org/) is running. The check runs `service memcached status` on your server and if it outputs a string that contains `memcached is running` the check will succeed. If not, the check will fail.

When a check fails, and on other events, the package can send you a notification. Notifications looks like this in Slack.
 
<img src="../images/check-failed.jpg" class="screenshot -slack">
 
You can specify which channels will send notifications [in the config file](https://docs.spatie.be/laravel-server-monitor/v1/installation-and-setup#basic-installation). By default the package has support for [Slack](https://slack.com/) and mail notifications. Because the package leverages Laravel's native notifications you can use any of the [community supported drivers](https://github.com/laravel-notification-channels) or [write your own](https://laravel.com/docs/5.4/notifications#custom-channels).
 
Hosts and checks can be added via the [`add-host` artisan command](https://docs.spatie.be/laravel-server-monitor/v1/monitoring-basics/managing-hosts#adding-hosts) or by manually [adding them](https://docs.spatie.be/laravel-server-monitor/v1/advanced-usage/manually-configure-hosts-and-checks) in the `hosts` and `checks` table.

This package comes with a few [built in checks](https://docs.spatie.be/laravel-server-monitor/v1/monitoring-basics/built-in-checks). But it's laughably easy to add your [own checks](https://docs.spatie.be/laravel-server-monitor/v1/monitoring-basics/writing-your-own-checks).
