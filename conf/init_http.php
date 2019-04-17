<?php

  ini_set('display_errors', 1);
  require_once(__DIR__ . '/../lib/class/System/Config.php');
  $config = Config::instance(__DIR__ . '/config.conf');
  date_default_timezone_set($config->site->timezone);
  session_start();

?>