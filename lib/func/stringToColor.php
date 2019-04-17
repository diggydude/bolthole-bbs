<?php

  function stringToColor($str)
  {
    $names = array(
               'white'    => '255,255,255',
               'black'    => '0,0,0',
               'gray'     => '128,128,128',
               'grey'     => '128,128,128',
               'charcoal' => '54,69,79',
               'silver'   => '192,192,192',
               'red'      => '255,0,0',
               'maroon'   => '128,0,0',
               'magenta'  => '255,0,255',
               'orange'   => '255,165,0',
               'yellow'   => '0,128,0',
               'green'    => '0,128,0',
               'lime'     => '0,255,0',
               'teal'     => '0,128,128',
               'aqua'     => '0,255,255',
               'olive'    => '128,128,0',
               'blue'     => '0,0,255',
               'navy'     => '0,0,128',
               'cyan'     => '0,255,255',
               'violet'   => '238,130,238',
               'purple'   => '128,0,128',
               'fuchsia'  => '255,0,255'
             );
	if (array_key_exists($str, $names)) {
      list($red, $green, $blue) = explode(",", $names[$str]);
      return (object) array(
               'red'   => $red,
               'green' => $green,
               'blue'  => $blue
             );
    }
    if (preg_match('/^rgb\((\d{1,3}),(\d{1,3}),(\d{1,3})\)$/', $str, $matches)) {
      return (object) array(
               'red'   => $matches[1],
               'green' => $matches[2],
               'blue'  => $matches[3]
             );
    }
    if (preg_match('/^#([a-fA-F0-9]{3,6})$/', $str, $matches)) {
      $color = intval(hexdec(ltrim($matches[0], "#")));
      return (object) array(
               'red'   => ($color >> 16) & 0xFF,
               'green' => ($color >> 8) & 0xFF,
               'blue'  => $color & 0xFF
             );

    }
	return false;
  } // stringToColor

?>