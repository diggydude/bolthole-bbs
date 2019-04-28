<?php

  require_once(__DIR__ . '/conf/init_http.php');
  require_once(__DIR__ . '/lib/class/User/Profile.php');
  require_once(__DIR__ . '/lib/class/Blog/Blog.php');
  require_once(__DIR__ . '/lib/class/Blog/BlogPost.php');
  require_once(__DIR__ . '/lib/class/Messaging/Alerts.php');

  $response = (object) array(
                'success' => false,
                'message' => '',
                'results' => null
              );

  if ($_POST) {
    switch ($_POST['command']) {
      case "getOwnerId":
        $blog    = new Blog($_POST['blogId']);
        $ownerId = $blog->ownerId;
        $response->success = true;
        $response->results = (object) array(
                               'ownerId' => $ownerId
                             );
        break;
      case "getPost":
        $post     = new BlogPost($_POST['postId']);
        $comments = $post->listComments();
        $blog     = new Blog($post->inBlog);
        $owner    = $blog->getOwner();
        $profile  = $owner->getProfile();
        $response->success = true;
        $response->results = (object) array(
                               'postId'      => $post->id,
                               'title'       => $post->title,
                               'body'        => $post->body,
                               'rendered'    => $post->rendered,
                               'editedAt'    => $post->editedAt,
                               'postedAt'    => $post->postedAt,
                               'postedBy'    => $owner->id,
                               'author'      => $owner->username,
                               'authorTitle' => $profile->title,
                               'avatar'      => $profile->avatar,
                               'signature'   => $profile->signature,
                               'comments'    => $comments
                             );
        break;
      case "savePost":
        if (intval($_POST['postId']) == 0) {
          $post = BlogPost::create(
                    array(
                      'inBlog' => $_POST['inBlog'],
                      'title'  => $_POST['title'],
                      'body'   => $_POST['body']
                    )
                  );
        }
        else {
          $post = new BlogPost($_POST['postId']);
          $post->edit(
            array(
              'title' => $_POST['title'],
              'body'  => $_POST['body']
            )
          );
        }
        $comments = $post->listComments();
        $blog     = new Blog($post->inBlog);
        $owner    = $blog->getOwner();
        $profile  = $owner->getProfile();
        $response->success = true;
        $response->message = "Your blog post has been saved.";
        $response->results = (object) array(
                               'postId'      => $post->id,
                               'title'       => $post->title,
                               'body'        => $post->body,
                               'rendered'    => $post->rendered,
                               'editedAt'    => $post->editedAt,
                               'postedAt'    => $post->postedAt,
                               'postedBy'    => $owner->id,
                               'author'      => $owner->username,
                               'authorTitle' => $profile->title,
                               'avatar'      => $profile->avatar,
                               'signature'   => $profile->signature,
                               'comments'    => $comments
                             );
        break;
      case "postComment":
        $comment  = Comment::create(
                      array(
                        'moduleId'     => $_POST['postId'],
                        'moduleTypeId' => 2,
                        'postedBy'     => $_POST['postedBy'],
                        'postedAt'     => gmdate('Y-m-d H:i:s'),
                        'body'         => $_POST['body']
                      )
                    );
        $post     = new BlogPost($_POST['postId']);
        $comments = $post->listComments();
        $blog     = new Blog($post->inBlog);
        $owner    = $blog->getOwner();
        $profile  = $owner->getProfile();
        $response->success = true;
        $response->message = "Your comment has been posted.";
        $response->results = (object) array(
                               'postId'      => $post->id,
                               'title'       => $post->title,
                               'body'        => $post->body,
                               'rendered'    => $post->rendered,
                               'editedAt'    => $post->editedAt,
                               'postedAt'    => $post->postedAt,
                               'postedBy'    => $owner->id,
                               'author'      => $owner->username,
                               'authorTitle' => $profile->title,
                               'avatar'      => $profile->avatar,
                               'signature'   => $profile->signature,
                               'comments'    => $comments
                             );
        $commenter = new User($_POST['postedBy']);
        $username  = $commenter->username;
        Alerts::enqueue(
          (object) array(
            'typeId'    => 4,
            'private'   => true,
            'recipient' => $owner->id,
            'data'      => "<a href=\"#\" class=\"profile-link\" data-userId=\"" . $commenter->id . "\">". $commenter->username . "</a>"
			             . " commented on <a href=\"#\" class=\"blog-post-link\" data-postId=\"" . $post->id . "\">your blog post</a>."
          )
        );
        break;
      case "search":
        $posts = Blog::search($_POST['terms']);
        $response->success = true;
        $response->results = (object) array(
                               'posts' => $posts
                             );
        break;
      default:
        $response->message = "Unsupported command: \"" . $_POST['command'] . "\".";
        break;
    }
  }

  header('Content-Type: application/json');
  echo json_encode($response);
  exit(0);

?>