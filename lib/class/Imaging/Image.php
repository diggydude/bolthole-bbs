<?php

  require_once(dirname(__FILE__) . '/Color.php');

  class Image
  {

    protected $filename;
    protected $gdResource;
    protected $width;
    protected $height;
    protected $mimeType;

    public function __construct()
    {
      $this->filename   = null;
      $this->gdResource = null;
      $this->width      = null;
      $this->height     = null;
      $this->mimeType   = null;
    } // __construct

    public function __get($prop)
    {
      return (property_exists($this, $prop)) ? $this->$prop : null;
    } // __get

    public function __destruct()
    {
      @imagedestroy($this->gdResource);
    } // __destruct

    public static function fromFile($filename)
    {
      if (!file_exists($filename)) {
        throw new Exception('Image file "' . $filename . '" not found.');
      }
      $size  = getimagesize($filename);
      $image = new Image();
      $image->filename = $filename;
      $image->mimeType = $size["mime"];
      switch ($image->mimeType) {
        case "image/jpeg":
          $image->gdResource = imagecreatefromjpeg($image->filename);
          break;
        case "image/gif":
          $image->gdResource = imagecreatefromgif($image->filename);
          break;
        case "image/png":
          $image->gdResource = imagecreatefrompng($image->filename);
          break;
        default:
          break;
      }
      if (!is_resource($image->gdResource)) {
        throw new Exception('Failed creating Image from file "' . $image->filename . '".');
      }
      $image->width  = imagesx($image->gdResource);
      $image->height = imagesy($image->gdResource);
      return $image;
    } // fromFile

    public static function fromResource($resource)
    {
      if (!is_resource($resource)) {
        throw new Exception('Failed creating Image from resource.');
      }
      $image = new Image();
      $image->gdResource = $resource;
      $image->width  = imagesx($resource);
      $image->height = imagesy($resource);
      return $image;
    } // fromResource

    public static function create($width, $height)
    {
      $image= new Image();
      $image->gdResource = imagecreatetruecolor($width, $height);
      if (!is_resource($image->gdResource)) {
        $msg = "Failed creating Image of size " . $width . "x" . $height . ".";
        throw new Exception($msg);
      }
      $image->width  = $width;
      $image->height = $height;
      return $image;
    } // create

    public function setMimeType($type)
    {
      $this->mimeType = $type;
    } // setMimeType

    public function setFilename($filename)
    {
      $this->filename = $filename;
    } // setFilename

    public function copy()
    {
      $res = imagecreatetruecolor($this->width, $this->height);
      imagecopy($res, $this->gdResource, 0, 0, 0, 0, $this->width, $this->height);
      $img = Image::fromResource($res);
      $img->mimeType = $this->mimeType;
      return $img;
    } // copy

    public function resize($width, $height)
    {
      $res = imagecreatetruecolor($width, $height);
      imagecopyresampled($res, $this->gdResource, 0, 0, 0, 0, $width, $height, $this->width, $this->height);
      imagedestroy($this->gdResource);
      $this->gdResource = $res;
      $this->width  = imagesx($this->gdResource);
      $this->height = imagesy($this->gdResource);
    } // resize

    public function fit($maxWidth, $maxHeight)
    {
      if (($this->width / $this->height) > ($maxWidth / $maxHeight)) {
        $newWidth  = $maxWidth;
        $newHeight = $maxWidth * ($this->height / $this->width);
      }
      else {
        $newWidth  = $maxHeight * ($this->width / $this->height);
        $newHeight = $maxHeight;
      }
      $this->resize($newWidth, $newHeight);
    } // fit

    public function scale($percent = 200)
    {
      if ($percent == 100) {
        return;
      }
      $factor = $percent / 100;
      $width  = floor($this->width * $factor);
      $height = floor($this->height * $factor);
      $this->resize($width, $height);
    } // scale

    public function crop($left, $top, $width, $height)
    {
      $res = imagecreatetruecolor($width, $height);
      imagecopy($res, $this->gdResource, 0, 0, $left, $top, $width, $height);
      imagedestroy($this->gdResource);
      $this->gdResource = $res;
      $this->width  = imagesx($this->gdResource);
      $this->height = imagesy($this->gdResource);
    } // crop

    public function flip()
    {
      $rows = array();
      for ($i = 0; $i < $this->height; $i++) {
        $rows[$i] = array();
        for ($j = 0; $j < $this->width; $j++) {
          $rows[$i][$j] = imagecolorat($this->gdResource, $j, $i);
        }
      }
      $rows = array_reverse($rows);
      $res  = imagecreatetruecolor($this->width, $this->height);
      for ($i = 0; $i < $this->height; $i++) {
        for ($j = 0; $j < $this->width; $j++) {
          imagesetpixel($res, $j, $i, $rows[$i][$j]);
        }
      }
      imagedestroy($this->gdResource);
      $this->gdResource = $res;
    } // flip

    public function mirror()
    {
      $cols = array();
      for ($i = 0; $i < $this->width; $i++) {
        $cols[$i] = array();
        for ($j = 0; $j < $this->height; $j++) {
          $cols[$i][$j] = imagecolorat($this->gdResource, $i, $j);
        }
      }
      $cols = array_reverse($cols);
      $res  = imagecreatetruecolor($this->width, $this->height);
      for ($i = 0; $i < $this->width; $i++) {
        for ($j = 0; $j < $this->height; $j++) {
          imagesetpixel($res, $i, $j, $cols[$i][$j]);
        }
      }
      imagedestroy($this->gdResource);
      $this->gdResource = $res;
    } // mirror

    public function rotate($degrees)
    {
      $degrees = 360 - $degrees;
      if (($degrees <= 0) || ($degrees >= 360)) {
        return;
      }
      $res = imagerotate($this->gdResource, $degrees, 0);
      if ($res === false) {
        throw new Exception('Failure rotating image "' . $this->filename . '".');
      }
      imagedestroy($this->gdResource);
      $this->gdResource = $res;
      $this->width  = imagesx($this->gdResource);
      $this->height = imagesy($this->gdResource);
    } // rotate

    public function sharpen()
    {
      $matrix  = array(array(-1.2, -1, -1.2),
                       array(-1,   20, -1),
                       array(-1.2, -1, -1.2)
                      );
      $divisor = array_sum(array_map('array_sum', $matrix));
      $offset  = 0;
      imageconvolution($this->gdResource, $matrix, $divisor, $offset);
    } // sharpen

    public function blur($amount = 3)
    {
      for ($i = 0; $i < $amount; $i++) {
        imagefilter($this->gdResource, IMG_FILTER_SELECTIVE_BLUR);
      }
    } // blur

    public function gaussianBlur($amount = 3)
    {
      for ($i = 0; $i < $amount; $i++) {
        imagefilter($this->gdResource, IMG_FILTER_GAUSSIAN_BLUR);
      }
    } // gaussianBlur

    public function smooth($amount = 3)
    {
      imagefilter($this->gdResource, IMG_FILTER_SMOOTH, $amount);
    } // smooth

    public function pixelate($blockSize = 4)
    {
      imagefilter($this->gdResource, IMG_FILTER_PIXELATE, $blockSize);
    } // pixelate

    public function negative()
    {
      imagefilter($this->gdResource, IMG_FILTER_NEGATE);
    } // negative

    public function greyscale()
    {
      imagefilter($this->gdResource, IMG_FILTER_GRAYSCALE);
    } // greyscale

    public function hue($red, $green, $blue)
    {
      $rgb = $red + $green + $blue;
      $col = array($red / $rgb, $blue / $rgb, $green / $rgb);
      for ($i = 0; $i < $this->width; $i++) {
        for ($j = 0; $j < $this->height; $j++) {
          $color = new Color(imagecolorat($this->gdResource, $i, $j));
          $rgb   = $color->toRgb();
          $red   = ($rgb->red * $col[0]) + ($rgb->green * $col[1]) + ($rgb->blue * $col[2]);
          $green = ($rgb->red * $col[2]) + ($rgb->green * $col[0]) + ($rgb->blue * $col[1]);
          $blue  = ($rgb->red * $col[1]) + ($rgb->green * $col[2]) + ($rgb->blue * $col[0]);
          $color = Color::fromRgb($red, $green, $blue)->toDec();
          imagesetpixel($this->gdResource, $i, $j, $color);
        }
      }
    } // hue

    public function colorize($red, $green, $blue, $opacity = 100)
    {
      $this->greyscale();
      $opacity = floor(((100 - $opacity) * 127) / 100);
      @imagefilter($this->gdResource, IMG_FILTER_COLORIZE, $red, $green, $blue, $opacity);
    } // colorize

    public function replaceColor($sRed, $sGreen, $sBlue, $rRed, $rGreen, $rBlue)
    {
      $searchColor  = Color::fromRgb($sRed, $sGreen, $sBlue)->toDec();
      $replaceColor = Color::fromRgb($rRed, $rGreen, $rBlue)->toDec();
      for ($i = 0; $i < $this->height; $i++) {
        for ($j = 0; $j < $this->width; $j++) {
          if (imagecolorat($this->gdResource, $j, $i) == $searchColor) {
            imagesetpixel($this->gdResource, $j, $i, $replaceColor);
          }
        }
      }
    } // replaceColor

    public function colorAt($x, $y)
    {
      $color = new Color(imagecolorat($this->gdResource, $x, $y));
      $rgb   = $color->toRgb();
      return (object) array('red'   => $rgb->red,
                            'green' => $rgb->green,
                            'blue'  => $rgb->blue,
                            'hex'   => $color->toHex(),
                            'dec'   => $color->toDec()
                           );
    } // colorAt

    public function render()
    {
      switch ($this->mimeType) {
        case "image/jpeg":
          imagejpeg($this->gdResource);
          break;
        case "image/gif":
          imagegif($this->gdResource);
          break;
        case "image/png":
        default:
          imagepng($this->gdResource);
          break;
      }
    } // render

    public function capture()
    {
      ob_start();
      $this->render();
      return ob_get_clean();
    } // capture

    public function save($filename = "", $mimeType = "")
    {
      $filename = (strlen($filename) > 0)
                ? $filename
                : $this->filename;
      $mimeType = (strlen($mimeType) > 0)
                ? $mimeType
                : $this->mimeType;
      switch ($mimeType) {
        case "image/jpeg":
          if (imagejpeg($this->gdResource, $filename) === false) {
            throw new Exception('Failed saving "' . $filename . '" as JPEG.');
          }
          break;
        case "image/gif":
          if (imagegif($this->gdResource, $filename) === false) {
            throw new Exception('Failed saving "' . $filename . '" as GIF.');
          }
          break;
        case "image/png":
        default:
          if (imagepng($this->gdResource, $filename) === false) {
            throw new Exception('Failed saving "' . $filename . '" as PNG.');
          }
          break;
      }
    } // save

  } // Image

?>
