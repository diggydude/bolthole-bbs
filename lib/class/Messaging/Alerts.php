<?php

  require_once(__DIR__ . '/../System/Config.php');
  require_once(__DIR__ . '/Chat.php');

  class Alerts
  {

    public static function fetchQueue($forWhom)
    {
      $cnf     = Config::instance();
      $pdo     = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $forWhom = intval($forWhom);
      $sql = "SELECT `evt`.`id`          AS `id`,
                     `evt`.`typeId`      AS `typeId`,
                     `evt`.`recipientId` AS `recipient`,
                     `evt`.`occurredAt`  AS `occurredAt`,
                     `evt`.`data`        AS `data`
              FROM `Event` AS `evt`
              LEFT JOIN `EventDispatch` AS `dsp` ON `dsp`.`eventId` = `evt`.`id` AND `dsp`.`recipientId` = `evt`.`recipientId`
              WHERE ((`evt`.`recipientId` = $forWhom AND `evt`.`private` = 1)
              OR (`evt`.`recipientId` = 1 AND `evt`.`private` = 0))
              AND (`evt`.`occurredAt` > (SELECT `joined` FROM `User` WHERE `id` = $forWhom))
              AND (SELECT `eventId` FROM `EventDispatch` WHERE `eventId` = `evt`.`id` AND `recipientId` = $forWhom) IS NULL
              ORDER BY `evt`.`occurredAt`";
      $stm = $pdo->query($sql);//var_dump($pdo->errorInfo());
      return $stm->fetchAll(PDO::FETCH_OBJ);
    } // fetchQueue

    public static function enqueue($params)
    {
      $cnf         = Config::instance();
      $pdo         = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $typeId      = intval($params->typeId);
      $private     = ($params->private == false) ? 0 : 1;
      $recipientId = ($private == 0) ? 1 : intval($params->recipient);
      $occurredAt  = $pdo->quote(gmdate('Y-m-d H:i:s'), PDO::PARAM_STR);
      $data        = $pdo->quote($params->data, PDO::PARAM_STR);
      $sql         = "INSERT INTO `Event` (`typeId`, `recipientId`, `occurredAt`, `private`, `data`)
                      VALUES ($typeId, $recipientId, $occurredAt, $private, $data)";
      $pdo->query($sql);
    } // enqueue

    public static function dispatch($eventId, $recipientId)
    {
      $cnf         = Config::instance();
      $pdo         = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $eventId     = intval($eventId);
      $recipientId = intval($recipientId);
      $sql         = "INSERT INTO `EventDispatch` (`eventId`, `recipientId`) VALUES ($eventId, $recipientId)";
      $pdo->query($sql);
    } // dispatch

  } // Alerts

?>