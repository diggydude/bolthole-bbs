<?php

  trait Singleton
  {

    protected static $_instance;

    public static function instance($params = null)
    {
	  if (!(self::$_instance instanceof self)) {
		self::$_instance = new self($params);
	  }
	  return self::$_instance;
    } // instance

    protected function __clone()
    {
    } // __clone

    protected function __wakeup()
    {
    } // __wakeup

  } // Singleton

?>