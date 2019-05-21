<?php

  require_once(__DIR__ . '/../System/Config.php');
  require_once(__DIR__ . '/../User/User.php');
  require_once(__DIR__ . '/UserFile.php');

  class Library
  {

    protected

      $id,
      $ownerId;

    public function __construct($id = 0)
    {
      $this->id      = 0;
      $this->ownerId = 0;
      if ($id > 0) {
        $this->load($id);
      }
    } // __construct

    public static function create($params)
    {
      $library          = new Library();
      $library->ownerId = (isset($params['ownerId']))  ? $params['ownerId']  : 0;
      $library->save();
      return $library;
    } // create

    public function load($id)
    {
      $cnf = Config::instance();
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $id  = intval($id);
      $sql = "SELECT * FROM `Library` WHERE `id` = $id";
      $stm = $pdo->query($sql);
      $row = $stm->fetch(PDO::FETCH_ASSOC);
      if ($row) {
        $this->id      = $row['id'];
        $this->ownerId = $row['ownerId'];
      }
    } // load

    public function save()
    {
      $cnf     = Config::instance();
      $pdo     = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $id      = intval($this->id);
      $title   = $pdo->quote($this->title, PDO::QUOTE_STR);
      $ownerId = intval($this->ownerId);
      if ($id > 0) {
        $sql = "UPDATE `Library` SET `ownerId` = $ownerId WHERE `id` = $id";
        $pdo->query($sql);
      }
      else {
        $sql = "INSERT INTO `Library` (`ownerId`) VALUES ($ownerId)";
        $pdo->query($sql);
        $this->id = $pdo->lastInsertId();
      }
      return $this->id;
    } // save

    public function delete()
    {
      $cnf = Config::instance();
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $id  = intval($this->id);
      $sql = "DELETE FROM `Comment` WHERE `id` IN ("
           . "  SELECT `co`.`id` AS `id`"
           . "  FROM `Comment`       AS `co`"
           . "  LEFT JOIN `FileInLibrary` AS `fl` ON `fl`.`id` = `co`.`moduleId`"
           . "  LEFT JOIN `Library`       AS `li` ON `li`.`id` = `fl`.`libraryId`"
           . "  WHERE `li`.`id` = $id AND `co`.`moduleTypeId` = 3)";
      $pdo->query($sql);
      $sql = "DELETE FROM `FileInLibrary` WHERE `libraryId` = $id";
      $pdo->query($sql);
      $sql = "DELETE FROM `Library` WHERE `id` = $id";
      $pdo->query($sql);
    } // delete

    public function getOwner()
    {
      return new User($this->ownerId);
    } // getOwner

    public function listFiles()
    {
      return UserFile::listFiles($this->id);
    } // listFiles

    public function __get($prop)
    {
      return (property_exists($this, $prop)) ? $this->$prop : null;
    } // __get

  } // Library

?>