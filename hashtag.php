<?php

  require_once(__DIR__ . '/conf/init_http.php');
  require_once(__DIR__ . '/lib/class/Forum/Forum.php');
  require_once(__DIR__ . '/lib/class/User/Profile.php');
  require_once(__DIR__ . '/lib/class/File/UserFile.php');

  $response = (object) array(
                'success' => false,
                'message' => '',
                'results' => null
              );

  if ($_POST) {
    switch ($_POST['command']) {
      case "go":
        $forumResults   = Forum::search($_POST['tag']);
        $profileResults = Profile::search($_POST['tag']);
		$blogResults    = Blog::search($_POST['tag']);
        $fileResults    = UserFile::search($_POST['tag']);
        $response->success = true;
        $response->results = (object) array(
                               'hashtag'  => $_POST['tag'],
                               'posts'    => $forumResults->posts,
                               'profiles' => array_values($profileResults),
							   'blogs'    => $blogResults,
                               'files'    => $fileResults
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