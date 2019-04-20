<?php

  function getOptimumHashCost($target = null, $algorithm = PASSWORD_BCRYPT)
  {
    if ($target == null) {
      $target = 0.05;
    }
    $cost = 8;
    do {
      $cost++;
      $start = microtime(true);
      password_hash("test", $algorithm, ['cost' => $cost]);
      $end = microtime(true);
    } while (($end - $start) < $target);
    return $cost;
  } // getOptimumHashCost

?>