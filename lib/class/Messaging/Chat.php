<?php

  require_once(__DIR__ . '/../System/Config.php');
  require_once(__DIR__ . '/../User/User.php');
  require_once(__DIR__ . '/Emoticons.php');
  require_once(__DIR__ . '/Alerts.php');
  require_once(__DIR__ . '/../Markup/MessageParser.php');

  class Chat
  {

    public static function join($username)
    {
      $cnf = Config::instance();
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $sql = "SELECT IFNULL(`id`, 0) AS `lastMessageId` FROM `Chat` ORDER BY `postedAt` DESC LIMIT 1";
      $stm = $pdo->query($sql);
      $lastMessageId = $stm->fetchColumn();
      self::sendMessage(
        (object) array(
          'postedBy' => 1,
          'body'     => "@" . $username . " has arrived."
        )
      );
      return $lastMessageId;
    } // join

    public static function quit($username)
    {
      self::sendMessage(
        (object) array(
          'postedBy' => 1,
          'body'     => "@" . $username . " has departed."
        )
      );
    } // quit

    public static function sendMessage($params)
    {
      $cnf       = Config::instance();
      $pdo       = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $postedBy  = intval($params->postedBy);
      $postedAt  = $pdo->quote(gmdate('Y-m-d H:i:s'), PDO::PARAM_STR);
      $userList  = User::listUsers();
      $emotes    = Emoticons::instance();
      $options   = array(
                      'allowedTags'       => $cnf->chat->allowedTags,
                      'userList'          => $userList,
                      'emoticonList'      => $emotes->listIcons(),
                      'openLinksInNewTab' => true
                    );
      $mentioned = array();
      $body      = MessageParser::parse($params->body, $mentioned, $options);
      $body      = $pdo->quote($body, PDO::PARAM_STR);
      $sql       = "INSERT INTO `Chat` (`postedBy`, `postedAt`, `body`)
                    VALUES ($postedBy, $postedAt, $body)";
      $pdo->query($sql);
      if (($postedBy == 1) && ((stripos($body, 'has arrived.') !== false) || (stripos($body, 'has departed.') !== false))) {
        return;
      }
      $username  = $userList[$postedBy]->username;
      $data      = "<a href=\"#\" class=\"profile-link\" data-userId=\"$postedBy\">$username</a> mentioned you in chat.";
      $mentioned = array_unique($mentioned);
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
    } // sendMessage

    public static function getMessages($lastMsgId)
    {
      $cnf       = Config::instance();
      $pdo       = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $lastMsgId = intval($lastMsgId);
      $sql       = "SELECT `cht`.`id`       AS `id`,
                           `cht`.`postedAt` AS `postedAt`,
                           `cht`.`postedBy` AS `postedBy`,
                           `usr`.`username` AS `author`,
                           `cht`.`body`     As `body`
                         FROM `Chat` AS `cht`
                    LEFT JOIN `User` AS `usr` ON `usr`.`id` = `cht`.`postedBy`
                    WHERE `cht`.`id` > $lastMsgId ORDER BY `postedAt`";
      $stm       = $pdo->query($sql);
      return $stm->fetchAll(PDO::FETCH_OBJ);
    } // getMessages

  } // Chat

?>