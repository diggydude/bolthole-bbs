<?php

  // Posted on Stack Overflow by anmount
  // https://stackoverflow.com/questions/2305362/php-search-string-with-wildcards

function wildcardCompare($wild, $string) {
    $wild_i = 0;
    $string_i = 0;

    $wild_len = strlen($wild);
    $string_len = strlen($string);

    while (($string_i < $string_len) && ($wild[$wild_i] != '*')) {
        if (($wild[$wild_i] != $string[$string_i]) && ($wild[$wild_i] != '?')) {
            return 0;
        }
        $wild_i++;
        $string_i++;
    }

    $mp = 0;
    $cp = 0;

    while ($string_i < $string_len) {
        if ($wild[$wild_i] == '*') {
            if (++$wild_i == $wild_len) {
                return 1;
            }
            $mp = $wild_i;
            $cp = $string_i + 1;
        }
        else
        if (($wild[$wild_i] == $string[$string_i]) || ($wild[$wild_i] == '?')) {
            $wild_i++;
            $string_i++;
        }
        else {
            $wild_i = $mp;
            $string_i = $cp++;
        }
    }

    while ($wild[$wild_i] == '*') {
        $wild_i++;
    }

    return $wild_i == $wild_len ? 1 : 0;
}

?>
