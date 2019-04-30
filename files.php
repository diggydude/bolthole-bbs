<?php

  require_once(__DIR__ . '/conf/init_http.php');
  require_once(__DIR__ . '/lib/class/User/User.php');
  require_once(__DIR__ . '/lib/class/File/Uploader.php');
  require_once(__DIR__ . '/lib/class/File/UserFile.php');
  require_once(__DIR__ . '/lib/class/File/Library.php');
  require_once(__DIR__ . '/lib/class/Messaging/Alerts.php');

  $response = (object) array(
                'success' => false,
                'message' => '',
                'results' => null
              );

  if ($_POST) {
    switch ($_POST['command']) {
      case "getDetails":
        $file     = new UserFile($_POST['fileId']);
        $comments = $file->listComments();
        $library  = new Library($file->inLibrary);
        $owner    = $library->getOwner();
        $profile  = $owner->getProfile();
        $response->success = true;
        $response->results = (object) array(
                               'id'          => $file->id,
                               'fileId'      => $file->fileId,
                               'filename'    => $file->filename,
                               'size'        => $file->size,
                               'mimeType'    => $file->mimeType,
                               'hash'        => $file->hash,
                               'description' => $file->description,
                               'rendered'    => $file->rendered,
                               'downloads'   => $file->downloads,
                               'inLibrary'   => $file->inLibrary,
                               'uploadedAt'  => $file->uploadedAt,
                               'uploadedBy'  => $owner->id,
                               'uploader'    => $owner->username,
                               'title'       => $profile->title,
                               'avatar'      => $profile->avatar,
                               'signature'   => $profile->signature,
                               'comments'    => $comments
                             );
        break;
      case "upload":
        $config   = Config::instance();
        $uploader = new Uploader(
                      (object) array(
                        'directory'    => $config->files->uploads->directory,
                        'allowedTypes' => $config->files->uploads->allowedTypes
                      )
                    );
        if (($info = $uploader->uploadFile($_FILES['upload'])) === false) {
          $response->message = $uploader->getLastError();
          break;
        }
        $file     = UserFile::create(
                      array(
                        'description' => $_POST['description'],
                        'inLibrary'   => $_POST['inLibrary'],
                        'filename'    => $info->filename,
                        'size'        => $info->size,
                        'mimeType'    => $info->mimeType,
                        'hash'        => $info->hash
                      )
                    );
        $comments = $file->listComments();
        $library  = new Library($file->inLibrary);
        $owner    = $library->getOwner();
        $profile  = $owner->getProfile();
        $response->success = true;
        $response->message = "The file was uploaded.";
        $response->results = (object) array(
                               'id'          => $file->id,
                               'fileId'      => $file->fileId,
                               'filename'    => $file->filename,
                               'size'        => $file->size,
                               'mimeType'    => $file->mimeType,
                               'hash'        => $file->hash,
                               'description' => $file->description,
                               'rendered'    => $file->rendered,
                               'downloads'   => $file->downloads,
                               'inLibrary'   => $file->inLibrary,
                               'uploadedAt'  => $file->uploadedAt,
                               'uploadedBy'  => $owner->id,
                               'uploader'    => $owner->username,
                               'title'       => $profile->title,
                               'avatar'      => $profile->avatar,
                               'signature'   => $profile->signature,
                               'comments'    => $comments
                             );
        Alerts::enqueue(
          (object) array(
            'typeId'    => 12,
            'private'   => false,
            'recipient' => 1,
            'data'      => "<a href=\"#\" class=\"profile-link\" data-userId=\"" . $owner->id . "\">" . $owner->username . "</a>"
                         . " uploaded <a href=\#\" class=\"file-details-link\" data-fileId=\"" . $file->id . "\">" . $file->filename . "</a>."
          )
        );
        break;
      case "search":
        $files = UserFile::search($_POST['terms']);
        $response->success = true;
        $response->results = (object) array(
                               'files' => $files
                             );
        break;
      case "postComment":
        $comment  = Comment::create(
                      array(
                        'moduleId'     => $_POST['fileId'],
                        'moduleTypeId' => 3,
                        'postedBy'     => $_POST['postedBy'],
                        'postedAt'     => gmdate('Y-m-d H:i:s'),
                        'body'         => $_POST['body']
                      )
                    );
        $file     = new UserFile($_POST['fileId']);
        $comments = $file->listComments();
        $library  = new Library($file->inLibrary);
        $owner    = $library->getOwner();
        $profile  = $owner->getProfile();
        $response->success = true;
        $response->message = "Your comment has been posted.";
        $response->results = (object) array(
                               'id'          => $file->id,
                               'fileId'      => $file->fileId,
                               'filename'    => $file->filename,
                               'size'        => $file->size,
                               'mimeType'    => $file->mimeType,
                               'hash'        => $file->hash,
                               'description' => $file->description,
                               'rendered'    => $file->rendered,
                               'downloads'   => $file->downloads,
                               'inLibrary'   => $file->inLibrary,
                               'uploadedAt'  => $file->uploadedAt,
                               'uploadedBy'  => $owner->id,
                               'uploader'    => $owner->username,
                               'title'       => $profile->title,
                               'avatar'      => $profile->avatar,
                               'signature'   => $profile->signature,
                               'comments'    => $comments
                             );
        $commenter = new User($_POST['postedBy']);
        Alerts::enqueue(
          (object) array(
            'typeId'    => 4,
            'private'   => true,
            'recipient' => $owner->id,
            'data'      => "<a href=\"#\" class=\"profile-link\" data-userId=\"" . $commenter->id . "\">". $commenter->username . "</a>"                         . " commented on <a href=\"#\" class=\"file-details-link\" data-fileId=\"" . $file->fileId . "\">" . $file->filename . "</a>."
          )
        );
        break;
      default:
        $response->message = "Unsupported command: \"" . $_POST['command'] . "\".";
        break;
    }
    header('Content-Type: application/json');
    echo json_encode($response);
    exit(0);
  }
  $config  = Config::instance();
  $file    = new UserFile($_GET['fileId']);
  $library = new Library($file->inLibrary);
  $owner   = $library->getOwner();
  $file->download();
  Alerts::enqueue(
    (object) array(
      'typeId'    => 6,
      'private'   => true,
      'recipient' => $owner->id,
      'data'      => "Someone downloaded <a href=\#\" class=\"file-details-link\" data-fileId=\"" . $file->id . "\">" . $file->filename . "</a>"
                   . " from your library."
    )
  );
  header('Content-Type: ' . $file->mimeType);
  header('Content-Length: ' . $file->size);
  header('Content-Disposition: attachment; filename="' . $file->filename . '"');
  readfile($config->files->uploads->directory . '/' . $file->hash);
  exit(0);

?>