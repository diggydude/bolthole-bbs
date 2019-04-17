<?php

function countChars($input)
{
  $l = mb_strlen($input, 'UTF-8');
  $unique = array();
  for ($i = 0; $i < $l; $i++) {
    $char = mb_substr($input, $i, 1, 'UTF-8');
    if (!array_key_exists($char, $unique)) {
      $unique[$char] = 0;
    }
    $unique[$char]++;
  }
  return $unique;
} // countChars

?>