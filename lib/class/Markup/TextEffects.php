<?php

  require_once(__DIR__ . '/../../func/countChars.php');
  require_once(__DIR__ . '/../../func/randomString.php');
  require_once(__DIR__ . '/../../func/stringToColor.php');

  class TextEffects
  {

    public static function rainbow($text)
    {
      $chars = countChars($text);
      if (!array_key_exists(" ", $chars))  $chars[" "]  = 0;
      if (!array_key_exists("\n", $chars)) $chars["\n"] = 0;
      if (!array_key_exists("\r", $chars)) $chars["\r"] = 0;
      $length  = strlen($text) - $chars[" "] - $chars["\n"] - $chars["\r"];
      $palette = array();
      $result  = "";
      $freq    = M_PI * 2 / $length;
      for ($i = 0; $i < $length; ++$i) {
        $palette[] = (object) array(
                       'red'   => intval(sin($freq * $i + 2) * 127 + 128),
                       'green' => intval(sin($freq * $i + 0) * 127 + 128),
                       'blue'  => intval(sin($freq * $i + 4) * 127 + 128)
                     );
      }
      for ($i = 0; $i < strlen($text); $i++) {
        if (in_array($text{$i}, array(" ", "\n", "\r"))) {
          $result .= $text{$i};
        }
        else {
          $color   = array_shift($palette);
          $result .= "<span style=\"color: rgb(" . $color->red . "," . $color->green . "," . $color->blue . ");\">" . $text{$i} . "</span>";
        }
      }
      return $result;
    } // rainbow

    public static function gradient($text, $startColor, $endColor)
    {
      if (($startRgb = stringToColor($startColor)) === false) {
        return $text;
      }
      if (($endRgb = stringToColor($endColor)) === false) {
        return $text;
      }
      $chars = countChars($text);
      if (!array_key_exists(" ",  $chars)) $chars[" "]  = 0;
      if (!array_key_exists("\n", $chars)) $chars["\n"] = 0;
      if (!array_key_exists("\r", $chars)) $chars["\r"] = 0;
      $length  = strlen($text) - $chars[" "] - $chars["\n"] - $chars["\r"];
      $palette = array();
      $result  = "";
      for ($i = 0; $i < $length; $i++) {
        $palette[] = (object) array(
                       'red'   => (($endRgb->red   - $startRgb->red)   != 0) ? intval($startRgb->red   + ($endRgb->red   - $startRgb->red)   * ($i / $length)) : $startRgb->red,
                       'green' => (($endRgb->green - $startRgb->green) != 0) ? intval($startRgb->green + ($endRgb->green - $startRgb->green) * ($i / $length)) : $startRgb->green,
                       'blue'  => (($endRgb->blue  - $startRgb->blue)  != 0) ? intval($startRgb->blue  + ($endRgb->blue  - $startRgb->blue)  * ($i / $length)) : $startRgb->blue
                     );
      }
      for ($i = 0; $i < strlen($text); $i++) {
         if (in_array($text{$i}, array(" ", "\n", "\r"))) {
          $result .= $text{$i};
        }
        else {
          $color   = array_shift($palette);
          $result .= "<span style=\"color: rgb(" . $color->red . "," . $color->green . "," . $color->blue . ");\">" . $text{$i} . "</span>";
        }
      }
      return $result;
    } // gradient

    public static function spoiler($text)
    {
      return str_rot13($text);
    } // spoiler

  } // TextEffects

?>