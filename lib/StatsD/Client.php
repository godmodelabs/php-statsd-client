<?php

namespace StatsD;

/**
 * PHP StatsD Client
 * 
 * sends statistics to StatsD over UDP
 * 
 * @author Julian Gruber <julian@juliangruber.com>
 * @license MIT
 * @version 0.0.1
 * 
 * Copyright (c) 2012 Julian Gruber julian@juliangruber.com
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a
 * copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to permit
 * persons to whom the Software is furnished to do so, subject to the
 * following conditions:
 * 
 * The above copyright notice and this permission notice shall be included
 * in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS
 * OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN
 * NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM,
 * DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR
 * OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE
 * USE OR OTHER DEALINGS IN THE SOFTWARE.
 */

class Client {
  
  private $host;
  private $port;
  private $timers;
  
  /**
   * The class constructor
   * 
   * @constructor
   * @param string [$host='localhost'] StatsD's hostname
   */
  public function __construct($host='localhost', $port=8125) {
    if (strpos($host, ':') !== false) {
      $segs = explode(':', $host);
      $host = $segs[0];
      $port = $segs[1];
    }
    $this->host = $host;
    $this->port = $port;
  }

  /**
   * Log `$time` in milliseconds to `$stat`.
   *
   * @param string  $stat
   * @param float   $time
   * @param float   [$sampleRate]
   */
  public function timing($stat, $time, $sampleRate) {
    $this->send(array($stat => "$time|ms"), $sampleRate);
  }
  
  /**
   * More convenient timing function
   * Starts timer
   *
   * @param string $stat
   */
  public function start($stat) {
    $this->timers[$stat] = microtime(true);
  }

  /**
   * More convenient timing function
   * Stops timer and logs to StatsD
   *
   * @param string  $stat
   * @param float   [$sampleRate]
   */
  public function stop($stat, $sampleRate) {
    $dt = microtime(true) - $this->timers[$stat];
    $dt *= 1000;
    $dt = round($dt);
    $this->timing($stat, $dt, $sampleRate);
  }

  /**
   * Set the gauge at `$stat` to `$value`.
   *
   * @param string  $stat
   * @param float   $value
   * @param float   [$sampleRate]
   */
  public function gauge($stat, $value, $sampleRate) {
    $this->send(array($stat => "$value|g"), $sampleRate);
  }

  /**
   * Increment the counter(s) at `$stats` by 1.
   *
   * @param string|string[] $stats
   * @param float           [$sampleRate]
   */
  public function increment($stats, $sampleRate) {
    $this->updateStats($stats, 1, $sampleRate);
  }

  /**
   * Decrement the counter(s) at `$stats` by 1.
   *
   * @param string|string[] $stats
   * @param float           [$sampleRate]
   */
  public function decrement($stats, $sampleRate) {
    $this->updateStats($stats, -1, $sampleRate);
  }

  /**
   * Update one or more stats counters with arbitrary deltas.
   *
   * @param string|string[] $stats
   * @param int             [$delta=1]
   * @param float           [$sampleRate]
   */
  public function updateStats($stats, $delta=1, $sampleRate) {
    if (!is_array($stats)) $stats = array($stats);
    $data = array();
    foreach($stats as $stat) $data[$stat] = "$delta|c";
    $this->send($data, $sampleRate);
  }

  /**
   * Transmit the metrics in `$data` over UDP
   * 
   * @param string[]  $data
   * @param float     [$sampleRate=1]
   */
  public function send($data, $sampleRate=1) {
    if ($sampleRate < 1) $data = StatsD::getSampledData($data, $sampleRate);
    if (empty($data)) return;
    try {
      $fp = fsockopen("udp://$this->host", $this->port);
      if (!$fp) return;
      foreach ($data as $stat=>$value) fwrite($fp, "$stat:$value");
      fclose($fp);
    } catch(Exception $e) {};
  }

  /**
   * Throw out data based on `$sampleRate`
   * 
   * @internal
   * @param  string[] $data
   * @param  float    $sampleRate
   * @return string[]
   */
  private static function getSampledData($data, $sampleRate) {
    $sampledData = array();
    foreach ($data as $stat=>$value) {
      if (mt_rand(0, 1) <= $sampleRate) {
        $sampledData[$stat] = "$value|@$sampleRate";
      }
    }
    return $sampledData;
  }
}

?>
