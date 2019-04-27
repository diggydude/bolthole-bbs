<?php

  require_once(__DIR__ . '/conf/init_http.php');
  require_once(__DIR__ . '/lib/class/User/Account.php');
  require_once(__DIR__ . '/lib/class/User/Profile.php');

  $response = (object) array(
                'success' => false,
                'message' => '',
                'results' => null
              );

  if ($_POST) {
    switch ($_POST['command']) {
      case "signIn":
        if (($user = Account::signIn($_POST['username'], $_POST['password'])) == false) {
          $response->message = Account::getLastError();
          break;
        }
    $profile = new Profile($user->id);
    $response->success = true;
    $response->results = (object) array(
                           'userId'      => $user->id,
                           'username'    => $user->username,
                           'joined'      => $user->joined,
                           'displayName' => $profile->displayName,
                           'title'       => $profile->title,
                           'avatar'      => $profile->avatar,
                           'signature'   => $profile->signature,
                           'website'     => $profile->website,
                           'about'       => $profile->about,
                           'rendered'    => $profile->rendered,
                           'blogId'      => $profile->blogId,
                           'blogPosts'   => $profile->listBlogPosts(),
                           'libraryId'   => $profile->libraryId,
                           'files'       => $profile->listFiles(),
                           'comments'    => $profile->listComments()
                         );
        break;
      case "signUp":
        $user = Account::signUp($_POST['username'], $_POST['password'], $_POST['again'], $_POST['question'], $_POST['answer']);
        if ($user == false) {
          $response->message = "That username is already taken.";
          break;
        }
        $profile = new Profile($user->id);
        $response->success = true;
        $response->results = (object) array(
                               'userId'      => $user->id,
                               'username'    => $user->username,
                               'joined'      => $user->joined,
                               'displayName' => $profile->displayName,
                               'title'       => $profile->title,
                               'avatar'      => $profile->avatar,
                               'signature'   => $profile->signature,
                               'website'     => $profile->website,
                               'about'       => $profile->about,
                               'rendered'    => $profile->rendered,
                               'blogId'      => $profile->blogId,
                               'blogPosts'   => $profile->listBlogPosts(),
                               'libraryId'   => $profile->libraryId,
                               'files'       => $profile->listFiles(),
                               'comments'    => $profile->listComments()
                             );
        break;
      case "lostPassword":
        if (($user = Account::lostPassword($_POST['username'])) == false) {
          $response->message = Account::getLastError();
          break;
        }
        $response->success = true;
        $response->results = (object) array(
                               'username' => $user->username,
                               'question' => $user->question
                             );
        break;
      case "recover":
        if (($user = Account::recover($_POST['username'], $_POST['answer'])) == false) {
          $response->message = Account::getLastError();
          break;
        }
        $profile = new Profile($user->id);
        $response->success = true;
        $response->results = (object) array(
                               'userId'      => $user->id,
                               'username'    => $user->username,
                               'joined'      => $user->joined,
                               'displayName' => $profile->displayName,
                               'title'       => $profile->title,
                               'avatar'      => $profile->avatar,
                               'signature'   => $profile->signature,
                               'website'     => $profile->website,
                               'about'       => $profile->about,
                               'rendered'    => $profile->rendered,
                               'blogId'      => $profile->blogId,
                               'blogPosts'   => $profile->listBlogPosts(),
                               'libraryId'   => $profile->libraryId,
                               'files'       => $profile->listFiles(),
                               'comments'    => $profile->listComments()
                             );
        break;
      case "changePassword":
        if (($user = Account::changePassword($_POST['userId'], $_POST['password'], $_POST['again'])) == false) {
          $response->message = Account::getLastError();
          break;
        }
        $profile = new Profile($user->id);
        $response->success = true;
        $response->results = (object) array(
                               'userId'      => $user->id,
                               'username'    => $user->username,
                               'joined'      => $user->joined,
                               'displayName' => $profile->displayName,
                               'title'       => $profile->title,
                               'avatar'      => $profile->avatar,
                               'signature'   => $profile->signature,
                               'website'     => $profile->website,
                               'about'       => $profile->about,
                               'rendered'    => $profile->rendered,
                               'blogId'      => $profile->blogId,
                               'blogPosts'   => $profile->listBlogPosts(),
                               'libraryId'   => $profile->libraryId,
                               'files'       => $profile->listFiles(),
                               'comments'    => $profile->listComments()
                             );
        break;
      case "signOut":
        $user = Account::signOut($_POST['userId']);
        $response->success = true;
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