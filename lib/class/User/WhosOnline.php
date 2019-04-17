<?php

  require_once(__DIR__ . '/../System/Config.php');
  require_once(__DIR__ . '/../Messaging/Alerts.php');

  class WhosOnline
  {

    public static function listUsers()
    {
      $cnf   = Config::instance();
      $pdo   = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $sql   = "SELECT"
             . " `who`.`userId`    AS `id`,"
             . " `who`.`arrivedAt` AS `arrivedAt`,"
             . " `who`.`sessionId` AS `sessionId`,"
             . " `usr`.`username`  AS `username`"
             . " FROM `WhosOnline` AS `who`"
             . " LEFT JOIN `User` AS `usr` ON `usr`.`id` = `who`.`userId`";
      $stm   = $pdo->query($sql);
      $rows  = $stm->fetchAll(PDO::FETCH_OBJ);
      $users = array();
      foreach ($rows as $row) {
        $users[$row->id] = $row;
      }
      return $users;
    } // listUsers

    public static function userArrived($userId, $sessionId)
    {
      $cnf       = Config::instance();
      $pdo       = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $userId    = intval($userId);
      $sessionId = $pdo->quote($sessionId, PDO::PARAM_STR);
      $sql       = "INSERT INTO `WhosOnline` (`userId`, `sessionId`) VALUES ($userId, $sessionId)";
      $pdo->query($sql);
      $sql      = "SELECT `username` FROM `User` WHERE `id` = $userId";
      $stm      = $pdo->query($sql);
      $username = $stm->fetchColumn();
    } // userArrived

    public static function userDeparted($userId)
    {
      $cnf    = Config::instance();
      $pdo    = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $userId = intval($userId);
      $sql    = "DELETE FROM `WhosOnline` WHERE `userId` = $userId";
      $pdo->query($sql);
      $sql      = "SELECT `username` FROM `User` WHERE `id` = $userId";
      $stm      = $pdo->query($sql);
      $username = $stm->fetchColumn();
    } // userDeparted

    public static function userIsOnline($userId)
    {
      $cnf    = Config::instance();
      $pdo    = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $userId = intval($userId);
      $sql    = "SELECT count(*) FROM `WhosOnline` WHERE `userId` = $userId";
      $stm    = $pdo->query($sql);
      return (bool) $stm->fetchColumn();
    } // userIsOnline

  } // WhosOnline

?>