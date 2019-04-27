<?php

  require_once(__DIR__ . '/../System/Config.php');
  require_once(__DIR__ . '/User.php');
  require_once(__DIR__ . '/WhosOnline.php');
  require_once(__DIR__ . '/../Messaging/Alerts.php');

  class Account
  {

    protected static

      $lastError = "";

    public static function signIn($username, $password)
    {
      if (($user = User::getUserByName($username)) === false) {
        self::$lastError = "Invalid username or password.";
        return false;
      }
      if (!password_verify($password, $user->password)) {
        self::$lastError = "Invalid username or password.";
        return false;
      }
      if (User::userIsBanned($user->id)) {
        self::$lastError = "You've been banned.";
        return false;
      }
      $_SESSION['userId']   = $user->id;
      $_SESSION['username'] = $user->username;
      session_write_close();
      WhosOnline::userArrived($user->id, session_id());
      return $user;
    } // signIn

    public static function signOut($userId)
    {
      session_destroy();
      WhosOnline::userDeparted($userId);
    } // signOut

    public static function signUp($username, $password, $again, $question, $answer)
    {
      if ($password !== $again) {
        self::$lastError = "The two passwords do not match.";
        return false;
      }
      $cnf    = Config::instance();
      $hashed = password_hash($password, $config->security->algorithm);
      $user   = User::create(
                  (object) array(
                    'username' => $username,
                    'password' => $hashed,
                    'question' => $question,
                    'answer'   => $answer
                  )
                );
      if ($user === false) {
        self::$lastError = "That username is already taken.";
        return false;
      }
      $userId   = $user->id;
      $username = $user->username;
      $_SESSION['userId']   = $id;
      $_SESSION['username'] = $username;
      session_write_close();
      Alerts::enqueue(
        (object) array(
          'typeId'  => 13,
          'private' => false,
          'data'    => "New member <a href=\"#\" class=\"profile-link\" data-userId=\"$userId\">$username</a> has registered."
        )
      );
      WhosOnline::userArrived($user->id, session_id());
      return $user;
    } // signUp

    public static function lostPassword($username)
    {
      if (($user = User::getUserByName($username)) === false) {
        self::$lastError = "Invalid username.";
        return false;
      }
      return $user;
    } // lostPassword

    public static function recover($username, $answer)
    {
      if (($user = User::getUserByName($username)) === false) {
        self::$lastError = "Invalid username.";
        return false;
      }
      if ($answer !== $user->answer) {
        self::$lastError = "The answer is incorrect.";
        return false;
      }
      return $user;
    } // recover

    public static function changePassword($userId, $password, $again)
    {
      if ($password !== $again) {
        self::$lastError = "The two passwords do not match.";
        return false;
      }
      $cnf  = Config::instance();
      $user = new User($userId);
      $user->update(
        (object) array(
          'password' => password_hash($password, $cnf->security->algorithm)
        )
      );
      return $user;
    } // changePassword

    public static function getLastError()
    {
      $message = self::$lastError;
      self::$lastError = "";
      return $message;
    } // getLastError

  } // Account

?>