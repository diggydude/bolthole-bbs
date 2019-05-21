<?php

  require_once(__DIR__ . '/../System/Config.php');
  require_once(__DIR__ . '/../System/Cache.php');
  require_once(__DIR__ . '/../User/User.php');
  require_once(__DIR__ . '/Emoticons.php');
  require_once(__DIR__ . '/../Markup/MessageParser.php');

  class Comment
  {

    protected

      $id,
      $moduleTypeId,
      $moduleType,
      $moduleId,
      $postedBy,
      $postedAt,
      $body,
      $rendered;

    public function __construct($id = 0)
    {
      $this->id           = 0;
      $this->moduleTypeId = 0;
      $this->moduleId     = 0;
      $this->postedBy     = 0;
      $this->postedAt     = "0000-00-00 00:00:00";
      $this->body         = "";
      $this->rendered     = "";
      if ($id > 0) {
        $this->load($id);
      }
    } // __construct

    public static function create($params)
    {
      $comment               = new Comment();
      $comment->moduleTypeId = (isset($params['moduleTypeId'])) ? $params['moduleTypeId'] : 0;
      $comment->moduleId     = (isset($params['moduleId']))     ? $params['moduleId']     : 0;
      $comment->postedBy     = (isset($params['postedBy']))     ? $params['postedBy']     : 0;
      $comment->postedAt     = (isset($params['postedAt']))     ? $params['postedAt']     : gmdate('Y-m-d H:i:s');
      $comment->body         = (isset($params['body']))         ? $params['body']         : "";
      $comment->save();
      return $comment;
    } // create

    public function load($id)
    {
      $cnf = Config::instance();
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $id  = intval($id);
      $sql = "SELECT"
           . " `cmt`.`id`           AS `id`,"
           . " `cmt`.`moduleTypeId` AS `moduleTypeId`,"
           . " `cmt`.`moduleId`     AS `moduleId`,"
           . " `cmt`.`postedBy`     AS `postedBy`,"
           . " `cmt`.`postedAt`     AS `postedAt`,"
           . " `cmt`.`body`         AS `body`,"
           . " `cmt`.`rendered`     AS `rendered`"
           . " FROM `Comment` AS `cmt`"
           . " LEFT JOIN `ModuleType` AS `mty` ON `mty`.`id` = `cmt`.`moduleTypeId`"
           . " WHERE `cmt`.`id` = $id";
      $stm = $pdo->query($sql);
      $row =  $stm->fetch(PDO::FETCH_ASSOC);
      if ($row) {
        $this->id           = $row['id'];
        $this->moduleTypeId = $row['moduleTypeId'];
        $this->moduleId     = $row['moduleId'];
        $this->postedBy     = $row['postedBy'];
        $this->postedAt     = $row['postedAt'];
        $this->body         = $row['body'];
        $this->rendered     = $row['rendered'];
        return;
      }
      throw new Exception(__METHOD__ . ' > Failed fetching row ' . $id . '.');
    } // load

    public function save()
    {
      $cnf   = Config::instance();
      $cache = new Cache(
                 (object) array(
                   'directory' => $cnf->comments->cache->directory
                 )
               );
      $key   = "comments_" . $this->moduleTypeId . "_" . $this->moduleId;
      $cache->remove($key);
      $pdo          = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $id           = intval($this->id);
      $moduleTypeId = intval($this->moduleTypeId);
      $moduleId     = intval($this->moduleId);
      $postedBy     = intval($this->postedBy);
      $postedAt     = $pdo->quote($this->postedAt, PDO::PARAM_STR);
      $body         = $pdo->quote($this->body,     PDO::PARAM_STR);
      $userList     = User::listUsers();
      $emotes       = Emoticons::instance();
      $options      = array(
                        'allowedTags'       => $cnf->comments->allowedTags,
                        'userList'          => $userList,
                        'emoticonList'      => $emotes->listIcons(),
                        'openLinksInNewTab' => true
                      );
      $mentioned    = array();
      $rendered     = MessageParser::parse($this->body, $mentioned, $options);
      $rendered     = $pdo->quote($rendered, PDO::PARAM_STR);
      if ($id > 0) {
        $sql = "UPDATE `Comment` SET `moduleTypeId` = $moduleTypeId,
                `moduleId` = $moduleId, `postedBy` = $postedBy, `postedAt` = $postedAt,
                `body` = $body, `rendered` = $rendered WHERE `id` = $id";
        $pdo->query($sql);
      }
      else {
        $sql = "INSERT INTO `Comment`(`moduleTypeId`, `moduleId`, `postedBy`, `postedAt`, `body`, `rendered`)
                VALUES ($moduleTypeId, $moduleId, $postedBy, $postedAt, $body, $rendered)";
        $pdo->query($sql);
        $this->id = $pdo->lastInsertId();
      }
      $username = $userList[$postedBy]->username;
      $data = "<a href=\"#\" class=\"profile-link\" data-userId=\"$postedBy\">" . $username . "</a> mentioned you in a ";
      switch ($moduleTypeId) {
        case 1:
          $data .= "<a href=\"#\" class=\"profile-link\" data-userId=\"$moduleId\">comment</a>.";
          break;
        case 2:
          $data .= "<a href=\"#\" class=\"blog-post-link\" data-postId=\"$moduleId\">comment</a>.";
          break;
        case 3:
          $data .= "<a href=\"#\" class=\"file-details-link\" data-fileId=\"$moduleId\">comment</a>.";
          break;
      }
      foreach ($mentioned as $userId) {
        Alerts::enqueue(
          (object) array(
            'typeId'    => 5,
            'recipient' => $userId,
            'private'   => true,
            'data'      => $data
          )
        );
      }
      return $this->id;
    } // save

    public function delete()
    {
      $cnf = Config::instance();
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $sql = "DELETE FROM `Comment` WHERE `id` = " . intval($this->id);
      $pdo->query($sql);
      $this->id           = 0;
      $this->moduleTypeId = 0;
      $this->moduleType   = "";
      $this->moduleId     = 0;
      $this->postedBy     = 0;
      $this->postedAt     = "0000-00-00 00:00:00";
      $this->body         = "";
      $this->rendered     = "";
    } // delete

    public static function listComments($moduleId, $moduleTypeId, $limit = 100)
    {
      $cnf   = Config::instance();
      $cache = new Cache(
                 (object) array(
                   'directory' => $cnf->comments->cache->directory
                 )
               );
      $key   = "comments_" . $moduleTypeId . "_" . $moduleId;
      if ($cache->exists($key)) {
        return $cache->fetch($key);
      }
      $pdo          = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $moduleId     = intval($moduleId);
      $moduleTypeId = intval($moduleTypeId);
      $sql          = "SELECT `com`.`id`       AS `id`,
                              `com`.`postedBy` AS `postedBy`,
                              `com`.`postedAt` AS `postedAt`,
                              `usr`.`username` AS `author`,
                              `com`.`rendered` AS `body`,
                              `prf`.`avatar`   AS `avatar`
                            FROM `Comment` AS `com`
                       LEFT JOIN `User`    AS `usr` ON `usr`.`id`     = `com`.`postedBy`
                       LEFT JOIN `Profile` AS `prf` ON `prf`.`userId` = `usr`.`id`
                       WHERE `com`.`moduleTypeId` = $moduleTypeId AND `com`.`moduleId` = $moduleId
                       ORDER BY `com`.`postedAt` LIMIT " . intval($limit);
      $stm          = $pdo->query($sql);
      $comments     = $stm->fetchAll(PDO::FETCH_OBJ);
      $cache->store($key, $comments);
      return $comments;
    } // listComments

    public function __get($prop)
    {
      return (property_exists($this, $prop)) ? $this->$prop : null;
    } // __get

  } //Comment

?>