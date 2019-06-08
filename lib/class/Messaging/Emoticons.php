<?php

  require_once(__DIR__ . '/../System/Config.php');
  require_once(__DIR__ . '/../../trait/Singleton.php');

  class Emoticons
  {

    use Singleton;

    protected

      $items,
      $lastError;

    protected function __construct()
    {
      $this->items     = array();
      $this->lastError = "";
      $this->load();
    } // __construct

    public function load()
    {
      $cnf  = Config::instance();
      $pdo  = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $sql  = "SELECT * FROM `Emoticon`";
      $stm  = $pdo->query($sql);
      $rows = $stm->fetchAll(PDO::FETCH_OBJ);
      foreach ($rows as $row) {
        $this->items[$row->code] = $row->filename;
      }
    } // load

    public function addIcon($bbCode, $filename)
    {
      $cnf  = Config::instance();
      $file = $cnf->files->emoticons->directory . "/" . $filename;
      if (!file_exists($file)) {
        $this->lastError = " File \"$file\" does not exist.";
        return false;
      }
      $pdo       = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $_code     = $pdo->quote($code,     PDO::PARAM_STR);
      $_filename = $pdo->quote($filename, PDO::PARAM_STR);
      $sql       = "INSERT INTO `Emoticon` (`code`, `filename`) VALUES ($code, $filename)";
      if ($pdo->query($sql)) {
        $this->items[$code] = $filename;
        return true;
      }
      $this->lastError = "Failed inserting row for \"$code\".";
      return false;
    } // addIcon

    public function removeIcon($bbCode)
    {
      $cnf      = Config::instance();
      $pdo      = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $code     = $pdo->quote($code, PDO::PARAM_STR);
      $sql      = "SELECT `filename` FROM `Emoticon` WHERE `code` = $code";
      $stm      = $pdo->query($sql);
      $filename = $stm->fetchColumn();
      if ($filename) {
        $sql = "DELETE FROM `Emoticon` WHERE `code` = $code";
        $pdo->query($sql);
        $file = $cnf->files->emoticons->directory . "/" . $filename;
        @unlink($file);
        unset($this->items[$code]);
        return true;
      }
      $this->lastError = "Emoticon \"$bbCode\" not in database.";
      return false;
    } // removeIcon

    public function listIcons($trimKeys = false)
    {
      if (!$trimKeys) {
        return $this->items;
      }
      $results = array();
      foreach ($this->items as $k => $v) {
        $key = trim($k, ':');
        $results[$key] = $v;
      }
      return $results;
    } // listIcons

    public function getLastError()
    {
      $message = $this->lastError;
      $this->lastError = "";
      return $message;
    } // getLastError

    public function __get($property)
    {
      return (isset($this->$property)) ? $this->$property : null;
    } // __get

  } // Emoticons