<?php

  require_once(__DIR__ . '/../System/Config.php');
  require_once(__DIR__ . '/../System/Cache.php');
  require_once(__DIR__ . '/../User/User.php');
  require_once(__DIR__ . '/../Messaging/Emoticons.php');
  require_once(__DIR__ . '/../Messaging/Alerts.php');
  require_once(__DIR__ . '/../Markup/MessageParser.php');
  require_once(__DIR__ . '/../Tree/Tree.php');

  class Forum
  {

    protected static

      $lastError = "";

    public static function getRecent()
    {
      $cnf   = Config::instance();
      $cache = new Cache(
                 (object) array(
                   'directory' => $cnf->forum->cache->directory
                 )
               );
      if ($cache->exists('recent')) {
        return $cache->fetch('recent');
      }
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,  false);
      $pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
      $sql = "SELECT `thr`.`id`         AS `threadId`,
                     `thr`.`topic`      AS `topic`,
                     `thr`.`startedBy`  AS `startedBy`,
                     `sus`.`username`   AS `starter`
                   FROM `ForumThread` AS `thr`
              LEFT JOIN `User`        AS `sus` ON `sus`.`id` = `thr`.`startedBy`
              ORDER BY `thr`.`lastPostAt` DESC LIMIT " . intval($cnf->forum->maxThreads);
      $stm = $pdo->query($sql);
      $threads   = array();
      $threadIds = array();
      while (($row = $stm->fetchObject()) !== false) {
        $threads[]   = $row;
        $threadIds[] = intval($row->threadId);
      }
      if (empty($threadIds)) {
        $data = (object) array(
                  'threads' => array(),
                  'posts'   => array()
                );
        $cache->store('recent', $data);
        return $data;
      }
      $sql   = "SELECT `pst`.`id`                   AS `postId`,
                       `pit`.`threadId`             AS `inThread`,
                       IFNULL(`rpl`.`inReplyTo`, 0) AS `inReplyTo`,
                       `pst`.`postedAt`             AS `postedAt`,
                       `pst`.`postedBy`             AS `postedBy`,
                       `usr`.`username`             AS `author`,
                       `pfl`.`signature`            AS `signature`,
                       `pfl`.`avatar`               AS `avatar`,
                       `pst`.`topic`                AS `topic`,
                       `pst`.`rendered`             AS `rendered`
                     FROM `ForumPost`         AS `pst`
                LEFT JOIN `ForumPostReply`    AS `rpl` ON `rpl`.`postId` = `pst`.`id`
                LEFT JOIN `ForumPostInThread` AS `pit` ON `pit`.`postId` = `pst`.`id`
                LEFT JOIN `User`              AS `usr` ON `usr`.`id`     = `pst`.`postedBy`
                LEFT JOIN `Profile`           AS `pfl` ON `pfl`.`userId` = `usr`.`id`
                WHERE `pit`.`threadId` IN (" . implode(",", $threadIds) . ") ORDER BY `pst`.`id`";
      $stm   = $pdo->query($sql);
      $posts = $stm->fetchAll(PDO::FETCH_OBJ);
      $data  = (object) array(
                 'threads' => $threads,
                 'posts'   => $posts
               );
      $cache->store('recent', $data);
      return $data;
    } // getRecent

    public static function postMessage($params)
    {
      $cnf       = Config::instance();
      $pdo       = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $inReplyTo = intval($params->inReplyTo);
      $postedBy  = intval($params->postedBy);
      $postedAt  = $pdo->quote(gmdate('Y-m-d H:i:s'), PDO::PARAM_STR);
      $topic     = $pdo->quote($params->topic, PDO::PARAM_STR);
      $body      = $pdo->quote($params->body,  PDO::PARAM_STR);
      $emotes    = Emoticons::instance();
      $userList  = User::listUsers();
      $options   = array(
                    'allowedTags'       => $cnf->forum->allowedTags,
                    'userList'          => $userList,
                    'emoticonList'      => $emotes->listIcons(),
                    'openLinksInNewTab' => true
                  );
      $mentioned = array();
      $rendered  = MessageParser::parse($params->body, $mentioned, $options);
      $rendered  = $pdo->quote($rendered, PDO::PARAM_STR);
      $sql       = "INSERT INTO `ForumPost` (`postedBy`, `postedAt`, `topic`, `body`, `rendered`)
                    VALUES ($postedBy, $postedAt, $topic, $body, $rendered)";
      $pdo->query($sql);
      $error = $pdo->errorInfo();
      if ($error[0] !== "00000") {
        self::$lastError = $error[2];
        return false;
      }
      $postId = $pdo->lastInsertId();
      if ($inReplyTo == 0) {
        $threadId = $postId;
        $sql      = "INSERT INTO `ForumThread` (`id`, `topic`, `startedAt`,
                     `startedBy`, `lastPostId`, `lastPostAt`, `lastPostBy`)
                     VALUES ($threadId, $topic, $postedAt, $postedBy, $postId, $postedAt, $postedBy)";
        $pdo->query($sql);
      }
      else {
        $sql = "INSERT INTO `ForumPostReply` (`postId`, `inReplyTo`) VALUES ($postId, $inReplyTo)";
        $pdo->query($sql);
        $sql      = "SELECT `threadId` FROM `ForumPostInThread` WHERE `postId` = $inReplyTo";
        $stm      = $pdo->query($sql);
        $threadId = $stm->fetchColumn();
        $sql      = "UPDATE `ForumThread` SET `lastPostId` = $postId, `lastPostBy` = $postedBy,
                     `lastPostAt` = $postedAt WHERE `id` = $threadId";
        $pdo->query($sql);
      }
      $sql = "INSERT INTO `ForumPostInThread` (`postId`, `threadId`) VALUES ($postId, $threadId)";
      $pdo->query($sql);
      $cache = new Cache(
                 (object) array(
                   'directory' => $cnf->forum->cache->directory
                 )
               );
      $cache->remove('recent');
      $username = $userList[$postedBy]->username;
      $data     = "<a href=\"#\" class=\"profile-link\" data-userId=\"$postedBy\">$username</a> mentioned"
                . " you in a <a href=\"#\" class=\"forum-post-link\" data-postId=\"$postId\">forum post</a>.";
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
      if ($inReplyTo > 1) {
        $sql       = "SELECT `usr`.`id` AS `recipient`
                           FROM `ForumPost` AS `pst`
                      LEFT JOIN `User`      AS `usr` ON `usr`.`id` = `pst`.`postedBy`
                      WHERE `pst`.`id` = $inReplyTo";
        $stm       = $pdo->query($sql);
        $recipient = $stm->fetchColumn();
        $data      = "<a href=\"#\" class=\"profile-link\" data-userId=\"$postedBy\">$username</a>"
                   . " <a href=\"#\" class=\"forum-post-link\" data-postId=\"$postId\">replied</a> to"
                   . " <a href=\"#\" class=\"forum-post-link\" data-postId=\"$inReplyTo\">your forum post</a>.";
        Alerts::enqueue(
          (object) array(
            'typeId'    => 2,
            'recipient' => $recipient,
            'private'   => true,
            'data'      => $data
          )
        );
      }
      return $postId;
    } // postMessage

    public static function search($terms)
    {
      $cnf   = Config::instance();
      $cache = new Cache(
                 (object) array(
                   'directory' => $cnf->search->cache->directory,
                   'ttl'       => $cnf->search->cache->ttl
                 )
               );
      $key   = "forum_" . md5($terms);
      if ($cache->exists($key)) {
        return $cache->fetch($key);
      }
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,  false);
      $pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
      $_terms = $pdo->quote('%' . $terms . '%', PDO::PARAM_STR);
      $sql    = "SELECT `thr`.`id`        AS `threadId`,
                        `thr`.`topic`     AS `topic`,
                        `thr`.`startedBy` AS `startedBy`,
                        `sus`.`username`  AS `starter`,
                        `pst`.`id`        AS `postId`
                      FROM `ForumPost`         AS `pst`
                 LEFT JOIN `ForumPostInThread` AS `pit` ON `pit`.`postId` = `pst`.`id`
                 LEFT JOIN `ForumThread`       AS `thr` ON `thr`.`id`     = `pit`.`threadId`
                 LEFT JOIN `User`              AS `sus` ON `sus`.`id`     = `thr`.`startedBy`
                 WHERE `pst`.`body` LIKE $_terms OR `pst`.`topic` LIKE $_terms";
      $stm    = $pdo->query($sql);
      $relevant  = array();
      $threadIds = array();
      $threads   = array();
      while (($row = $stm->fetchObject()) !== false) {
        $relevant[] = intval($row->postId);
        $threadId   = intval($row->threadId);
        if (!in_array($threadId, $threadIds)) {
          $threadIds[] = $threadId;
          $threads[]   = $row;
        }
      }
      if (empty($threadIds)) {
        $data = (object) array(
                  'threads'  => array(),
                  'posts'    => array(),
                  'relevant' => array()
                );
        $cache->store($key, $data);
        return $data;
      }
      $sql   = "SELECT `pst`.`id`                   AS `postId`,
                       `pit`.`threadId`             AS `inThread`,
                       IFNULL(`rpl`.`inReplyTo`, 0) AS `inReplyTo`,
                       `pst`.`postedAt`             AS `postedAt`,
                       `pst`.`postedBy`             AS `postedBy`,
                       `usr`.`username`             AS `author`,
                       `pfl`.`signature`            AS `signature`,
                       `pfl`.`avatar`               AS `avatar`,
                       `pst`.`topic`                AS `topic`,
                       `pst`.`rendered`             AS `rendered`
                     FROM `ForumPost`         AS `pst`
                LEFT JOIN `ForumPostReply`    AS `rpl` ON `rpl`.`postId` = `pst`.`id`
                LEFT JOIN `ForumPostInThread` AS `pit` ON `pit`.`postId` = `pst`.`id`
                LEFT JOIN `User`              AS `usr` ON `usr`.`id`     = `pst`.`postedBy`
                LEFT JOIN `Profile`           AS `pfl` ON `pfl`.`userId` = `usr`.`id`
                WHERE `pit`.`threadId` IN (" . implode(",", $threadIds) . ") ORDER BY `pst`.`id`";
      $stm   = $pdo->query($sql);
      $posts = $stm->fetchAll(PDO::FETCH_OBJ);
      $tree  = new Tree();
      $tree->importStore('postId', 'inReplyTo', $posts);
      $results = array();
      foreach ($relevant as $postId) {
        $limb    = $tree->getNodeById($postId)->getLimb();
        $results = array_merge($results, $limb->toArray());
      }
      $data = (object) array(
                'threads'  => $threads,
                'posts'    => $results,
                'relevant' => $relevant
              );
      $cache->store($key, $data);
      return $data;
    } // search
/*
    public static function hashtagSearch($tag)
    {
      $cnf   = Config::instance();
      $cache = new Cache(
                 (object) array(
                   'directory' => $cnf->search->cache->directory,
                   'ttl'       => $cnf->search->cache->ttl
                 )
               );
      $key   = "forum_hashtag_" . md5($tag);
      if ($cache->exists($key)) {
        return $cache->$key;
      }
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,  false);
      $pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
      $tag  = $pdo->quote('%' . $tag . '%', PDO::PARAM_STR);
      $sql  = "SELECT `pst`.`id`                   AS `postId`,
                      `pit`.`threadId`             AS `inThread`,
                      IFNULL(`rpl`.`inReplyTo`, 0) AS `inReplyTo`,
                      `pst`.`postedAt`             AS `postedAt`,
                      `pst`.`postedBy`             AS `postedBy`,
                      `usr`.`username`             AS `author`,
                      `pfl`.`signature`            AS `signature`,
                      `pfl`.`avatar`               AS `avatar`,
                      `pst`.`topic`                AS `topic`,
                      `pst`.`rendered`             AS `rendered`
                    FROM `ForumPost`         AS `pst`
               LEFT JOIN `ForumPostReply`    AS `rpl` ON `rpl`.`postId` = `pst`.`id`
               LEFT JOIN `ForumPostInThread` AS `pit` ON `pit`.`postId` = `pst`.`id`
               LEFT JOIN `User`              AS `usr` ON `usr`.`id`     = `pst`.`postedBy`
               LEFT JOIN `Profile`           AS `pfl` ON `pfl`.`userId` = `usr`.`id`
               WHERE `pst`.`topic` LIKE $tag OR `pst`.`body` LIKE $tag
               ORDER BY `pst`.`postedAt` DESC";
      $stm  = $pdo->query($sql);
      $data = $stm->fetchAll(PDO::FETCH_OBJ);
      $cache->store($key, $data);
      return $data;
    } // hashtagSearch
*/
    public static function deleteMessage($postId, $deleteThread = false)
    {
      $user = new User($_SESSION['userId']);
      if ($user->accessLevel < 4) {
        self::$lastError = "You don't have permission to do that.";
        return false;
      }
      $cnf        = Config::instance();
      $pdo        = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $postId     = intval($postId);
      $sql        = "SELECT `threadId`, `lastPostId`, `lastPostBy`, `lastPostAt`
                     FROM `Forum` WHERE `postId` = $postId ORDER BY `postedAt` DESC LIMIT 2";
      $stm        = $pdo->query($sql);
      $lastPost   = $stm->fetch(PDO::FETCH_OBJ);
      $nextToLast = $stm->fetch(PDO::FETCH_OBJ);
      $threadId   = $lastPost->threadId;
      if ($threadId == $postId) {
        if ($deleteThread == false) {
          self::$lastError = "This is a top post. If you delete it, the whole thread will be deleted. Continue?";
          return false;
        }
        $sql     = "SELECT GROUP_CONCAT(`postId`) AS `postIds` FROM `ForumPostInThread` WHERE `threadId` = $threadId";
        $stm     = $pdo->query($sql);
        $postIds = $stm->fetchColumn();
        $sql     = "DELETE FROM `ForumPostInThread` WHERE `postId` IN ($postIds)";
        $pdo->query($sql);
        $sql = "DELETE FROM `ForumPost` WHERE `postId` IN ($postIds)";
        $pdo->query($sql);
        $sql = "DELETE FROM `ForumThread` WHERE `id` = $threadId";
        $pdo->query($sql);
        $cache = new Cache(
                   (object) array(
                     'directory' => $cnf->forum->cache->directory
                   )
                 );
        $cache->remove('recent');
        $cache = new Cache(
                   (object) array(
                     'directory' => $cnf->search->cache->directory
                   )
                 );
        foreach ($cache->findKeys('forum_*') as $key) {
          $cache->remove($key);
        }
        return true;
      }
      if ($lastPost->postId == $postId) {
        $lastPostId = $nextToLast->postId;
        $lastPostBy = $nextToLast->postedBy;
        $lastPostAt = $nextToLast->postedAt;
        $sql        = "UPDATE `ForumThread` SET `lastPostId` = $lastPostId, `lastPostBy` = $lastPostBy,
                       `LastPostAt` = $lastPostAt WHERE `id` = $threadId";
        $pdo->query($sql);
      }
      $sql = "DELETE FROM `ForumPostInThread` WHERE `postId` = $postId";
      $pdo->query($sql);
      $sql = "DELETE FROM `ForumPost` WHERE `id` = $postId";
      $pdo->query($sql);
      $cache = new Cache(
                 (object) array(
                   'directory' => $cnf->forum->cache->directory
                 )
               );
      $cache->remove('recent');
      $cache = new Cache(
                 (object) array(
                   'directory' => $cnf->search->cache->directory
                 )
               );
      foreach ($cache->findKeys('forum_*') as $key) {
        $cache->remove($key);
      }
      return true;
    } // deleteMessage

    public static function deleteThread($threadId) {
      $user = new User($_SESSION['userId']);
      if ($user->accessLevel < 4) {
        self::$lastError = "You don't have permission to do that.";
        return false;
      }
      return $this->deleteMessage($threadId, true);
    } // deleteThread

    public static function lockThread($threadId)
    {
      $user = new User($_SESSION['userId']);
      if ($user->accessLevel < 4) {
        self::$lastError = "You don't have permission to do that.";
        return false;
      }
      $cnf = Config::instance();
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $sql = "UPDATE `ForumThread` SET `locked` = 1 WHERE `threadId` = " . intval($threadId);
      $pdo->query($sql);
    } // lockThread

    public static function unlockThread($threadId)
    {
      $user = new User($_SESSION['userId']);
      if ($user->accessLevel < 4) {
        self::$lastError = "You don't have permission to do that.";
        return false;
      }
      $cnf = Config::instance();
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $sql = "UPDATE `ForumThread` SET `locked` = 0 WHERE `threadId` = " . intval($threadId);
      $pdo->query($sql);
    } // unlockThread

    public static function getLastError()
    {
      $message = self::$lastError;
      self::$lastError = "";
      return $message;
    } // getLastError

  } // Forum

?>