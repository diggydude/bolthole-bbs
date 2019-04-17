<?php

  require_once(__DIR__ . '/conf/init_http.php');
  require_once(__DIR__ . '/lib/class/Messaging/Chat.php');
  require_once(__DIR__ . '/lib/class/User/WhosOnline.php');

  $response = (object) array(
                'success' => false,
                'message' => '',
                'results' => null
              );

  if ($_POST) {
    switch ($_POST['command']) {
      case "join":
        $lastMessageId     = Chat::join($_POST['username']);
        $response->success = true;
        $response->results = (object) array(
                               'lastMessageId' => $lastMessageId
                             );
        break;
	  case "quit":
	    Chat::quit($_POST['username']);
		$response->success = true;
		break;
      case "sendMessage":
        Chat::sendMessage(
          (object) array(
            'postedBy' => $_POST['postedBy'],
            'body'     => $_POST['body']
          )
        );
        $response->success = true;
        break;
      case "getMessages":
        $messages = Chat::getMessages($_POST['lastMsgRcvd']);
        $online   = array_values(WhosOnline::listUsers());
        $response->success = true;
        $response->results = (object) array(
                               'chats'  => $messages,
                               'online' => $online
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