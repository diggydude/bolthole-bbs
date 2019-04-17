<?php

  require_once(__DIR__ . '/conf/init_http.php');
  require_once(__DIR__ . '/lib/class/Messaging/PostOffice.php');
  require_once(__DIR__ . '/lib/class/Messaging/Alerts.php');

  $response = (object) array(
                'success' => false,
                'message' => '',
                'results' => null
              );

  if ($_POST) {
    switch ($_POST['command']) {
      case "checkMail":
        $inbox  = PostOffice::fetchQueue($_POST['for']);
        $alerts = Alerts::fetchQueue($_POST['for']);
		foreach ($inbox as $message) {
	      PostOffice::dispatch($message->messageId);
		}
		foreach ($alerts as $alert) {
	      Alerts::dispatch($alert->id, $_POST['for']);
		}
        $response->success = true;
        if (!empty($inbox)) {
          $response->message = "You've got mail!";
        }
        $response->results = (object) array(
                               'inbox'  => $inbox,
                               'outbox' => array(),
                               'alerts' => $alerts
                             );
        break;
      case "sendMail":
        $messageId = PostOffice::send(
                       (object) array(
                         'to'      => $_POST['to'],
                         'from'    => $_POST['from'],
                         'subject' => $_POST['subject'],
                         'body'    => $_POST['body']
                       )
                     );
        $posted   = PostOffice::getMessage($messageId);
        if ($posted) {
          $response->success = true;
          $response->message = "Your message has been sent.";
          $response->results = (object) array(
                                 'inbox'  => array(),
                                 'outbox' => array($posted),
                                 'alerts' => array()
                               );
          break;
        }
        $response->message = "Your message was not sent.";
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