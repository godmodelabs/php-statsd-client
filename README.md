php-statsd-client
=================

PHP client for Etsy's StatsD

Usage
-----

```php
include_once 'lib/StatsD/Client.php'; 			// or have the SplClassLoader do this
$log = new \StatsD\Client('localhost:8125'); 	// insert StatsD hostname

$log->timing('pageload', 123);        			// in miliseconds
$log->timing('pageload', 123, 0.5);   			// supports sampling

$log->start('pageload');
// do stuff
$log->stop('pageload', 0.4);          			// utility function (with sampling)

$log->increment('visits');
$log->increment(['users', 'wins']);   			// handles multiple stats at once
$log->decrement('users', 0.2);        			// supports sampling as well

$log->gauge('cpu-usage', 30, 0.2);    			// gauges with sampling work too
```

API
---

If you specify a sampleRate (between 0 and 1) StatsD doesn't get hit on every
log event in order to reduce load but samples up the events that get through so the stats stay correct.

### new \StatsD\Client([$host='localhost'][, $port=8125])
Returns an instance of the StatsD client bound to `$host:$port`, from now on referred to as `log`. `$host` can also contain the port, like `graphite.local:8125`.

### $log->timing($stat, $time [, $sampleRate])
Log `$time` in milliseconds to `$stat`.

### $log->start($stat)
More convenient timing function: Starts timer

### $log->stop($stat [, $sampleRate])
More convenient timing function: Stops timer and logs to StatsD

### $log->increment($stats [, $sampleRate])
Increment the counter(s) at `$stats` by 1.

The parameter `$stats` can either a `string` or an `array`, in case you want to log the same data to different _stats_.

### $log->decrement($stats [, $sampleRate])
Decrement the counter(s) at `$stats` by 1.

The parameter `$stats` can either a `string` or an `array`, in case you want to log the same data to different _stats_.

### $log->gauge($stat, $value [, $sampleRate])
Set the gauge at `$stat` to `$value`.

License
-------
(MIT)

Copyright (c) 2012 Julian Gruber <julian@juliangruber.com>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the "Software"), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.