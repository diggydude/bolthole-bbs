<?php

  function isInRange($number, $min, $max)
  {
    if (!is_int($number) && !is_float($number)) {
      return false;
    }
    if ($number < $min) {
      return false;
    }
    if ($number > $max) {
      return false;
    }
    return true;
  } // isInRange

?>