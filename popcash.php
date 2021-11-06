<?php

// PopCash Anti AdBlock PHP Script
// Release Date: 10 March 2021
// Version: 1.0.1

$UID     =  "267437"; // Your publisherID
$WID     =  "638462"; // Your domainID
$TOKEN   =  "27cff38eb813b4d41e14-1636160576-e74b6c460cfe3c0e27af267437"; // Token used in PopCash API
$OPTIONS = [
  "pop_fback" => "under", // values: 'under' or 'up'
  "pop_fcap"  => "1",  // How many ads we should show in a 24 hours time frame.
  "pop_delay" => "0", // popunder delay from the user click (in seconds) - Default is 0
];


class PopcashPublisherScript
{

  /**
   * cache
   *
   * @var array
   */
  protected $cache = [
    'enabled' => true,
    'host'    => 'localhost',
    'port'    => '11211',
    'key'     => 'ppch-h6IzF4iRLEdZV-QX82hhpzmvxX--',
  ];

  /**
   * endpoint
   *
   * @var string
   */
  public $endpoint = 'https://api-js.popcash.net/getCode?';

  /**
   * timeout
   *
   * @var int
   */
  public $timeout = 2;

  /**
   * expiration
   *
   * @var int
   */
  public $expiration = 10 * 60;

  /**
   * Constructor
   *
   * @param mixed $uid
   * @param mixed $wid
   * @param mixed $token
   * @param array $options
   */
  public function __construct($uid, $wid, $token, $options=[])
  {

    $this->uid           = $uid;
    $this->wid           = $wid;
    $this->token         = $token;
    $this->options       = $options;
    $this->cache['key'] .= "$uid-$wid";
  }

  /**
   * getCode
   *
   * @access public
   */
  public function getCode()
  {

    $code = $this->getCache()->get($this->cache['key']);

    if($this->cache['enabled'] && $code = $this->getCache()->get($this->cache['key'])) {
      return (object) ['response' =>$code, 'cacheStatus' => 1];
    }

    $userAgent = (isset($_SERVER['HTTP_USER_AGENT']) && !empty($_SERVER['HTTP_USER_AGENT']))
       ? $_SERVER['HTTP_USER_AGENT']
       : '';

    $curl = curl_init();

    curl_setopt_array($curl, [
      CURLOPT_URL            => $this->endpoint . "uid={$this->uid}&wid={$this->wid}&apikey={$this->token}&" . http_build_query($this->options),
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT        => $this->timeout,
      CURLOPT_USERAGENT      => $userAgent,
      CURLOPT_REFERER        => !empty($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : "pencarikatakunci.github.io",
    ]);

    $response = curl_exec($curl);

    $cacheStatus = $this->cache['enabled'];

    if ($this->cache['enabled'] && curl_getinfo($curl, CURLINFO_HTTP_CODE) == 200) {
      $cacheStatus = $this->getCache()->set($this->cache['key'], $response, $this->expiration);
    }

    return (object) [
      'response'    => $response,
      'cacheStatus' => $cacheStatus,
    ];
  }

  /**
   * fromCache
   *
   * @access public
   */
  public function getCache()
  {

    if (class_exists('Memcached')) {
      $memcached = new \Memcached();
      $memcached->addServer($this->cache['host'], $this->cache['port']);

      $index = $this->cache['host'] . ':' . $this->cache['port'];
      $st = $memcached->getStats();
      if (isset($st[$index]['pid']) && $st[$index]['pid'] > 0) {
        return $memcached;
      }
    }

    if (class_exists('Memcache')) {
      $memcache = new XMemcache();
      if (@$memcache->connect($this->cache['host'], $this->cache['port'])) {
        return $memcache;
      }
    }

    return new SimpleFile($this->expiration);
  }

  /**
   * getCacheKey
   *
   * @access public
   */
  public function getCacheKey()
  {

    return $this->cache['key'];
  }
}

if (class_exists('\Memcache')) {
  class XMemcache extends \Memcache
  {

    /**
     * set
     *
     * @param mixed $key
     * @param mixed $var
     * @param mixed $expire
     */
    public function set ($key, $var, $expire)
    {
      return parent::set($key, $var, 0, $expire);
    }

  }
}

class SimpleFile
{

  /**
   * expiration
   *
   * @var int
   */
  protected $expiration;

  /**
   * Constructor
   *
   * @param mixed $expiration
   */
  public function __construct($expiration)
  {
    $this->expiration = $expiration;
  }

  /**
   * set
   *
   * @param mixed $filename
   * @param mixed $content
   * @access public
   * @return void
   */
  function set($filename, $content)
  {

    try {
      $file = @fopen(sys_get_temp_dir() . "/$filename", 'w');
      if (!$file) {
        return false;
      }
      fwrite($file, $content);
      return fclose($file);
    } catch (\Exception $e) {

      return false;
    }
  }

  function get($filename)
  {
    try {
      if (!file_exists(sys_get_temp_dir() . "/$filename")) {
        return false;
      }
      $content = file_get_contents(sys_get_temp_dir() . "/$filename");
      if (!$content) {
        return false;
      }
      if (time() - filemtime(sys_get_temp_dir() . "/$filename") > $this->expiration) {
        unlink(sys_get_temp_dir() . "/$filename");
        return false;
      }
      return $content;
    } catch (\Exception $e) {
      return false;
    }
  }
}

$ps = new  PopcashPublisherScript($UID , $WID, $TOKEN, $OPTIONS);

echo "<script type='text/javascript'>";
echo $ps->getCode()->cacheStatus == 1 ? "// Cache Key: {$ps->getCacheKey()}\n\n" : "// no cache\n\n";
echo $ps->getCode()->response;
echo "</script>";

?>
