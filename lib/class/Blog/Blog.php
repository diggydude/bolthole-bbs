<?php

  require_once(__DIR__ . '/../System/Config.php');
  require_once(__DIR__ . '/../User/User.php');
  require_once(__DIR__ . '/BlogPost.php');

  class Blog
  {

    protected

      $id,
      $ownerId;

    public function __construct($id = 0)
    {
      $this->id      = 0;
      $this->ownerId = 0;
      if ($id > 0) {
        $this->load($id);
      }
    } // __construct

    public static function create($params)
    {
      $blog          = new Blog();
      $blog->ownerId = (isset($params['ownerId'])) ? $params['ownerId'] : 0;
      $blog->save();
      return $blog;
    } // create

    public function load($id)
    {
      $cnf = Config::instance();
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $id  = intval($id);
      $sql = "SELECT * FROM `Blog` WHERE `id` = $id";
      $stm = $pdo->query($sql);
      $row = $stm->fetch(PDO::FETCH_ASSOC);
      if ($row) {
        $this->id      = $row['id'];
        $this->ownerId = $row['ownerId'];
        return;
      }
      throw new Exception(__METHOD__ . ' > Failed fetching row ' . $id . '.');
    } // load

    public function save()
    {
      $cnf     = Config::instance();
      $pdo     = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $id      = intval($this->id);
      $ownerId = intval($this->ownerId);
      if ($id > 0) {
        $sql = "UPDATE `Blog` SET `ownerId` = $ownerId"
             . " WHERE `id` = $id";
        $pdo->query($sql);
      }
      else {
        $sql = "INSERT INTO `Blog` (`ownerId`)"
             . " VALUES($ownerId)";
        $pdo->query($sql);
        $this->id = $pdo->lastInsertId();
      }
      return $this->id;
    } // save

    public function delete()
    {
      $cnf = Config::instance();
      $pdo = new PDO($cnf->db->dsn, $cnf->db->username, $cnf->db->password);
      $sql = "DELETE FROM `Comment` WHERE `id` IN ("
           . "  SELECT `co`.`id` AS `id` FROM `Blog` AS `bl`"
           . "  LEFT JOIN `BlogPost` AS `bp` ON `bp`.`inBlog`   = `bl`.`id`"
           . "  LEFT JOIN `Comment`  AS `co` ON `co`.`moduleId` = `bp`.`id`"
           . "  WHERE `bp`.`inBlog` = $id AND `co`.`moduleTypeId` = 2"
           . ")";
      $pdo->query($sql);
      $sql = "DELETE FROM `BlogPost` WHERE `inBlog` = $id";
      $pdo->query($sql);
      $sql = "DELETE FROM `Blog` WHERE `id` = $id";
      $pdo->query($sql);
      $this->id      = 0;
      $this->title   = "";
      $this->ownerId = 0;
    } // delete

    public function getOwner()
    {
      return new User($this->ownerId);
    } // getOwner

    public function listPosts()
    {
      return BlogPost::listPosts($this->id);
    } // listPosts

    public function addPost($params)
    {
      $post = BlogPost::create(
                array(
                  'inBlog'   => $this->id,
                  'title'    => $params->title,
                  'body'     => $params->body,
                  'postedAt' => gmdate('Y-m-d H:i:s')
                )
              );
      return $post;
    } // addPost

    public function deletePost($postId)
    {
      $post = new BlogPost($postId);
      $post->delete();
    } // deletePost

    public function __get($property)
    {
      return (isset($this->$property)) ? $this->$property : null;
    } // __get

  } // Blog

?>