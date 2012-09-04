php-statsd-client
=================

PHP client for Etsy's StatsD

Usage
-----

```php
require('statsd-client.php');
$log = new StatsD('localhost');       // insert StatsD hostname

$log->timing('pageload', 123);        // in miliseconds
$log->timing('pageload', 123, 0.5);   // supports sampling

$log->start('pageload');
// do stuff
$log->stop('pageload', 0.4);          // utility function (with sampling)

$log->increment('visits');
$log->increment(['users', 'wins']);   // handles multiple stats at once
$log->decrement('users', 0.2);        // supports sampling as well

$log->gauge('cpu-usage', 30, 0.2);    // gauges with sampling work too
```