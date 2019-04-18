<?php

  require_once(__DIR__ . '/../System/Config.php');
  require_once(__DIR__ . '/../User/User.php');
  require_once(__DIR__ . '/Emoticons.php');
  require_once(__DIR__ . '/../Markup/MessageParser.php');

  class PostOffice
  {

    public static function fetchQueue($forWhom)
    {
      $cnf      = Config::instance();
      $pdo      = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $forWhom  = intval($forWhom);
      $sql      = "SELECT `msg`.`id`        AS `messageId`,
                          `msg`.`to`        AS `to`,
                          `rcp`.`username`  AS `recipient`,
                          `rpf`.`avatar`    AS `recipientAvatar`,
                          `rpf`.`signature` AS `recipientSignature`,
                          `msg`.`from`      AS `from`,
                          `snd`.`username`  AS `sender`,
                          `spf`.`avatar`    AS `senderAvatar`,
                          `spf`.`signature` AS `senderSignature`,
                          `msg`.`postedAt`  AS `postedAt`,
                          `msg`.`subject`   AS `subject`,
                          `msg`.`rendered`  AS `body`
                   FROM      `Mail`    AS `msg`
                   LEFT JOIN `User`    AS `rcp` ON `rcp`.`id`     = `msg`.`to`
                   LEFT JOIN `Profile` AS `rpf` ON `rpf`.`userId` = `rcp`.`id`
                   LEFT JOIN `User`    AS `snd` ON `snd`.`id`     = `msg`.`from`
                   LEFT JOIN `Profile` AS `spf` ON `spf`.`userId` = `snd`.`id`
                   WHERE `msg`.`delivered` = 0 AND `msg`.`to` = $forWhom
                   ORDER BY `msg`.`postedAt` DESC";
      $stm      = $pdo->query($sql);
      return $stm->fetchAll(PDO::FETCH_OBJ);
    } // fetchQueue

    public static function send($params)
    {
      $cnf       = Config::instance();
      $pdo       = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $to        = intval($params->to);
      $from      = intval($params->from);
	  $postedAt  = $pdo->quote(gmdate('Y-m-d H:i:s'), PDO::PARAM_STR);
      $subject   = $pdo->quote($params->subject,      PDO::PARAM_STR);
      $body      = $pdo->quote($params->body,         PDO::PARAM_STR);
      $emotes    = Emoticons::instance();
      $options   = array(
                    'userList'          => User::listUsers(),
                    'emoticonList'      => $emotes->listIcons(),
                    'openLinksInNewTab' => true
                  );
      $mentioned = array();
      $rendered  = MessageParser::parse($params->body, $mentioned, $options);
      $rendered  = $pdo->quote($rendered, PDO::PARAM_STR);
      $sql       = "INSERT INTO `Mail` (`to`,`from`, `postedAt`, `subject`, `body`, `rendered`)
                    VALUES ($to, $from, $postedAt, $subject, $body, $rendered)";
      $pdo->query($sql);
      return $pdo->lastInsertId();
    } // send

    public static function getMessage($messageId)
    {
      $cnf = Config::instance();
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $sql      = "SELECT `msg`.`id`        AS `messageId`,
                          `msg`.`to`        AS `to`,
                          `rcp`.`username`  AS `recipient`,
                          `rpf`.`avatar`    AS `recipientAvatar`,
                          `rpf`.`signature` AS `recipientSignature`,
                          `msg`.`from`      AS `from`,
                          `snd`.`username`  AS `sender`,
                          `spf`.`avatar`    AS `senderAvatar`,
                          `spf`.`signature` AS `senderSignature`,
                          `msg`.`postedAt`  AS `postedAt`,
                          `msg`.`subject`   AS `subject`,
                          `msg`.`rendered`  AS `body`
                   FROM      `Mail`    AS `msg`
                   LEFT JOIN `User`    AS `rcp` ON `rcp`.`id`     = `msg`.`to`
                   LEFT JOIN `Profile` AS `rpf` ON `rpf`.`userId` = `rcp`.`id`
                   LEFT JOIN `User`    AS `snd` ON `snd`.`id`     = `msg`.`from`
                   LEFT JOIN `Profile` AS `spf` ON `spf`.`userId` = `snd`.`id`
                   WHERE `msg`.`id` = " . intval($messageId);
      $stm = $pdo->query($sql);
      return $stm->fetchObject();
    } // getMessage

    public static function dispatch($messageId)
    {

      $cnf = Config::instance();
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $sql = "UPDATE `Mail` SET `delivered` = 1 WHERE `id` = " . intval($messageId);
      $pdo->query($sql);
    } // dispatch

  } // PostOffice

?>