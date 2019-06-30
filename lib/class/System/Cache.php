<?php

  require_once(__DIR__ . '/Config.php');
  require_once(__DIR__ . '/../../func/wildcardCompare.php');

  class Cache
  {

    protected

      $directory,
      $ttl,
      $memcached;

    public function __construct($params)
    {
      $cnf             = Config::instance();
      $this->directory = $params->directory;
      $this->ttl       = (property_exists($params, 'ttl')) ? $params->ttl : 0;
      $this->memcached = null;
      if ($cnf->programs->memcached->enabled) {
        $this->memcached = new Memcached($cnf->programs->memcached->persistentId);
        $this->memcached->setOption(Memcached::OPT_LIBKETAMA_COMPATIBLE, true);
        if (Memcached::HAVE_IGBINARY) {
          $this->memcached->setOption(Memcached::OPT_SERIALIZER, Memcached::SERIALIZER_IGBINARY);
        }
        if (!count($this->memcached->getServerList())) {
          $this->memcached->addServers($cnf->programs->memcached->servers);
        }
        $this->directory = basename($this->directory);
      }
    } // __construct

    public function exists($key)
    {
      if ($this->memcached) {
        if ($this->memcached->getByKey($this->directory, $key) === false) {
          if ($this->memcached->getResultCode() == Memcached::RES_NOTFOUND) {
            return false;
          }
        }
        return true;
      }
      return (is_file($this->directory . "/" . $key));
    } // exists

    public function isFresh($key)
    {
      if (!$this->exists($key)) {
        return false;
      }
      if ($this->ttl == 0) {
        return true;
      }
      if ($this->memcached) {
        return true;
      }
      return (time() < (filemtime($this->directory . "/" . $key) + $this->ttl));
    } // isFresh

    public function findKeys($pattern)
    {
      if ($this->memcached) {
        $keys  = $this->memcached->getAllKeys();
        $found = array();
        foreach ($keys as $key) {
          if (wildcardCompare($pattern, $key) == 1) {
            $found[] = $key;
          }
        }
        return $found;
      }
      return glob($this->directory . "/" . $pattern);
    } // findKeys

    public function store($key, $content)
    {
      if ($this->memcached) {
        return $this->memcached->setByKey($this->directory, $key, $content, $this->ttl);
      }
      return @file_put_contents($this->directory . "/" . $key, serialize($content));
    } // store

    public function fetch($key)
    {
      if (!$this->exists($key)) {
        return false;
      }
      if ($this->memcached) {
        return $this->memcached->getByKey($this->directory, $key);
      }
      $content = @file_get_contents($this->directory . "/" . $key);
      return unserialize($content);
    } // fetch

    public function remove($key)
    {
      if ($this->memcached) {
        return $this->memcached->deleteByKey($this->directory, $key);
      }
      return @unlink($this->directory . "/" . $key);
    } // remove

    public function clear()
    {
      if ($this->memcached) {
        $this->memcached->flush();
        return;
      }
      foreach (readdir($this->directory) as $filename) {
        $file = $this->directory . "/" . $filename;
        if (is_file($file)) {
          @unlink($file);
        }
      }
    } // clear

    public function __set($key, $content)
    {
      $this->store($key, $content);
    } // __set

    public function __get($key)
    {
      return $this->fetch($key);
    } // __get

    public function __unset($key)
    {
      $this->remove($key);
    } // __unset

  } // Cache

?>
