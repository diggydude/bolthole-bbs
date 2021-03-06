<?php

  require_once(__DIR__ . '/../System/Config.php');
  require_once(__DIR__ . '/TextEffects.php');
  require_once(__DIR__ . '/../../vnd/geshi/geshi.php');
  require_once(__DIR__ . '/../../func/randomString.php');

  class MessageParser
  {

    protected static

      $codeBlocks = array();

    public static function parse($text, &$mentioned, $options = array())
    {
      $allowedTags       = (array_key_exists('allowedTags',       $options)) ? $options['allowedTags']       : array();
      $openLinksInNewTab = (array_key_exists('openLinksInNewTab', $options)) ? $options['openLinksInNewTab'] : true;
      $emoticonList      = (array_key_exists('emoticonList',      $options)) ? $options['emoticonList']      : array();
      $userList          = (array_key_exists('userList',          $options)) ? $options['userList']          : array();
      if (empty($allowedTags) || in_array('code', $allowedTags)) {
        $text = self::stashCodeBlocks($text);
      }
      if (empty($allowedTags) || in_array('ansi', $allowedTags)) {
        $text = self::stashAnsiArt($text);
      }
      $text = strip_tags($text);
      $text = self::parseEmoticons($text, $emoticonList);
      $text = self::parseMentions($text, $mentioned, $userList);
      $text = self::parseMarkup($text, $allowedTags, $openLinksInNewTab);
      $text = self::parseHashTags($text);
      $text = self::parseRawLinks($text, $openLinksInNewTab);
      $text = nl2br($text);
      $text = self::restoreCodeBlocks($text);
      return $text;
    } // parse

    public static function parseEmoticons($text, $emoticonList = array())
    {
      $cnf = Config::instance();
      if (preg_match_all('/(\:\w+\:)/', $text, $matches) < 1) {
        return $text;
      }
      $codes   = $matches[1];
      $search  = array();
      $replace = array();
      foreach ($codes as $code) {
        if (array_key_exists($code, $emoticonList)) {
          $search[]  = $code;
          $replace[] = "<img src=\"" . $cnf->files->emoticons->baseUri . "/" .  $emoticonList[$code] . "\" alt=\"" . $code . "\" />";
        }
      }
      return str_replace($search, $replace, $text);
    } // parseEmoticons

    public static function parseHashTags($text)
    {
      if (preg_match_all('/(#\w+)/', $text, $matches) < 1) {
        return $text;
      }
      $hashtags = $matches[1];
      $search   = array();
      $replace  = array();
      foreach ($hashtags as $hashtag) {
        $term = substr($hashtag, 1);
        if (in_array($hashtag, $search)) {
          continue;
        }
        $search[]  = $hashtag;
        $replace[] = "<a href=\"#\" class=\"hashtag-link\" data-hashtag=\"#" . $term . "\">#" . $term . "</a>";
      }
      return str_replace($search, $replace, $text);
    } // parseHashTags

    public static function parseMentions($text, &$mentioned, $userList = array())
    {
      if (preg_match_all('/(@\w+)/', $text, $matches) < 1) {
        return $text;
      }
      $mentions  = $matches[1];
      $search    = array();
      $replace   = array();
      foreach ($mentions as $mention) {
        $username = substr($mention, 1);
        foreach ($userList as $user) {
          if (strtolower($user->username) == strtolower($username)) {
            if (in_array($mention, $search)) {
              continue;
            }
            $search[]    = $mention;
            $replace[]   = "<a href=\"#\" class=\"profile-link\" data-userId=\"" . $user->userId . "\" \>@" . $user->username . "</a>";
            $mentioned[] = $user->userId;
            break;
          }
        }
      }
      return str_replace($search, $replace, $text);
    } // parseMentions

    public static function parseMarkup($text, $allowedTags = array(), $openLinksInNewTab = true)
    {
      $cnf = Config::instance();
      $_s  = array(
               'i'       => '/\[i\]((?!\[\/i\]).*?)\[\/i\]/ms',
               'b'       => '/\[b\]((?!\[\/b\]).*?)\[\/b\]/ms',
               'u'       => '/\[u\]((?!\[\/u\]).*?)\[\/u\]/ms',
               'color'   => '/\[color=([^\]]*)\]((?!\[\/color\]).*?)\[\/color\]/ms',
               'size'    => '/\[size=([^\]]*)\]((?!\[\/size\]).*?)\[\/size\]/ms',
               'url'     => '/\[url=([^\]]*)\]((?!\[\/url\]).*?)\[\/url\]/ms',
               'img'     => '/\[img\]((?!\[\/img\]).*?)\[\/img\]/ms',
               'youtube' => '/\[youtube\]((?!\[\/youtube\]).*?)\[\/youtube\]/ms',
               'marquee' => '/\[marquee\]((?!\[\/marquee\]).*?)\[\/marquee\]/ms',
               'blink'   => '/\[blink\]((?!\[\/blink\]).*?)\[\/blink\]/m'
             );
      $_r  = array(
               'i'       => '<span style="font-style: italic;">$1</span>',
               'b'       => '<span style="font-weight: bold;">$1</span>',
               'u'       => '<span style="text-decoration: underline;">$1</span>',
               'color'   => '<span style="color: $1;">$2</span>',
               'size'    => '<span style="font-size: $1;">$2</span>',
               'url'     => '<a href="$1"' . (($openLinksInNewTab) ? ' target="_blank"' : '') . '>$2</a>',
               'img'     => '<img src="' . $cnf->files->uploads->baseUri . '/$1" alt="$1" />',
               'youtube' => '<iframe width="560" height="315"'
                          . ' src="https://www.youtube.com/embed/$1" frameborder="0"'
                          . ' allowfullscreen></iframe>',
               'marquee' => '<div class="marquee"><div class="marquee-text">$1</div></div>',
               'blink'   => '<span class="blinking-text">$1</span>'
             );
      $s   = array();
      $r   = array();
      if (!empty($allowedTags)) {
        foreach ($allowedTags as $tag) {
          if (array_key_exists($tag, $_s)) {
            $s[$tag] = $_s[$tag];
          }
          if (array_key_exists($tag, $_r)) {
            $r[$tag] = $_r[$tag];
          }
        }
      }
      else {
        $s = $_s;
        $r = $_r;
      }
      unset($_s, $_r);
      $text = preg_replace($s, $r, $text);
      if (empty($allow) || in_array('rainbow', $allow)) {
        $s    = "/\[rainbow\]((?!\[\/rainbow\]).*?)\[\/rainbow\]/ms";
        $text = preg_replace_callback($s, 'self::__rainbow', $text);
      }
      if (empty($allow) || in_array('gradient', $allow)) {
        $s    = "/\[gradient start=((?!end).*?) end=([^\]]*)\]((?!\[\/gradient\]).*?)\[\/gradient\]/ms";
        $text = preg_replace_callback($s, 'self::__gradient', $text);
      }
      if (empty($allow) || in_array('spoiler', $allow)) {
        $s    = "/\[spoiler\]((?!\[\/spoiler\]).*?)\[\/spoiler\]/ms";
        $text = preg_replace_callback($s, 'self::__spoiler', $text);
      }
      return $text;
    } // parseMarkup

    public static function parseRawLinks($text, $openLinksInNewTab = true)
    {
      $s = "/(\s+)(http[s]?\:\/\/[^\s]*)(\s+)/ms";
      $r = "$1<a href=\"$2\"" . (($openLinksInNewTab) ? "target=\"_blank\"" : "") . ">$2</a>$3";
      return preg_replace($s, $r, $text);
    } // parseRawLinks

    public static function stashCodeBlocks($text)
    {
      $matches = array();
      $search  = "/\[code=([^\]]*)\]((?!\[\/code\]).*?)\[\/code\]/ms";
      $count   = preg_match_all($search, $text, $matches);
      if ($count) {
        for ($i = 0; $i < $count; $i++) {
          $id    = randomString(32);
          $geshi = new GeSHi($matches[2][$i], $matches[1][$i]);
		  $geshi->set_link_target('_blank');
          self::$codeBlocks[$id] = $geshi->parse_code();
          $text = str_replace($matches[0][$i], '[[' . $id . ']]', $text);
        }
      }
      $matches = array();
      $search  = "/\[code\]((?!\[\/code\]).*?)\[\/code\]/ms";
      $count   = preg_match_all($search, $text, $matches);
      if ($count) {
        for ($i = 0; $i < $count; $i++) {
          $id = randomString(32);
          self::$codeBlocks[$id] = "<pre style=\"font-family: monospace;\">" . $matches[1][$i] . "</pre>";
          $text = str_replace($matches[0][$i], '[[' . $id . ']]', $text);
        }
      }
      return $text;
    } // stashCodeBlocks

    public static function restoreCodeBlocks($text)
    {
      foreach (self::$codeBlocks as $id => $code) {
        $text = str_replace('[[' . $id . ']]', $code, $text);
      }
      return $text;
    } // restoreCodeBlocks

    public static function stashAnsiArt($text)
    {
      $config = Config::instance();
      if (!$config->programs->ansifilter->enabled) {
        return $text;
      }
      $cmd     = $config->programs->ansifilter->path;
      $matches = array();
      $search  = "/\[ansi type=((?!width).*?) width=((?!height).*?) height=([^\]]*)\]((?!\[\/ansi\]).*?)\[\/ansi\]/ms";
      $count   = preg_match_all($search, $text, $matches);
      if ($count) {
        for ($i = 0; $i < $count; $i++) {
          $id   = randomString(32);
          $type = strtolower($matches[1][$i]);
          $type =  (in_array($type, array('bin', 'cp437', 'tundra'))) ? ("--art-" . $type) : "--art-cp437";
          $w    = $matches[2][$i];
          $h    = $matches[3][$i];
          $src  = $config->files->uploads->directory . "/" . $matches[4][$i];
          $dest = $src . ".html";
          $uri  = $config->files->uploads->baseUri . "/" . $matches[4][$i] . ".html";
          if (!file_exists($dest)) {
            $exec = "$cmd -H $type --art-width $w --art-height $h -i $src -o $dest -F \"Courier New\"";
            exec($exec, $out, $ret);
            if (intval($ret) != 0) {
              $text = str_replace($matches[0][$i], '[The ANSI file could not be rendered.]', $text);
              continue;
            }
          }
          self::$codeBlocks[$id] = "<iframe class=\"ansi-art-frame\" src=\"$uri\"></iframe>";
          $text = str_replace($matches[0][$i], '[[' . $id . ']]', $text);
        }
      }
      $matches = array();
      $search  = "/\[ansi\]((?!\[\/ansi\]).*?)\[\/ansi\]/ms";
      $count   = preg_match_all($search, $text, $matches);
      if ($count) {
        for ($i = 0; $i < $count; $i++) {
          $id   = randomString(32);
          $src  = $config->files->uploads->directory . "/" . $matches[1][$i];
          $dest = $src . ".html";
          $uri  = $config->files->uploads->baseUri . "/" . $matches[1][$i] . ".html";
          if (!file_exists($dest)) {
            $exec = "$cmd -H --art-cp437 -i $src -o $dest -F \"Courier New\"";
            exec($exec, $out, $ret);
            if (intval($ret) != 0) {
              $text = str_replace($matches[0][$i], '[The ANSI file could not be rendered.]', $text);
              continue;
            }
          }
          self::$codeBlocks[$id] = "<iframe class=\"ansi-art-frame\" src=\"$uri\"></iframe>";
          $text = str_replace($matches[0][$i], '[[' . $id . ']]', $text);
        }
      }
      return $text;
    } // stashAnsiArt

    private static function __rainbow($matches)
    {
      return TextEffects::rainbow($matches[1]);
    } // __rainbow

    private static function __gradient($matches)
    {
      return TextEffects::gradient($matches[3], $matches[1], $matches[2]);
    } // __gradient

    private static function __spoiler($matches)
    {
      return TextEffects::spoiler($matches[1]);
    } // __spoiler

  } // MessageParser

?>