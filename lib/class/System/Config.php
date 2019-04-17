<?php

  require_once(__DIR__ . '/../../trait/Singleton.php');

  class Config
  {

    use Singleton;

    protected

      $filename,
      $settings;

    protected function __construct($filename)
    {
      $this->settings = new stdClass();
      $this->filename = $filename;
      if (file_exists($this->filename)) {
        $this->settings = unserialize(file_get_contents($this->filename));
      }
    } // __construct

    public function save()
    {
      file_put_contents($this->filename, serialize($this->settings));
    } // save

    public function __set($prop, $val)
    {
      $this->settings->$prop = $val;
    } // __set

    public function __get($prop)
    {
      return (property_exists($this->settings, $prop)) ? $this->settings->$prop : null;
    } // __get

  } // Config

?>