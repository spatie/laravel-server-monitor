---
title: Managing hosts
weight: 1
---



## Adding hosts

You can add hosts by running:

```bash
php artisan server-monitor:add-host
```

You'll be prompted for the name of your host, the ssh user and the port that should be used to connect to the server and which checks it should run.

<img src="../../images/add-host.jpg" class="screenshot -cli">

On most systems the authenticity of the host will be verified when connecting to it for the first time. To avoid problems while running the check we recommend manually opening up an ssh connection to the server you want to monitor to get past that check.

<img src="../../images/authenticity.jpg" class="screenshot -cli">

Although we don't recommend this, you could opt to [disable the host authenticity check](http://linuxcommando.blogspot.be/2008/10/how-to-disable-ssh-host-key-checking.html) altogether. Be aware that this will leave yourself open to man in the middle attacks. If you want to go ahead with this option add `-o UserKnownHostsFile=/dev/null -o StrictHostKeyChecking=no -q` to the `ssh_command_suffix` key in the `server-monitor` config file.

You can also prefix the SSH command. Just add your desired prefix to the `ssh_command_prefix` key in the config file.

## Deleting hosts

Deleting hosts is a simple as running

```bash
php artisan server-monitor:delete-host <host-name>
```

where `<host-name>` is the name of the host you wish to delete.

## Syncing from a file

If you have a large number of hosts that you wish to monitor using the `server-monitor:add-host` becomes tedious fast. Luckily there's also a command to bulk import hosts and check from a json file:

```
php artisan server-monitor:sync-file <path-to-file>
```

Here's an example of the structure that json file should have:

```json
[
  {
    "name": "my-site.com",
    "ssh_user": "forge",
    "ip": "1.2.3.4",
    "checks": [
      "diskspace", "mysql"
    ]
  },
  {
    "name": "another-site.be",
    "ssh_user": "forge",
    "checks": [
      "diskspace"
    ]
  }
]
```

## Manually modifying hosts and checks

Instead of using artisan commands you may opt to [manually configure](https://docs.spatie.be/laravel-server-monitor/v1/advanced-usage/manually-configure-hosts-and-checks) the hosts and checks in the database
