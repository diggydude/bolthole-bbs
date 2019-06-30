<?php

  ini_set('display_errors', 0);
  require_once(__DIR__ . '/../lib/class/System/Config.php');
  $config = Config::instance('/etc/bolthole.conf');
  date_default_timezone_set($config->site->timezone);
  session_start();

?>
