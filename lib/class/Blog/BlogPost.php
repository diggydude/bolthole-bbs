<?php

  require_once(__DIR__ . '/../System/Config.php');
  require_once(__DIR__ . '/../System/Cache.php');
  require_once(__DIR__ . '/Blog.php');
  require_once(__DIR__ . '/../User/User.php');
  require_once(__DIR__ . '/../Messaging/Emoticons.php');
  require_once(__DIR__ . '/../Messaging/Comment.php');
  require_once(__DIR__ . '/../Messaging/Alerts.php');
  require_once(__DIR__ . '/../Markup/MessageParser.php');

  class BlogPost
  {

    protected

      $id,
      $title,
      $postedAt,
      $editedAt,
      $body,
      $rendered,
      $inBlog;

    public function __construct($id = 0)
    {
      $this->id       = 0;
      $this->title    = "";
      $this->postedAt = "0000-00-00 00:00:00";
      $this->editedAt = "0000-00-00 00:00:00";
      $this->body     = "";
      $this->rendered = "";
      $this->inBlog   = 0;
      if ($id > 0) {
        $this->load($id);
      }
    } // __construct

    public static function create($params)
    {
      $post           = new BlogPost();
      $post->title    = (isset($params['title']))    ? $params['title']    : "";
      $post->postedAt = (isset($params['postedAt'])) ? $params['postedAt'] : gmdate('Y-m-d H:i:s');
      $post->editedAt = (isset($params['editedAt'])) ? $params['editedAt'] : "0000-00-00 00:00:00";
      $post->body     = (isset($params['body']))     ? $params['body']     : "";
      $post->inBlog   = (isset($params['inBlog']))   ? $params['inBlog']   : 0;
      $post->save();
      $blog  = new Blog($post->inBlog);
      $owner = $blog->getOwner();
      Alerts::enqueue(
        (object) array(
          'typeId'    => 11,
          'private'   => false,
          'recipient' => 1,
          'data'      => "<a href=\"#\" class=\"profile-link\" data-userId=\"" . $owner->id . "\">" . $owner->username  . "</a>"
                       . " made a new <a href=\"#\" class=\"blog-post-link\" data-postId=\"" . $post->id . "\">blog post</a>."
        )
      );
      return $post;
    } // create

    public function load($id)
    {
      $cnf   = Config::instance();
      $cache = new Cache(
                 (object) array(
                   'directory' => $cnf->profiles->cache->directory
                 )
               );
      $key   = "blog_post_" . $id;
      if ($cache->exists($key)) {
        $row = $cache->fetch($key);
      }
      else {
        $pdo  = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
        $id   = intval($id);
        $sql  = "SELECT * FROM `BlogPost` WHERE `id` = $id";
        $stm  = $pdo->query($sql);
        $row  = $stm->fetch(PDO::FETCH_ASSOC);
		$cache->store($key, $row);
	  }
      if ($row) {
        $this->id       = $row['id'];
        $this->title    = $row['title'];
        $this->postedAt = $row['postedAt'];
        $this->editedAt = $row['editedAt'];
        $this->body     = $row['body'];
        $this->rendered = $row['rendered'];
        $this->inBlog   = $row['inBlog'];
        return;
      }
    } // load

    public function save()
    {
      $cnf            = Config::instance();
      $cache          = new Cache(
                          (object) array(
                            'directory' => $cnf->profiles->cache->directory
                          )
                        );
      $pdo            = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $id             = intval($this->id);
      $title          = $pdo->quote($this->title,    PDO::PARAM_STR);
      $postedAt       = $pdo->quote($this->postedAt, PDO::PARAM_STR);
      $editedAt       = $pdo->quote($this->editedAt, PDO::PARAM_STR);
      $body           = $pdo->quote($this->body,     PDO::PARAM_STR);
      $userList       = User::listUsers();
      $emotes         = Emoticons::instance();
      $options        = array(
                           'allowedTags'       => array(),
                           'userList'          => $userList,
                           'emoticonList'      => $emotes->listIcons(),
                           'openLinksInNewTab' => true
                         );
      $mentioned      = array();
      $this->rendered = MessageParser::parse($this->body, $mentioned, $options);
      $rendered       = $pdo->quote($this->rendered, PDO::PARAM_STR);
      $inBlog         = intval($this->inBlog);
      if ($id > 0) {
        $sql = "UPDATE `BlogPost` SET `title` = $title, `postedAt` = $postedAt,"
             . " `editedAt` = $editedAt, `body` = $body,"
             . " `rendered` = $rendered, `inBlog` = $inBlog WHERE `id` = $id";
        $pdo->query($sql);
      }
      else {
        $sql = "INSERT INTO `BlogPost` ("
             . " `title`, `postedAt`, `editedAt`, `body`, `rendered`, `inBlog`)"
             . " VALUES ($title, $postedAt, $editedAt, $body, $rendered, $inBlog)";
        $pdo->query($sql);
        $this->id = $pdo->lastInsertId();
      }
      $key = "blog_post_" . $this->id;
	  $cache->remove($key);
	  $key = "blog_posts_" . $this->inBlog;
	  $cache->remove($key);
      if (!empty($mentioned)) {
        $blog  = new Blog($inBlog);
        $owner = $blog->getOwner();
        $data  = "<a href=\"#\" class=\"profile-link\" data-userId=\"" . $owner->id . "\">" . $owner->username . "</a> mentioned"
               . " you in a <a href=\"#\" class=\"blog-post-link\" data-postId=\"" . $this->id . "\">blog post</a>.";
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
      }
      return $this->id;
    } // save

    public function delete()
    {
      $cnf   = Config::instance();
      $cache = new Cache(
                 (object) array(
                   'directory' => $cnf->profiles->cache->directory
                 )
               );
	  $key   = "blog_post_" . $this->id;
	  $cache->remove($key);
	  $key   = "blog_posts_" . $this->inBlog;
	  $cache->remove($key);
      $pdo   = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $id    = intval($this->id);
      $sql   = "DELETE FROM `Comment` WHERE `moduleTypeId` = 2 AND `moduleId` = $id";
      $pdo->query($sql);
      $sql = "DELETE FROM `BlogPost` WHERE `id` = $id";
      $pdo->query($sql);
      $this->id       = 0;
      $this->title    = "";
      $this->postedAt = "0000-00-00 00:00:00";
      $this->editedAt = "0000-00-00 00:00:00";
      $this->body     = "";
      $this->inBlog   = 0;
	  $cache->remove($this->id);
    } // delete

    public function edit($title, $body)
    {
      $this->title    = $title;
      $this->body     = $body;
      $this->editedAt = gmdate('Y-m-d H:i:s');
      $this->save();
    } // edit

    public function listComments()
    {
      return Comment::listComments($this->id, 2);
    } // listComments

    public static function listPosts($blogId)
    {
      $cnf   = Config::instance();
      $cache = new Cache(
                 (object) array(
                   'directory' => $cnf->profiles->cache->directory
                 )
               );
	  $key   = "blog_posts_" . $blogId;
	  if ($cache->exists($key)) {
		return $cache->fetch($key);
	  }
      $pdo    = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $blogId = intval($blogId);
      $sql    = "SELECT `blp`.`id`        AS `postId`,
                        `blp`.`title`     AS `title`,
                        `blp`.`postedAt`  AS `postedAt`,
                        `blp`.`editedAt`  AS `editedAt`,
                        `blp`.`body`      AS `body`,
                        `blp`.`rendered`  AS `rendered`,
                        `usr`.`id`        AS `postedBy`,
                        `usr`.`username`  AS `author`,
                        `pfl`.`title`     AS `authorTitle`,
                        `pfl`.`avatar`    AS `avatar`,
                        `pfl`.`signature` AS `signature`
                      FROM `BlogPost` AS `blp`
                 LEFT JOIN `Blog`     AS `blg` ON `blg`.`id`     = `blp`.`inBlog`
                 LEFT JOIN `User`     AS `usr` ON `usr`.`id`     = `blg`.`ownerId`
                 LEFT JOIN `Profile`  AS `pfl` ON `pfl`.`userId` = `usr`.`id`
                 WHERE `blg`.`id` = $blogId ORDER BY `postedAt` DESC LIMIT 100";
      $stm    = $pdo->query($sql);
      $posts  = $stm->fetchAll(PDO::FETCH_OBJ);
      foreach ($posts as &$post) {
        $summary = preg_replace('/\[\/?(?:i|b|u|url|img|color|size|code|blink|marquee|rainbow|gradient|youtube|ansi)*?.*?\]/', '', $post->body);
        $summary = substr($summary, 0, 255);
        $summary = substr($summary, 0, strripos($summary, " ")) . "...";
        $post->summary = $summary;
        unset($post->body);
      }
	  $cache->store($key, $posts);
      return $posts;
    } // listPosts

    public function __get($property)
    {
      return (isset($this->$property)) ? $this->$property : null;
    } // __get

  } // BlogPost