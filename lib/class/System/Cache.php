<?php

  class Cache
  {

    protected

      $directory,
      $ttl;

    public function __construct($params)
    {
      $this->directory = $params->directory;
      $this->ttl = (property_exists($params, 'ttl')) ? $params->ttl : 0;
    } // __construct

    public function exists($key)
    {
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
      return (time() < (filemtime($this->directory . "/" . $key) + $this->ttl));
    } // isFresh

    public function findKeys($pattern)
    {
      return glob($this->directory . "/" . $pattern);
    } // findKeys

    public function store($key, $content)
    {
      return @file_put_contents($this->directory . "/" . $key, serialize($content));
    } // store

    public function fetch($key)
    {
      if (!$this->exists($key)) {
        return false;
      }
      $content = @file_get_contents($this->directory . "/" . $key);
      return unserialize($content);
    } // fetch

    public function remove($key)
    {
      return @unlink($this->directory . "/" . $key);
    } // remove

    public function clear()
    {
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