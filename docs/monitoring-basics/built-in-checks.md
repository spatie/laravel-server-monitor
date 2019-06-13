---
title: Built in checks
weight: 2
---

This package comes with a few built in checks and notifications to get you started. Need more? [Write your own](https://docs.spatie.be/laravel-server-monitor/v1/monitoring-basics/writing-your-own-checks), it's easy!

### Diskspace

This check verifies the percentage of diskspace usage on the primary disk.

It executes this command on the server: `df -P .`.

If the reported diskpace is below 80% the check will succeed. If the diskpace usage is 80% or above a warning will be sent. If the reported diskpace is above 90% the check will be marked as failed.

### Elasticsearch

This check verifies if [Elasticsearch](https://www.elastic.co/) is running.

It executes this command on the server: `curl http://localhost:9200`.

If the output contains `lucene_version` the check will succeed, otherwise it will fail.

### Memcached

This check verifies if [Memcached](https://memcached.org/) is running.

It executes this command on the server: `service memcached status`.

If the output contains `memcached` the check will succeed, otherwise it will fail.

### MySQL

This check verifies if [MySQL](https://www.mysql.com/) is running.

It executes this command on the server: `ps -e | grep mysqld$`.

If the output contains `mysql` the check will succeed, otherwise it will fail.
