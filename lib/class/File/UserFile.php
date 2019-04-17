<?php

  require_once(__DIR__ . '/../System/Config.php');
  require_once(__DIR__ . '/../User/User.php');
  require_once(__DIR__ . '/Library.php');

  class UserFile
  {

    protected

      $id,
      $fileId,
      $filename,
      $mimeType,
      $size,
      $hash,
      $uploadedAt,
      $description,
      $inLibrary,
      $downloads;

    public function __construct($id = 0)
    {
      $this->id          = 0;
      $this->fileId      = 0;
      $this->filename    = "";
      $this->mimeType    = "";
      $this->size        = 0;
      $this->hash        = "";
      $this->uploadedAt  = "0000-00-00 00:00:00";
      $this->description = "";
      $this->inLibrary   = 0;
      $this->downloads   = 0;
      if ($id > 0) {
        $this->load($id);
      }
    } // __construct

    public static function create($params)
    {
      $file              = new UserFile();
      $file->id          = (isset($params['id']))          ? $params['id']          : 0;
      $file->fileId      = (isset($params['fileId']))      ? $params['fileId']      : 0;
      $file->filename    = (isset($params['filename']))    ? $params['filename']    : "";
      $file->mimeType    = (isset($params['mimeType']))    ? $params['mimeType']    : "";
      $file->size        = (isset($params['size']))        ? $params['size']        : 0;
      $file->hash        = (isset($params['hash']))        ? $params['hash']        : "";
      $file->uploadedAt  = (isset($params['uploadedAt']))  ? $params['uploadedAt']  : gmdate('Y-m-d H:i:s');
      $file->description = (isset($params['description'])) ? $params['description'] : "";
      $file->inLibrary   = (isset($params['inLibrary']))   ? $params['inLibrary']   : 0;
      $file->downloads   = (isset($params['downloads']))   ? $params['downloads']   : 0;
      $file->save();
      return $file;
    } // create

    public function load($id)
    {
      $cnf  = Config::instance();
      $pdo  = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $id   = intval($id);
      $sql  = "SELECT"
            . " `flb`.`id`          AS `id`,"
            . " `flb`.`fileId`      AS `fileId`,"
            . " `flb`.`filename`    AS `filename`,"
            . " `fil`.`mimeType`    AS `mimeType`,"
            . " `fil`.`size`        AS `size`,"
            . " `fil`.`hash`        AS `hash`,"
            . " `flb`.`uploadedAt`  AS `uploadedAt`,"
            . " `flb`.`description` AS `description`,"
            . " `flb`.`libraryId`   AS `inLibrary`,"
            . " `flb`.`downloads`   AS `downloads`"
            . "      FROM `FileInLibrary` AS `flb`"
            . " LEFT JOIN `File`          AS `fil` ON `fil`.`id` = `flb`.`fileId`"
            . " WHERE `flb`.`id` = $id";
      $stm  = $pdo->query($sql);
      $row  = $stm->fetch(PDO::FETCH_ASSOC);
      if ($row) {
        $this->id          = $row['id'];
        $this->fileId      = $row['fileId'];
        $this->filename    = $row['filename'];
        $this->mimeType    = $row['mimeType'];
        $this->size        = $row['size'];
        $this->hash        = $row['hash'];
        $this->uploadedAt  = $row['uploadedAt'];
        $this->description = $row['description'];
        $this->inLibrary   = $row['inLibrary'];
        $this->downloads   = $row['downloads'];
      }
    } // load

    public function save()
    {
      $cnf         = Config::instance();
      $pdo         = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $filename    = $pdo->quote($this->filename,    PDO::PARAM_STR);
      $uploadedAt  = $pdo->quote($this->uploadedAt,  PDO::PARAM_STR);
      $description = $pdo->quote($this->description, PDO::PARAM_STR);
      $libraryId   = intval($this->inLibrary);
      $downloads   = intval($this->downloads);
      $mimeType    = $pdo->quote($this->mimeType, PDO::PARAM_STR);
      $size        = intval($this->size);
      $hash        = $pdo->quote($this->hash, PDO::PARAM_STR);
      $sql         = "SELECT `id` AS `fileId` FROM `File` WHERE `hash` = $hash";
      $stm         = $pdo->query($sql);
      if (($fileId = $stm->fetchColumn()) === false) {
        $sql = "INSERT INTO `File` (`mimeType`, `size`, `hash`) VALUES ($mimeType, $size, $hash)";
        $pdo->query($sql);
        $fileId = $pdo->lastInsertId();
      }
      $fileId = intval($fileId);
      $sql = "INSERT INTO `FileInLibrary` (`filename`, `fileId`, `uploadedAt`, `description`, `libraryId`, `downloads`)
              VALUES ($filename, $fileId, $uploadedAt, $description, $libraryId, $downloads) ON DUPLICATE KEY UPDATE
              `filename` = $filename, `description` = $description, `id` = LAST_INSERT_ID(`id`)";
      $pdo->query($sql);
      $this->id = $pdo->lastInsertId();
      return $this->id;
    } // save

    public function delete()
    {
      $cnf = Config::instance();
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $id  = intval($this->id);
      $sql = "DELETE FROM `Comment` WHERE `id` IN (SELECT `id` FROM `Comment` WHERE `moduleTypeId` = 3 AND `moduleId` = $id)";
      $pdo->query($sql);
      $sql = "DELETE FROM `FileInLibrary` WHERE `id` = $id";
      $pdo->query($sql);
      $this->id          = 0;
      $this->filename    = "";
      $this->mimeType    = "";
      $this->size        = 0;
      $this->hash        = "";
      $this->uploadedAt  = "0000-00-00 00:00:00";
      $this->description = "";
      $this->inLibrary   = 0;
      $this->downloads   = 0;
    } // delete

    public function getLibrary()
    {
      return new Library($this->libraryId);
    } // getLibrary

    public function download()
    {
      $cnf = Config::instance();
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $sql = "UPDATE `FileInLibrary` SET `downloads` = `downloads` + 1 WHERE `id` = " . intval($this->id);
      $pdo->query($sql);
    } // download

    public function listComments()
    {
      return Comment::listComments($this->id, 3);
    } // listComments

    public static function listFiles($libraryId)
    {
      $cnf = Config::instance();
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $id  = intval($libraryId);
      $sql = "SELECT"
           . " `flb`.`id`          AS `id`,"
           . " `flb`.`fileId`      AS `fileId`,"
           . " `flb`.`filename`    AS `filename`,"
           . " `flb`.`description` AS `description`,"
           . " `flb`.`uploadedAt`  AS `uploadedAt`,"
           . " `flb`.`downloads`   AS `downloads`,"
           . " `flb`.`libraryId`   AS `inLibrary`,"
           . " `fil`.`size`        AS `size`,"
           . " `fil`.`mimeType`    AS `mimeType`,"
           . " `fil`.`hash`        AS `hash`"
           . "      FROM `FileInLibrary` AS `flb`"
           . " LEFT JOIN `File`          AS `fil` ON `fil`.`id` = `flb`.`fileId`"
           . " WHERE `flb`.`libraryId` = $id ORDER BY `flb`.`filename` LIMIT 100";
      $stm = $pdo->query($sql);
      return $tsm->fetchAll(PDO::FETCH_OBJ);
    } // listFiles

    public static function search($terms)
    {
      $cnf   = Config::instance();
      $pdo   = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $terms = $pdo->quote('%' . $terms . '%', PDO::PARAM_STR);
      $sql   = "SELECT"
             . " `flb`.`id`          AS `id`,"
             . " `flb`.`fileId`      AS `fileId`,"
             . " `flb`.`filename`    AS `filename`,"
             . " `flb`.`description` AS `description`,"
             . " `flb`.`uploadedAt`  AS `uploadedAt`,"
             . " `flb`.`downloads`   AS `downloads`,"
             . " `flb`.`libraryId`   AS `inLibrary`,"
             . " `fil`.`size`        AS `size`,"
             . " `fil`.`mimeType`    AS `mimeType`,"
             . " `fil`.`hash`        AS `hash`"
             . "      FROM `FileInLibrary` AS `flb`"
             . " LEFT JOIN `File`          AS `fil` ON `fil`.`id` = `flb`.`fileId`"
             . " WHERE `flb`.`filename`    LIKE $terms"
             . "    OR `flb`.`description` LIKE $terms"
             . "    OR `fil`.`mimeType`    LIKE $terms"
             . "    OR `fil`.`hash`        LIKE $terms"
             . " ORDER BY `flb`.`filename`";
      $stm   = $pdo->query($sql);
      return $stm->fetchAll(PDO::FETCH_OBJ);
    } // search

    public function __get($property)
    {
      return (isset($this->$property)) ? $this->$property : null;
    } // __get

  } // UserFile

?>