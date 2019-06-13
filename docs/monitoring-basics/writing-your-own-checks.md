---
title: Writing your own checks
weight: 4
---

Writing your own checks is very easy. Let's create a check that'll verify if `nginx` is running.

Let's take a look at how to manually verify if Nginx is running. The easiest way is to run `systemctl is-active nginx`. This command outputs `active` if Nginx is running.

<img src="../../images/nginx.jpg" class="screenshot -cli">

Let's create an automatic check using that command.

The first thing you must to do is create a class that extends from `Spatie\ServerMonitor\CheckDefinitions\CheckDefinition`.  Here's an example implementation.

```php
namespace App\MyChecks;

use Spatie\ServerMonitor\CheckDefinitions\CheckDefinition;
use Symfony\Component\Process\Process;

class Nginx extends CheckDefinition
{
    public $command = 'systemctl is-active nginx';

    public function resolve(Process $process)
    {
        if (trim($process->getOutput()) === 'active') {
            $this->check->succeed('is running');

            return;
        }

        $this->check->fail('is not running');
    }
}
```

Let's go over this code in detail. The command to be executed on the server is specified in the `$command` property of the class.

The `resolve` function that accepts an instance of `Symfony\Component\Process\Process`. The output of that `process` can be inspected using `$process->getOutput()`. If the output contains `active` we'll call `$this->check->succeeded` which will mark the check successful. If it does not contain that string `$this->check->fail` will be called and the check marked as failed. By default the package [sends you a notification](https://docs.spatie.be/laravel-server-monitor/v1/monitoring-basics/notifications-and-events) whenever a check fails. The string that is passed to `$this->check->failed` will be displayed in the notification.

After creating this class you must register your class in the config file.

```php
// config/server-monitor.php
'checks' => [
    ...
    'nginx' => App\MyChecks\Nginx::class,
],
```

### Determining when a check will run the next

If you scheduled `php artisan server-monitor:run-checks`, [like we recommended](https://docs.spatie.be/laravel-server-monitor/v1/installation-and-setup#scheduling), to run every minute a successful check will run again 10 minutes later. If it fails it'll be run again the next minute.
 
This behaviour is defined on the `Spatie\ServerMonitor\CheckDefinitions\CheckDefinition` class where all `CheckDefinitions` are extending from.
 
 ```php
 // in class Spatie\ServerMonitor\CheckDefinitions\CheckDefinition
 
 public function performNextRunInMinutes(): int
 {
     if ($this->check->hasStatus(CheckStatus::SUCCESS)) {
         return 10;
     }

     return 0;
 ```
 
You may override that function in your own check.

### Setting the timeout of a command

When executing a command on the server a timeout of 10 seconds will be used. If a command takes longer than that the check will be marked as failed.

This behaviour is defined in the `Spatie\ServerMonitor\CheckDefinitions\CheckDefinition` class from which all `CheckDefinitions` are extended.
 
```php
public function timeoutInSeconds(): int
{
    return 10;
}
```

Need a different timeout? Just override the `timeoutInSeconds` function in your own check.

### Handling failed commands

Whenever your command fails, e.g. because a connection to the host can't be made or your command is invalid, `handleFailedProcess` will be called.

This is the default implementation on `Spatie\ServerMonitor\CheckDefinitions\CheckDefinition`:

```php 
public function handleFailedProcess(Process $process)
{
    $this->check->failed("failed to run: {$process->getErrorOutput()}");
}
```

Again, if you which to customize this behaviour, you can override that function in your own check.

### Using custom properties

Both the check and the host can retrieve and store custom properties. These properties are stored as json in the `custom_properties` field in the `checks` and `hosts` tables.

Here's how to work with custom properties: 

```php
// a $model can be instance of `host` or `check`
$model->setCustomProperty('key', 'value');
$model->getCustomProperty('key'); // returns 'value'

$model->forgetCustomProperty('key');
$model->getCustomProperty('key'); // returns null
```

You can retrieve custom properties from your checks like this:

```php
public function handleFailedProcess(Process $process)
{
    ...

    $customValueStoredOnCheck = $this->check->getCustomProperty('key');
    
    $customValueStoredOnHost = $this->check->host->getCustomProperty('key');
    
    ...
}
```
