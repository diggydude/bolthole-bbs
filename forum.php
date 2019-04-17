<?php

  require_once(__DIR__ . '/conf/init_http.php');
  require_once(__DIR__ . '/lib/class/Forum/Forum.php');

  $response = (object) array(
                'success' => false,
                'message' => '',
                'results' => null
              );

  if ($_POST) {
    switch ($_POST['command']) {
	  case "getRecent":
		$response->success = true;
        $response->results = Forum::getRecent();
        break;
      case "postMessage":
        $postId = Forum::postMessage(
                    (object) array(
                      'inReplyTo' => $_POST['inReplyTo'],
                      'postedBy'  => $_SESSION['userId'],
                      'topic'     => $_POST['topic'],
                      'body'      => $_POST['body']
                    )
                  );
        if ($postId == false) {
          $response->message = Forum::getLastError();
          break;
        }
		$data = Forum::getRecent();
        $response->success = true;
        $response->message = "Your message was posted.";
        $response->results = (object) array(
                               'postId'  => $postId,
							   'threads' => $data->threads,
							   'posts'   => $data->posts
                             );
        break;
	  case "search":
		$response->success = true;
        $response->results = Forum::search($_POST['terms']);
        break;
      case "deletePost":
        if (Forum::deleteMessage($_POST['postId']) === false) {
          $response->message = Forum::getLastError();
          break;
        }
        $response->success = true;
        $response->message = "Post $postId was deleted.";
        break;
      case "deleteThread":
        if (Forum::deleteThread($_POST['threadId']) === false) {
          $response->message = Forum::getLastError();
          break;
        }
        $response->success = true;
        $response->message = "Thread $threadId was deleted.";
        break;
      case "lockThread":
        if (Forum::lockThread($_POST['threadId']) === false) {
          $response->message = Forum::getLastError();
          break;
        }
        $response->success = true;
        $response->message = "Thread $threadId is locked.";
        break;
      case "unlockThread":
        if (Forum::unlockThread($_POST['threadId']) === false) {
          $response->message = Forum::getLastError();
          break;
        }
        $response->success = true;
        $response->message = "Thread $threadId is unlocked.";
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