<?php

  ini_set('display_errors', 1);
  require_once(__DIR__ . '/../lib/class/System/Config.php');
  require_once(__DIR__ . '/../lib/class/User/User.php');
  require_once(__DIR__ . '/../lib/func/getOptimumHashCost.php');
  $config = Config::instance(__DIR__ . '/config.conf');

  $config->site     = (object) array(
                        'name'         => "Pam's Confessional",
                        'documentRoot' => "C:\xampp\www",
                        'baseUri'      => 'http://127.0.0.1',
                        'copyright'    => '2019 Retro Web, Inc.',
                        'description'  => "Well, it's hard to explain, really.",
                        'keywords'     => 'chat, forum, blog, uploads, downloads, file sharing, retro, website',
                        'timezone'     => 'America/Chicago',
                        'sysop'        => (object) array(
                                            'name'  => 'Diggy Dude',
                                            'email' => 'therealdiggydude@gmail.com'
                                          )
                      );

  $config->session  = (object) array(
                        'name' => 'session_id'
                      );

  $config->db       = (object) array(
                        'dsn'      => 'mysql:dbname=bolthole;host=127.0.0.1',
                        'username' => 'apache',
                        'password' => 'eK23s5il'
                      );

  $config->files    = (object) array(
                        'uploads'    => (object) array(
                                          'maxSize'      => 10000000,
                                          'directory'    => realpath(__DIR__ . '/../assets/user_files'),
                                          'baseUri'      => '/assets/user_files',
                                          'allowedTypes' => array(
                                                              'application/gnutar',
                                                              'application/msword',
                                                              'application/octet-stream',
                                                              'application/pdf',
                                                              'application/x-7z-compressed',
                                                              'application/x-compressed',
                                                              'application/x-gtar',
                                                              'application/x-gzip',
                                                              'application/x-midi',
                                                              'application/x-rar-compressed',
                                                              'application/x-tar',
                                                              'application/x-zip-compressed',
                                                              'application/zip',
                                                              'audio/mid',
                                                              'audio/midi',
                                                              'audio/mod',
                                                              'audio/mpeg',
                                                              'audio/mpeg3',
                                                              'audio/wav',
                                                              'audio/x-mid',
                                                              'audio/x-midi',
                                                              'audio/x-mod',
                                                              'audio/x-mpeg',
                                                              'audio/x-mpequrl',
                                                              'audio/x-wav',
                                                              'image/gif',
                                                              'image/jpeg',
                                                              'image/pjpeg',
                                                              'image/png',
                                                              'text/plain',
                                                              'text/richtext',
                                                              'text/x-uuencode',
                                                              'text/x-vcard',
                                                              'video/mpeg',
                                                              'video/mp4',
                                                              'x-music/x-midi'
                                                            )
                                        ),
                         'avatars'   => (object) array(
                                          'maxSize'      => 1000000,
                                          'maxWidth'     => 800,
                                          'maxHeight'    => 800,
                                          'directory'    => realpath(__DIR__ . '/../assets/avatars'),
                                          'baseUri'      => '/assets/avatars',
                                          'allowedTypes' => array(
                                                              'image/gif',
                                                              'image/jpeg',
                                                              'image/pjpeg',
                                                              'image/png'
                                                            )
                                        ),
                         'emoticons' => (object) array(
                                          'directory' => realpath(__DIR__  . '/../client/emoticons'),
                                          'baseUri'   => '/client/emoticons'
                                        )
                       );

  $config->forum    = (object) array(
                        'maxThreads' => 100
                      );

  $config->profiles = (object) array(
                        'defaultAvatar'    => '266e735438b81e0f3ff90a023da668b3',
                        'defaultTitle'     => 'Russian Bot',
                        'defaultSignature' => '"No matter where you go, there you are." - Buckaroo Banzai',
                        'defaultWebsite'   => $config->site->baseUri,
                        'defaultAbout'     => 'Are you gonna fill this in or what?'
                      );

  $config->programs = (object) array(
                        'ansifilter' => (object) array(
                                          'path' => 'C:/ansifilter/ansifilter.exe'
                                        )
                      );
					  
  $config->security = (object) array(
                        'algorithm'       => PASSWORD_BCRYPT,
                        'optimumHashCost' => getOptimumHashCost()
					  );

  $config->save();

  date_default_timezone_set($config->site->timezone);
  session_start();

  User::create(
    (object) array(
      'username'    => 'System',
      'password'    => password_hash('ch@n93m3', PASSWORD_BCRYPT),
      'question'    => 'What is your username?',
      'answer'      => 'System',
      'accessLevel' => 4
    )
  );

  User::create(
    (object) array(
      'username'    => 'Sysop',
      'password'    => password_hash('ch@n93m3', PASSWORD_BCRYPT),
      'question'    => 'What is your username?',
      'answer'      => 'Sysop',
      'accessLevel' => 4
    )
  );

?>