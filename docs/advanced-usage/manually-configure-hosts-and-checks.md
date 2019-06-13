---
title: Manually modifying hosts and checks
weight: 1
---

All configured checks are stored in the `checks` table in the database. Every check is related to one host from the `hosts` table.  The various `server-monitor` commands manipulate the data these two tables:
 
 - `server-monitor:add-host` adds a host in the `hosts` table and creates checks in the `check` table related to that host.
 - `server-monitor:delete-host` deletes a host and all related checks
 - `server-monitor:list-hosts` lists all hosts
 - `server-monitor:list-checks` lists detailed information about all checks
 
You can also manually manipulate the rows of both tables. These fields can be manipulated in the `hosts` table:

- `name`: the name of the host that will be checked.
- `ssh_user`: the name of the ssh user the package should use when connecting to the remote server.
- `port`: the port that should be used when connecting to the server. If this is empty port 22 will be used.
- `ip`: if this field contains an ip-address we'll use that instead of the `name` when connecting to a server
- `custom_properties`: see the section on [using custom properties](https://docs.spatie.be/laravel-server-monitor/v1/monitoring-basics/writing-your-own-checks#using-custom-properties)
 
These are the fields you can manipulate in the `checks` table: 

- `host_id`: the `id` of the host in the `hosts` table on which this check will be performed.
- `type`: this value determines which check should be performed. The value should correspond to one of the keys in `checks` keys in the config file eg `diskspace`, `mysql`, ...
- `enabled`: if this contains `0` the check won't be executed.
- `custom_properties`: see the section on [using custom properties](https://docs.spatie.be/laravel-server-monitor/v1/monitoring-basics/writing-your-own-checks#using-custom-properties)
   
 All other fields in the `checks` and `hosts` tables are managed by the package and should not be manually modified.
 
