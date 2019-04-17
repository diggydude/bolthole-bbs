<?php

  class Color
  {

    protected $value;

    public function __construct($val)
    {
      $this->value = $val;
    } // __construct

    public static function fromHex($hex)
    {
      return new Color(intval(hexdec(ltrim($hex, "#"))));
    } // fromHex

    public static function fromRgb($r, $g, $b)
    {
      $r = intval($r);
      $g = intval($g);
      $b = intval($b);
      $r = dechex($r < 0 ? 0 : ($r > 255 ? 255 : $r));
      $g = dechex($g < 0 ? 0 : ($g > 255 ? 255 : $g));
      $b = dechex($b < 0 ? 0 : ($b > 255 ? 255 : $b));
      $hex  = (strlen($r) < 2 ? '0' : '') . $r;
      $hex .= (strlen($g) < 2 ? '0' : '') . $g;
      $hex .= (strlen($b) < 2 ? '0' : '') . $b;
      return self::fromHex($hex);
    } // fromRgb

    public function toHex()
    {
      return "#" . dechex($this->value);
    } // toHex

    public function toDec()
    {
      return $this->value;
    } // toDec

    public function toRgb()
    {
      return (object) array('red'   => ($this->value >> 16) & 0xFF,
                            'green' => ($this->value >> 8) & 0xFF,
                            'blue'  => $this->value & 0xFF
                           );
    } // toRgb

// There is no known reliable algorithm for this, and it's not needed anyway. - JElkins, 2/29/2012
/*
    public static function fromHsl($h, $s, $l)
    {
      if ($s == 0) {
        $r = $g = $b = $l * 255;
      }
      else {
        $varH = $h * 6;
        $vari = floor($varH);
        $var1 = $l * (1 - $s);
        $var2 = $l * (1 - $s * ($varH - $vari));
        $var3 = $l * (1 - $s * (1 - ( $varH - $vari)));
        if      ($vari == 0) {$varR = $l;    $varG = $var3; $varB = $var1;}
        else if ($vari == 1) {$varR = $var2; $varG = $l;    $varB = $var1;}
        else if ($vari == 2) {$varR = $var1; $varG = $l;    $varB = $var3;}
        else if ($vari == 3) {$varR = $var1; $varG = $var2; $varB = $l;}
        else if ($vari == 4) {$varR = $var3; $varG = $var1; $varB = $l;}
        else                 {$varR = $l;    $varG = $var1; $varB = $var2;}
        $r = $varR * 255;
        $g = $varG * 255;
        $b = $varB * 255;
      }
      return self::fromRgb($r, $g, $b);
    } // fromHsl

    public static function fromCmyk($c, $m, $y, $k)
    {
      $c  = $c / 100;
      $m  = $m / 100;
      $y  = $y / 100;
      $k  = $k / 100;
      $r  = 1 - ($c * (1 - $k)) - $k;
      $g  = 1 - ($m * (1 - $k)) - $k;
      $b  = 1 - ($y * (1 - $k)) - $k;
      return self::fromRgb($r, $g, $b);
    } // fromCmyk

    public function toHsl()
    {
      $rgb    = $this->toRgb();
      $r      = $rgb->red;
      $g      = $rgb->green;
      $b      = $rgb->blue;
      $min    = min($r, $g, $b);
      $max    = max($r, $g, $b);
      $delta  = $max - $min;
      $l      = $max / 255;
      if ($delta == 0) {
        $h = 0;
        $s = 0;
      }
      else {
        $s    = $delta / $max;
        $delR = ((($max - $r) / 6) + ($delta / 2)) / $delta;
        $delG = ((($max - $g) / 6) + ($delta / 2)) / $delta;
        $delB = ((($max - $b) / 6) + ($delta / 2)) / $delta;
        if ($r == $max){
          $h = $delB - $delG;
        }
        else if ($g == $max) {
          $h = (1 / 3) + $delR - $delB;
        }
        else if ($b == $max) {
          $h = (2 / 3) + $delG - $delR;
        }
        if ($h < 0) {
          $h++;
        }
        if ($h > 1) {
          $h--;
        }
      }
      return (object) array('hue'        => round($h * 360),
                            'saturation' => round($s * 100),
                            'luminance'  => round($l * 100)
                           );
    } // toHsl

    public function toCmyk()
    {
      $rgb = $this->toRgb();
      $r   = $rgb->red;
      $g   = $rgb->green;
      $b   = $rgb->blue;
      $c   = 1 - ($r / 255);
      $m   = 1 - ($g / 255);
      $y   = 1 - ($b / 255);
      $min = min($c, $m, $y);
      if ($min == 1) {
        return (object) array('cyan'    => 0,
                              'magenta' => 0,
                              'yellow'  => 0,
                              'black'   => 1
                             );
      }
      $k     = $min;
      $black = 1 - $k;
      return (object) array('cyan'    => ($c - $k) / $black,
                            'magenta' => ($m - $k) / $black,
                            'yellow'  => ($y - $k) / $black,
                            'black'   => $k
                           );
    } // toCmyk
*/
  } // Color

?>
