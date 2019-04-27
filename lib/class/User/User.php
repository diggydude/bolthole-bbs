<?php

  require_once(__DIR__ . '/../System/Config.php');
  require_once(__DIR__ . '/Profile.php');

  class User
  {

    const PERM_ADMINISTRATOR = 4;
    const PERM_MODERATOR     = 3;
    const PERM_ELITE_MEMBER  = 2;
    const PERM_MEMBER        = 1;
    const PERM_GUEST         = 0;

    protected

      $id,
      $userame,
      $password,
      $question,
      $answer,
      $joined,
      $accessLevel,
      $lastError;

    public function __construct($id = 0)
    {
      $this->id          = 0;
      $this->username    = "";
      $this->password    = "";
      $this->question    = "";
      $this->answer      = "";
      $this->joined      = "0000-00-00 00:00:00";
      $this->accessLevel = self::PERM_GUEST;
      $this->lastError   = "";
      if ($id > 0) {
        $this->load($id);
      }
    } // __construct

    public static function create($params)
    {
      if (($user = self::getUserByName($params->username)) !== false) {
        return false;
      }
      $user              = new User();
      $user->username    = (property_exists($params, 'username'))    ? $params->username    : "";
      $user->password    = (property_exists($params, 'password'))    ? $params->password    : "";
      $user->question    = (property_exists($params, 'question'))    ? $params->question    : "";
      $user->answer      = (property_exists($params, 'answer'))      ? $params->answer      : "";
      $user->joined      = (property_exists($params, 'joined'))      ? $params->joined      : gmdate('Y-m-d H:i:s');
      $user->accessLevel = (property_exists($params, 'accessLevel')) ? $params->accessLevel : self::PERM_GUEST;
      $user->save();
      return $user;
    } // create

    public function load($id)
    {
      $cnf = Config::instance();
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $sql = "SELECT * FROM `User` WHERE `id` = " . intval($id);
      $stm = $pdo->query($sql);
      $row = $stm->fetchObject();
      if ($row) {
        $this->id          = $row->id;
        $this->username    = $row->username;
        $this->password    = $row->password;
        $this->question    = $row->question;
        $this->answer      = $row->answer;
        $this->joined      = $row->joined;
        $this->accessLevel = $row->accessLevel;
        return true;
      }
      $this->lastError = "Invalid user ID.";
      return false;
    } // load

    public function save()
    {
      $cnf         = Config::instance();
      $pdo         = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $id          = intval($this->id);
      $username    = $pdo->quote($this->username, PDO::PARAM_STR);
      $password    = $pdo->quote($this->password, PDO::PARAM_STR);
      $question    = $pdo->quote($this->question, PDO::PARAM_STR);
      $answer      = $pdo->quote($this->answer,   PDO::PARAM_STR);
      $joined      = $pdo->quote($this->joined,   PDO::PARAM_STR);
      $accessLevel = intval($this->accessLevel);
      if ($id > 0) {
        $sql = "UPDATE `User` SET `username` = $username, `password` = $password,"
             . " `question` = $question, `answer` = $answer, `joined` = $joined,"
             . " `accessLevel` = $accessLevel WHERE `id` = $id";
        $pdo->query($sql);
      }
      else {
        $sql = "INSERT INTO `User` (`username`, `password`, `question`, `answer`,"
             . " `joined`, `accessLevel`) VALUES ($username, $password, $question,"
             . " $answer, $joined, $accessLevel)";
        $pdo->query($sql);
        $this->id  = $pdo->lastInsertId();
        $title     = $pdo->quote($cnf->profiles->defaultTitle,     PDO::PARAM_STR);
        $avatar    = $pdo->quote($cnf->profiles->defaultAvatar,    PDO::PARAM_STR);
        $signature = $pdo->quote($cnf->profiles->defaultSignature, PDO::PARAM_STR);
        $website   = $pdo->quote($cnf->profiles->defaultWebsite,   PDO::PARAM_STR);
        $about     = $pdo->quote($cnf->profiles->defaultAbout,     PDO::PARAM_STR);
        $sql       = "INSERT INTO `Profile` (`userId`, `title`, `avatar`, `signature`, `website`, `about`)
                      VALUES (" . $this->id . ", $title, $avatar, $signature, $website, $about)";
        $pdo->query($sql);
        $sql = "INSERT INTO `Blog` (`ownerId`) VALUES (" .$this->id . ")";
        $pdo->query($sql);
        $sql = "INSERT INTO `Library` (`ownerId`) VALUES (" .$this->id . ")";
        $pdo->query($sql);
      }
      return $this->id;
    } // save

    public function update($params)
    {
      $cnf = Config::instance();
      if (property_exists($params, 'password')) {
        $this->password = $params->password;
      }
      if (property_exists($params, 'question')) {
        $this->question = $params->question;
      }
      if (property_exists($params, 'answer')) {
        $this->answer = $params->answer;
      }
      $this->save();
    } // update

    public function delete()
    {
      $id = intval($this->id);
      if ($id == 1) {
        $this->lastError = "Cannot delete the superuser.";
        return false;
      }
      $cnf = Config::instance();
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $sql = "DELETE FROM `User` WHERE `id` = $id";
      $pdo->query($sql);
      $this->id          = 0;
      $this->username    = "";
      $this->password    = "";
      $this->question    = "";
      $this->answer      = "";
      $this->joined      = "0000-00-00 00:00:00";
      $this->accessLevel = self::PERM_GUEST;
      return true;
    } // delete

    public function getProfile()
    {
      return new Profile($this->id);
    } // getProfile

    public static function getUserByName($username)
    {
      $cnf = Config::instance();
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $sql = "SELECT `id` FROM `User` WHERE `username` = " . $pdo->quote($username, PDO::PARAM_STR);
      $stm = $pdo->query($sql);
      $id  = $stm->fetchColumn();
      if (!$id) {
        return false;
      }
      return new User($id);
    } // getUserByName

    public static function banUser($userId)
    {
      $cnf    = Config::instance();
      $pdo    = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $userId = intval($userId);
      $sql    = "INSERT INTO `Ban` (`userId`, `bannedBy`, `reason`) VALUES ($userId, 2, 'No reason.')";
      $stm    = $pdo->query($sql);
    } // banUser

    public static function userIsBanned($userId)
    {
      $cnf = Config::instance();
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $sql = "SELECT `bannedAt` FROM `Ban` WHERE `userId` = " . intval($userId);
      $stm = $pdo->query($sql);
      $col  = $stm->fetchColumn();
      if (!$col) {
        return false;
      }
      return true;
    } // userIsBanned

    public static function listUsers()
    {
      $cnf   = Config::instance();
      $pdo   = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $sql   = "SELECT `usr`.`id`       AS `userId`,
                       `usr`.`username` AS `username`,
                       `usr`.`joined`   AS `joined`,
                       `pfl`.`avatar`   AS `avatar`,
                       `pfl`.`title`    AS `title`,
                       `blg`.`id`       AS `blogId`,
                       `lib`.`id`       AS `libraryId`
                     FROM `User`    AS `usr`
                LEFT JOIN `Profile` AS `pfl` ON `pfl`.`userId`  = `usr`.`id`
                LEFT JOIN `Blog`    AS `blg` ON `blg`.`ownerId` = `usr`.`id`
                LEFT JOIN `Library` AS `lib` ON `lib`.`ownerId` = `usr`.`id`
                ORDER BY `usr`.`username`";
      $stm   = $pdo->query($sql);
      $rows  =  $stm->fetchAll(PDO::FETCH_OBJ);
      $users = array();
      foreach ($rows as $row) {
        $users[$row->userId] = $row;
      }
      return $users;
    } // listUsers

    public function getLastError()
    {
      $message = $this->lastError;
      $this->lastError = "";
      return $message;
    } // getLastError

    public function __get($prop)
    {
      return (property_exists($this, $prop)) ? $this->$prop : null;
    } // __get

  } // User

?>