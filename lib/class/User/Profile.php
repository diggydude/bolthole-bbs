<?php

  require_once(__DIR__ . '/../System/Config.php');
  require_once(__DIR__ . '/../System/Cache.php');
  require_once(__DIR__ . '/User.php');
  require_once(__DIR__ . '/../Messaging/Emoticons.php');
  require_once(__DIR__ . '/../Blog/Blog.php');
  require_once(__DIR__ . '/../Messaging/Comment.php');
  require_once(__DIR__ . '/../Markup/MessageParser.php');

  class Profile
  {

    protected

      $userId,
      $displayName,
      $title,
      $avatar,
      $banner,
      $signature,
      $website,
      $about,
      $rendered,
      $blogId,
      $libraryId;

    public function __construct($userId = 0)
    {
      $this->userId      = 0;
      $this->displayName = "";
      $this->title       = "";
      $this->avatar      = "";
      $this->banner      = "";
      $this->signature   = "";
      $this->website     = "";
      $this->about       = "";
      $this->rendered    = "";
      $this->blogId      = 0;
      $this->libraryId   = 0;
      if ($userId > 0) {
        $this->load($userId);
      }
    } // __construct

    public function load($userId)
    {
      $cnf   = Config::instance();
      $cache = new Cache(
                 (object) array(
                   'directory' => $cnf->profiles->cache->directory
                 )
               );
      $key   = "profile_" . $userId;
      if ($cache->exists($key)) {
        $row = $cache->fetch($key);
      }
      else {
        $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
        $sql = "SELECT `pfl`.`userId`      AS `userId`,
                       `pfl`.`displayName` AS `displayName`,
                       `pfl`.`title`       AS `title`,
                       `pfl`.`avatar`      AS `avatar`,
                       `pfl`.`banner`      AS `banner`,
                       `pfl`.`signature`   AS `signature`,
                       `pfl`.`website`     AS `website`,
                       `pfl`.`about`       AS `about`,
                       `pfl`.`rendered`    AS `rendered`,
                       `blg`.`id`          AS `blogId`,
                       `lib`.`id`          AS `libraryId`
                     FROM `Profile` AS `pfl`
                LEFT JOIN `Blog`    AS `blg` ON `blg`.`ownerId` = `pfl`.`userId`
                LEFT JOIN `Library` AS `lib` ON `lib`.`ownerId` = `pfl`.`userId`
                WHERE `pfl`.`userId` = " . intval($userId);
        $stm = $pdo->query($sql);
        $row = $stm->fetchObject();
        $cache->store($key, $row);
      }
      if ($row) {
        $this->userId      = $row->userId;
        $this->displayName = $row->displayName;
        $this->title       = $row->title;
        $this->avatar      = $row->avatar;
        $this->banner      = $row->banner;
        $this->signature   = $row->signature;
        $this->website     = $row->website;
        $this->about       = $row->about;
        $this->rendered    = $row->rendered;
        $this->blogId      = $row->blogId;
        $this->libraryId   = $row->libraryId;
      }
    } // load

    public function save()
    {
      $cnf         = Config::instance();
      $cache       = new Cache(
                       (object) array(
                         'directory' => $cnf->profiles->cache->directory
                       )
                     );
      $key         = "profile_" . $this->userId;
      $pdo         = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $displayName = $pdo->quote($this->displayName, PDO::PARAM_STR);
      $title       = $pdo->quote($this->title,       PDO::PARAM_STR);
      $avatar      = $pdo->quote($this->avatar,      PDO::PARAM_STR);
      $banner      = $pdo->quote($this->banner,      PDO::PARAM_STR);
      $signature   = $pdo->quote($this->signature,   PDO::PARAM_STR);
      $website     = $pdo->quote($this->website,     PDO::PARAM_STR);
      $about       = $pdo->quote($this->about,       PDO::PARAM_STR);
      $userList    = User::listUsers();
      $emotes      = Emoticons::instance();
      $options     = array(
                        'allowedTags'       => array(),
                        'userList'          => $userList,
                        'emoticonList'      => $emotes->listIcons(),
                        'openLinksInNewTab' => true
                      );
      $mentioned   = array();
      $this->rendered = MessageParser::parse($this->about, $mentioned, $options);
      $rendered = $pdo->quote($this->rendered, PDO::PARAM_STR);
      $sql      = "UPDATE `Profile` SET `displayName` = $displayName, `title` = $title, `avatar` = $avatar, `banner` = $banner,
                   `signature` = $signature, `website` = $website, `about` = $about, `rendered` = $rendered
                   WHERE `userId` = " . intval($this->userId);
      $pdo->query($sql);
      $cache->remove($key);
      $key = "users";
      $cache->remove($key);
      return $this->userId;
    } // save

    public function update($params)
    {

      if (property_exists($params, 'displayName')) {
        $this->displayName = $params->displayName;
      }
      if (property_exists($params, 'title')) {
        $this->title = $params->title;
      }
      if (property_exists($params, 'avatar')) {
        $this->avatar = $params->avatar;
      }
      if (property_exists($params, 'banner')) {
        $this->banner = $params->banner;
      }
      if (property_exists($params, 'signature')) {
        $this->signature = $params->signature;
      }
      if (property_exists($params, 'website')) {
        $this->website = $params->website;
      }
      if (property_exists($params, 'about')) {
        $this->about = $params->about;
      }
      $this->save();
    } // update

    public function delete()
    {
      $userId = intval($this->userId);
      $cnf    = Config::instance();
      $cache  = new Cache(
                  (object) array(
                    'directory' => $cnf->profiles->cache->directory
                  )
                );
      $key    = "profile_" . $userId;
      $pdo    = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $sql    = "DELETE FROM `Profile` WHERE `userId` = $userId";
      $pdo->query($sql);
      $this->userId      = 0;
      $this->displayName = "";
      $this->title       = "";
      $this->avatar      = "";
      $this->banner      = "";
      $this->signature   = "";
      $this->website     = "";
      $this->about       = "";
      $this->rendered    = "";
      $cache->remove($key);
      $key = "users";
      $cache->remove($key);
    } // delete

    public function getUser()
    {
      return new User($this->userId);
    } // getUser

    public function listBlogPosts()
    {
      $blog = new Blog($this->blogId);
      return $blog->listPosts();
    } // listBlogPosts

    public function listFiles($limit = 100)
    {
      $cnf   = Config::instance();
      $cache = new Cache(
                 (object) array(
                   'directory' => $cnf->profiles->cache->directory
                 )
               );
      $key   = "files_" . $this->userId;
      if ($cache->exists($key)) {
        return $cache->fetch($key);
      }
      $pdo    = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $userId = intval($this->userId);
      $sql    = "SELECT `flb`.`id`       AS `id`,
                        `fil`.`id`       AS `fileId`,
                        `fil`.`mimeType` AS `mimeType`,
                        `fil`.`size`     AS `size`,
                        `fil`.`hash`     AS `hash`,
                        `flb`.`filename` AS `filename`,
                        `flb`.`description` AS `description`,
                        `flb`.`uploadedAt`  AS `uploadedAt`,
                        `flb`.`downloads`   AS `downloads`
                      FROM `File`          AS `fil`
                 LEFT JOIN `FileInLibrary` AS `flb` ON `flb`.`fileId` = `fil`.`id`
                 LEFT JOIN  `Library`      AS `lib` ON `lib`.`id`     = `flb`.`libraryId`
                 WHERE `lib`.`ownerId` = $userId
                 ORDER BY `flb`.`uploadedAt` DESC LIMIT " . intval($limit);
      $stm    = $pdo->query($sql);
      $files  = $stm->fetchAll(PDO::FETCH_OBJ);
      $cache->store($key, $files);
      return $files;
    } // listFiles

    public function listComments()
    {
      return Comment::listComments($this->userId, 1);
    } // listComments

    public static function search($terms, $userIds = array())
    {
      $cnf     = Config::instance();
      $cache   = new Cache(
                   (object) array(
                     'directory' => $cnf->search->cache->directory,
                     'ttl'       => $cnf->search->cache->ttl
                   )
                 );
      $key     = "profiles_" . md5($terms);
      if ($cache->exists($key)) {
        return $cache->fetch($key);
      }
      $pdo     = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $terms   = $pdo->quote('%' . $terms . '%', PDO::PARAM_STR);
      $sql     = "SELECT GROUP_CONCAT(DISTINCT `usr`.`id`) AS `userIds`
                  FROM `User`         AS `usr`
                  LEFT JOIN `Profile` AS `pfl` ON `pfl`.`userId` = `usr`.`id`
                  WHERE (`usr`.`username`    LIKE $terms
                      OR `pfl`.`displayName` LIKE $terms
                      OR `pfl`.`title`       LIKE $terms
                      OR `pfl`.`signature`   LIKE $terms
                      OR `pfl`.`about`       LIKE $terms)";
      if (!empty($userIds)) {
        $sql .= " AND `pfl`.`userId` IN (" . implode(",", array_map('intval', $userIds)) . ")";
      }
      $stm     = $pdo->query($sql);
      $userIds = $stm->fetchColumn();
      if (strlen($userIds) < 1) {
        return array();
      }
      $sql   = "SELECT `usr`.`id`          AS `userId`,
                       `usr`.`username`    AS `username`,
                       `usr`.`joined`      AS `joined`,
                       `pfl`.`avatar`      AS `avatar`,
                       `pfl`.`banner`      AS `banner`,
                       `pfl`.`title`       AS `title`,
                       `pfl`.`displayName` AS `displayName`,
                       `blg`.`id`          AS `blogId`,
                       `lib`.`id`          AS `libraryId`
                     FROM `User`    AS `usr`
                LEFT JOIN `Profile` AS `pfl` ON `pfl`.`userId`  = `usr`.`id`
                LEFT JOIN `Blog`    AS `blg` ON `blg`.`ownerId` = `usr`.`id`
                LEFT JOIN `Library` AS `lib` ON `lib`.`ownerId` = `usr`.`id`
                WHERE `usr`.`id` IN ($userIds) ORDER BY `usr`.`username`";
      $stm   = $pdo->query($sql);
      $rows  =  $stm->fetchAll(PDO::FETCH_OBJ);
      $users = array();
      foreach ($rows as $row) {
        $users[$row->userId] = $row;
      }
      $cache->store($key, $users);
      return $users;
    } // search

    public function __get($prop)
    {
      return (property_exists($this, $prop)) ? $this->$prop : null;
    } // __get

  } // Profile

?>