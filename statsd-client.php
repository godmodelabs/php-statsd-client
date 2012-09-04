<?php

/**
 * PHP StatsD Client
 * 
 * sends statistics to StatsD over UDP
 * 
 * @author Julian Gruber <jgruber@boerse-go.de>
 */

class StatsD {
  
  private $host;
  private $timers;
  
  /**
   * The class constructor
   * 
   * @throws NoHostnameException
   * @param string $host StatsD's hostname
   */
  public function __construct($host) {
    if (!isset($host)) throw new Exception('StatsD hostname not provided (eg. `localhost`)');
    $this->host = $host;
  }

  /**
   * Log timing information
   *
   * @param string  $stat       The metric to log timing for
   * @param float   $time       The time to log
   * @param float|1 $sampleRate The rate (0..1) for sampling
   */
  public function timing($stat, $time, $sampleRate=1) {
    $this->send(array($stat => "$time|ms"), $sampleRate);
  }
  
  /**
   * More convenient timing function
   * Starts timer
   *
   * @param string $stat The metric to log timing for
   */
  public function start($stat) {
    $this->timers[$stat] = microtime(true);
  }

  /**
   * More convenient timing function
   * Stops timer and logs to StatsD
   *
   * @param string  $stat       The metric to log timing for
   * @param float|1 $sampleRate The rate (0..1) for sampling
   */
  public function stop($stat, $sampleRate=1) {
    $dt = microtime(true) - $this->timers[$stat];
    $dt *= 1000;
    $dt = round($dt);
    $this->timing($stat, $dt, $sampleRate);
  }

  /**
   * Log arbitrary values
   *
   * @param string  $stat       The metric to log values for
   * @param float   $value      The value to log
   * @param float|1 $sampleRate The rate (0..1) for sampling
   */
  public function gauge($stat, $value, $sampleRate=1) {
    $this->send(array($stat => "$value|g"), $sampleRate);
  }

  /**
   * Increment one or more counters
   *
   * @param string|array  $stats      The metric(s) to increment
   * @param float|1       $sampleRate The rate (0..1) for sampling
   */
  public function increment($stats, $sampleRate=1) {
    $this->updateStats($stats, 1, $sampleRate);
  }

  /**
   * Decrement one or more stats counters.
   *
   * @param string|array  $stats      The metric(s) to decrement
   * @param float|1       $sampleRate The rate (0..1) for sampling
   */
  public function decrement($stats, $sampleRate=1) {
    $this->updateStats($stats, -1, $sampleRate);
  }

  /**
   * Update one or more stats counters by arbitrary amounts.
   *
   * @param string|array  $stats      The metric(s) to update
   * @param int|1         $delta      The amount to increment/decrement each metric by
   * @param float|1       $sampleRate The rate (0..1) for sampling
   */
  public function updateStats($stats, $delta=1, $sampleRate=1) {
    if (!is_array($stats)) $stats = array($stats);
    $data = array();
    foreach($stats as $stat) $data[$stat] = "$delta|c";
    $this->send($data, $sampleRate);
  }

  /**
   * Transmit the metrics over UDP
   * 
   * @param array   $data       Data to transmit
   * @param float|1 $sampleRate The rate (0..1) for sampling
   */
  public function send($data, $sampleRate=1) {
    if ($sampleRate < 1) $data = StatsD::getSampledData($data, $sampleRate);
    if (empty($data)) return;
    try {
      $fp = fsockopen("udp://$this->host", 8125);
      if (!$fp) return;
      foreach ($data as $stat=>$value) fwrite($fp, "$stat:$value");
      fclose($fp);
    } catch(Exception $e) {};
  }

  /**
   * Throw out data based on $sampleRate
   * 
   * @internal
   * @param  array $data         Data to sample
   * @param  float $sampleRate   The rate (0..1) for sampling
   * @return array               Sampled data
   */
  private static function getSampledData($data, $sampleRate) {
    $sampledData = array();
    foreach ($data as $stat=>$value) {
      if (mt_rand(0, 1) <= $sampleRate) $sampledData[$stat] = "$value|@$sampleRate";
    }
    return $sampledData;
  }
}

?>