<?php

  require_once(__DIR__ . '/conf/init_http.php');
  require_once(__DIR__ . '/lib/class/User/User.php');
  require_once(__DIR__ . '/lib/class/User/Profile.php');
  require_once(__DIR__ . '/lib/class/File/Uploader.php');
  require_once(__DIR__ . '/lib/class/Imaging/Image.php');
  require_once(__DIR__ . '/lib/class/Messaging/Alerts.php');

  $response = (object) array(
                'success' => false,
                'message' => '',
                'results' => null
              );

  if ($_POST) {
    switch ($_POST['command']) {
      case "save":
        $config  = Config::instance();
        $profile = new Profile($_POST['userId']);
        $params  = (object) array(
                     'displayName' => $_POST['displayName'],
                     'title'       => $_POST['title'],
                     'signature'   => $_POST['signature'],
                     'website'     => $_POST['website'],
                     'about'       => $_POST['about']
                   );
        if ($_FILES) {
          // Avatar
          if (array_key_exists('avatar', $_FILES)) {
            $uploader = new Uploader($config->files->avatars);
            if (($info = $uploader->uploadFile($_FILES['avatar'])) === false) {
              $response->message = $uploader->getLastError();
              break;
            }
            $dimensions = getimagesize($info->path);
            $maxWidth   = $config->files->avatars->maxWidth;
            $maxHeight  = $config->files->avatars->maxHeight;
            if (($dimensions[0] > $maxWidth) || ($dimensions[1] > $maxHeight)) {
              $response->message = "Avatar dimensions cannot exceed $maxWidth by $maxHeight pixels.";
              @unlink($info->path);
              break;
            }
	    $image = Image::fromFile($info->path);
            if ($image->width > $image->height) {
              $cropLeft   = floor(($image->width / 2) - ($image->height / 2));
              $cropTop    = 0;
              $cropWidth  = $image->height;
              $cropHeight = $image->height;
            }
            else {
              $cropLeft   = 0;
              $cropTop    = floor(($image->height / 2) - ($image->width / 2));
              $cropWidth  = $image->width;
              $cropHeight = $image->width;
            }
            $image->crop($cropLeft, $cropTop, $cropWidth, $cropHeight);
            $image->resize(125, 125);
            $image->setMimeType('image/png');
            $image->setFilename($config->files->avatars->directory . '/' . $info->hash . '_' . 'large.png');
            $image->save();
            $image = Image::fromFile($info->path);
            $image->crop($cropLeft, $cropTop, $cropWidth, $cropHeight);
            $image->resize(60, 60);
            $image->setMimeType('image/png');
            $image->setFilename($config->files->avatars->directory . '/' . $info->hash . '_' . 'medium.png');
            $image->save();
            $image = Image::fromFile($info->path);
            $image->crop($cropLeft, $cropTop, $cropWidth, $cropHeight);
            $image->resize(48, 48);
            $image->setMimeType('image/png');
            $image->setFilename($config->files->avatars->directory . '/' . $info->hash . '_' . 'small.png');
            $image->save();
            unlink($info->path);
            $params->avatar = $info->hash;
          }
          // Banner
          if (array_key_exists('banner', $_FILES)) {
            $uploader = new Uploader($config->files->banners);
            if (($info = $uploader->uploadFile($_FILES['banner'])) === false) {
              $response->message = $uploader->getLastError();
              break;
            }
            $dimensions = getimagesize($info->path);
            $maxWidth   = $config->files->banners->maxWidth;
            $maxHeight  = $config->files->banners->maxHeight;
            if (($dimensions[0] > $maxWidth) || ($dimensions[1] > $maxHeight)) {
              $response->message = "Banner dimensions cannot exceed $maxWidth by $maxHeight pixels.";
              @unlink($info->path);
              break;
            }
            $image = Image::fromFile($info->path);
            $image->setMimeType('image/png');
            $image->setFilename($config->files->banners->directory . '/' . $info->hash . '.png');
            $image->save();
            unlink($info->path);
            $params->banner = $info->hash;
          }
        }
        $profile->update($params);
        $user = $profile->getUser();
        $response->success = true;
        $response->message = "Your profile has been saved.";
        $response->results = (object) array(
                               'userId'      => $profile->userId,
                               'username'    => $user->username,
                               'joined'      => $user->joined,
                               'displayName' => $profile->displayName,
                               'title'       => $profile->title,
                               'avatar'      => $profile->avatar,
                               'banner'      => $profile->banner,
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
        $user = $profile->getUser();
        Alerts::enqueue(
          (object) array(
            'typeId'    => 10,
            'private'   => false,
            'recipient' => 1,
            'data'      => "<a href=\"#\" class=\"alert-profile-link\" data-userId=\"" . $user->id . "\">" . $user->username . "</a>"
                         . " updated their profile."
          )
        );
        break;
      case "postComment":
        $comment = Comment::create(
                     array(
                       'moduleId'     => $_POST['profileId'],
                       'moduleTypeId' => 1,
                       'postedBy'     => $_POST['postedBy'],
                       'postedAt'     => gmdate('Y-m-d H:i:s'),
                       'body'         => $_POST['body']
                     )
                   );
        $profile = new Profile($_POST['profileId']);
        $user    = $profile->getUser();
        $response->success = true;
        $response->message = "Your comment has been posted.";
        $response->results = (object) array(
                               'userId'      => $profile->userId,
                               'username'    => $user->username,
                               'joined'      => $user->joined,
                               'displayName' => $profile->displayName,
                               'title'       => $profile->title,
                               'avatar'      => $profile->avatar,
                               'banner'      => $profile->banner,
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
        $commenter = new User($_POST['postedBy']);
        Alerts::enqueue(
          (object) array(
            'typeId'    => 4,
            'private'   => true,
            'recipient' => $_POST['profileId'],
	    'data'      => "<a href=\"#\" class=\"alert-profile-link\" data-userId=\"" . $commenter->id . "\">". $commenter->username . "</a>"
                         . " commented on your profile."
          )
        );
        break;
      case "listProfiles":
        $profiles = User::listUsers();
        $response->success = true;
        $response->results = (object) array(
                               'profiles' => array_values($profiles)
                             );
        break;
      case "search":
        $profiles = Profile::search($_POST['terms']);
        $response->success = true;
        $response->results = (object) array(
                               'profiles' => array_values($profiles)
                             );
        break;
      default:
        $response->message = "Unsupported command: \"" . $_POST['command'] . "\".";
        break;
    }
  }
  else {
    $profile = new Profile($_GET['userId']);
    $user    = $profile->getUser();
    $response->success = true;
    $response->results = (object) array(
                           'userId'      => $profile->userId,
                           'username'    => $user->username,
                           'joined'      => $user->joined,
                           'displayName' => $profile->displayName,
                           'title'       => $profile->title,
                           'avatar'      => $profile->avatar,
                           'banner'      => $profile->banner,
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
    if ($_GET['viewerId'] != $_GET['userId']) {
      $viewer = new User($_GET['viewerId']);
      Alerts::enqueue(
        (object) array(
          'typeId'    => 3,
          'private'   => true,
          'recipient' => $_GET['userId'],
          'data'      => "<a href=\"#\" class=\"alert-profile-link\" data-userId=\"" . $_GET['viewerId'] . "\">"
                       . $viewer->username . "</a> viewed your profile."
        )
      );
    }
  }

  header('Content-Type: application/json');
  echo json_encode($response);
  exit(0);

?>
