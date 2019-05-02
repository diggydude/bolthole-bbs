<?php

  require_once(__DIR__ . '/conf/init_http.php');
  require_once(__DIR__ . '/lib/class/User/User.php');
  require_once(__DIR__ . '/lib/class/Messaging/Emoticons.php');

  $users  = User::listUsers();
  $emotes = Emoticons::instance();
  $conf   = json_encode(
              (object) array(
                'siteName'      => $config->site->name,
                'maxUploadSize' => $config->files->uploads->maxSize,
                'maxAvatarSize' => $config->files->avatars->maxSize,
                'avatarFolder'  => $config->files->avatars->baseUri,
                'allowedTags'   => (object) array(
                                     'forum'    => '[' . implode('], [', $config->forum->allowedTags) . ']',
                                     'chat'     => '[' . implode('], [', $config->chat->allowedTags) . ']',
                                     'comments' => '[' . implode('], [', $config->comments->allowedTags) . ']',
                                     'files'    => '[' . implode('], [', $config->files->uploads->allowedTags) . ']'
                                   ),
                'emoticons'     => $emotes->listIcons(true),
				'emoticonPath'  => $config->files->emoticons->baseUri
              )
            );

?>
<!DOCTYPE html>
<html lang="en-us">
  <head>
    <meta charset="UTF-8" />
    <title><?=$config->site->name; ?></title>
    <meta name="description" value="<?=$config->site->description; ?>" />
    <meta name="keywords" value="<?=$config->site->keywords; ?>" />
    <link rel="stylesheet" type="text/css" href="./client/flexbox.css" />
    <link rel="stylesheet" type="text/css" href="./client/profile.css" />
    <link rel="stylesheet" type="text/css" href="./client/comments.css" />
    <link rel="stylesheet" type="text/css" href="./client/forum.css" />
    <link rel="stylesheet" type="text/css" href="./client/blog.css" />
    <link rel="stylesheet" type="text/css" href="./client/userfile.css" />
    <link rel="stylesheet" type="text/css" href="./client/marquee.css" />
    <link id="theme" rel="stylesheet" type="text/css" href="./client/theme/0.css" />
    <script type="text/javascript">
      var Config = <?=$conf; ?>;
    </script>
    <script type="text/javascript" src="./client/misc.js"></script>
    <script type="text/javascript" src="./client/js.cookie.js"></script>
    <script type="text/javascript" src="./client/rot13.js"></script>
    <script type="text/javascript" src="./client/tabs.js"></script>
    <script type="text/javascript" src="./client/jstparser.js"></script>
    <script type="text/javascript" src="./client/module/EventHandlers.js"></script>
    <script type="text/javascript" src="./client/module/TaskList.js"></script>
    <script type="text/javascript" src="./client/module/Tree.js"></script>
    <script type="text/javascript" src="./client/module/Client.js"></script>
    <script type="text/javascript" src="./client/module/Following.js"></script>
    <script type="text/javascript" src="./client/module/Profile.js"></script>
    <script type="text/javascript" src="./client/module/Preferences.js"></script>
    <script type="text/javascript" src="./client/module/Session.js"></script>
    <script type="text/javascript" src="./client/module/AlertFilter.js"></script>
    <script type="text/javascript" src="./client/module/Mailbox.js"></script>
    <script type="text/javascript" src="./client/module/Chat.js"></script>
    <script type="text/javascript" src="./client/module/Forum.js"></script>
    <script type="text/javascript" src="./client/module/Blog.js"></script>
    <script type="text/javascript" src="./client/module/UserFiles.js"></script>
    <script type="text/javascript" src="./client/module/Search.js"></script>
  </head>
  <body>
    <div class="full-screen">
      <span id="cursor-container"></span>
      <header class="header">
        <div class="logo" id="logo"><?=$config->site->name; ?></div>
        <div id="site-search">
          <input id="site-search-terms" type="text" />
          <select id="site-search-where">
            <option value="forum">Bulletins</option>
            <option value="profiles">Profiles</option>
            <option value="blog">Blog Posts</option>
            <option value="files">File Libraries</option>
            <!-- option value="stores">Stores</option -->
            <!-- option value="channels">Channels</option -->
          </select>
          <button id="site-search-button">Search</button>
        </div>
      </header>
      <nav class="nav">
        <a href="#" class="nav-link" id="show-forum-link">Bulletin Board</a> &nbsp;
        <a href="#" class="nav-link" id="list-profiles-link">Profiles</a> &nbsp;
        <!-- a href="#" class="nav-link" id="show-stores-link">Stores</a> &nbsp;
        <a href="#" class="nav-link" id="show-channels-link">Channels</a> &nbsp;
        <a href="#" class="nav-link" id="show-tos-link">Terms of Service</a -->
        <a href="#" class="nav-link" id="help-link">Help</a>
      </nav>
      <div class="main">
        <aside class="left">
          <div id="welcome"></div>
          <div id="accordion">
            <div class="accordion">&middot; Compose</div>
            <div class="panel">
              <div class="listBox" id="compose">
                <div class="tbl">
                  <div class="tr">
                    <div class="label">To:</div>
                    <div class="control">
                      <select id="compose_to">
                       <?foreach ($users as $userId => $user): ?>
                       <option value="<?=$userId; ?>"><?=$user->username; ?></option>
                       <?endforeach; ?>
                      </select>
                    </div>
                  </div>
                  <div class="tr">
                    <div class="label">Subject:</div>
                    <div class="control"><input type="text" id="compose_subject" value="" /></div>
                  </div>
                </div>
                <div class="groupLabel">Message:</div>
                <div><textarea id="compose_body"></textarea></div>
                <div class="send">
                  <button id="compose_cancel">Cancel</button>
                  <button id="compose_send">Send</button>
                </div>
              </div>
            </div>
            <div class="accordion">&middot; Inbox</div>
            <div class="panel">
              <div class="listBox" id="inbox"></div>
              <div class="listBox searchResults" id="inboxSearchResults"></div>
              <div class="searchControls">
                <input type="text" id="inbox_search_terms" />
                <button id="inbox_search">Search</button>
              </div>
            </div>
            <div class="accordion">&middot; Outbox</div>
            <div class="panel">
              <div class="listBox" id="outbox"></div>
              <div class="listBox searchResults" id="outboxSearchResults"></div>
              <div class="searchControls">
                <input type="text" id="outbox_search_terms" />
                <button id="outbox_search">Search</button>
              </div>
            </div>
            <div class="accordion">&middot; Alerts</div>
            <div class="panel">
              <div class="listBox" id="alerts"></div>
              <div class="clear"><button id="alerts_clear">Clear</button></div>
            </div>
            <div class="accordion">&middot; Following</div>
            <div class="panel">
              <div class="listBox" id="following"></div>
              <div class="listBox searchResults" id="followingSearchResults"></div>
              <div class="searchControls">
                <input type="text" id="following_search_terms" />
                <button id="following_search">Search</button>
              </div>
            </div>
            <div class="accordion">&middot; Preferences</div>
            <div class="panel">
              <div class="groupLabel"><a href="#" id="settings-change-password-link">Change Password</a></div>
              <div class="listBox" id="settings">
                <div class="tbl">
                  <div class="tr">
                    <div class="label">Theme:</div>
                    <div class="control">
                      <select id="settings_theme">
                        <option value="0">Citrus Punch</option>
                        <option value="100">Seasick Ferry</option>
                        <option value="200">Stratosphere</option>
                      </select>
                    </div>
                  </div>
                  <div class="tr">
                    <div class="label">Cursor Trail:</div>
                    <div class="control">
                      <select id="settings_cursor">
                        <option value="none">None</option>
                        <option value="bubble">Bubbles</option>
                        <option value="fairy_dust">Fairy Dust</option>
                        <option value="ghost">Ghost</option>
                        <option value="snowflake">Snowflakes</option>
                        <option value="circle">Circling Text</option>
                        <option value="spring">Springy Text</option>
                        <option value="squidie">Space Ant</option>
                      </select>
                    </div>
                  </div>
                  <div class="tr">
                    <div class="label">Sound Effects:</div>
                    <div class="control">
                      <select id="settings_sounds">
                        <option value="0">Off</option>
                        <option value="1">On</option>
                      </select>
                    </div>
                  </div>
                </div>
                <div>
                  <div class="groupLabel">Alert me when these events occur:</div>
                  <div><label><input type="checkbox" id="settings_notifyUserSignup"   /> A new member signs up</label></div>
                  <div><label><input type="checkbox" id="settings_notifyReply"        /> Someone replies to one of my forum posts</label></div>
                  <div><label><input type="checkbox" id="settings_notifyVisit"        /> Someone views my profile</label></div>
                  <div><label><input type="checkbox" id="settings_notifyComment"      /> Someone comments on my profile or blog</label></div>
                  <div><label><input type="checkbox" id="settings_notifyMention"      /> I'm mentioned in chat, forum, or a blog post</label></div>
                  <div><label><input type="checkbox" id="settings_notifyDownload"     /> Someone downloads a file from my library</label></div>
                  <div><label><input type="checkbox" id="settings_notifyBkmkProfile"  /> A followed user's profile is updated</label></div>
                  <div><label><input type="checkbox" id="settings_notifyBkmkBlogPost" /> A followed user makes a blog post</label></div>
                  <div><label><input type="checkbox" id="settings_notifyBkmkUpload"   /> A followed user uploads a file</label></div>
                  <div><label><input type="checkbox" id="settings_notifyAnyProfile"   /> Anyone's profile is updated</label></div>
                  <div><label><input type="checkbox" id="settings_notifyAnyBlogPost"  /> Anyone makes a blog post</label></div>
                  <div><label><input type="checkbox" id="settings_notifyAnyUpload"    /> Anyone uploads a file</label></div>
                  <div><label><input type="checkbox" id="settings_notifyUserBanned"   /> A member is banned</label></div>
                </div>
                <div class="save"><button id="settings_save">Save</button></div>
              </div>
            </div>
            <div class="accordion">&middot; Profile</div>
            <div class="panel">
              <div class="groupLabel"><a href="#" id="view-my-profile-link">View My Profile</a></div>
              <div class="groupLabel"><a href="#" id="new-blog-post-link">New Blog Post</a></div>
              <div class="groupLabel"><a href="#" id="file-upload-link">Upload a File</a></div>
              <div class="listBox" id="profile">
                <div class="tbl">
                  <div class="tr">
                    <div class="label">Display Name:</div>
                    <div class="control"><input type="text" id="profile_display_name" value="" /></div>
                  </div>
                  <div class="tr">
                    <div class="label">Title:</div>
                    <div class="control"><input type="text" id="profile_title" value="" /></div>
                  </div>
                  <div class="tr">
                    <div class="label">Avatar:</div>
                    <div class="control"><input type="file" accept="image/*" id="profile_avatar" value="" /></div>
                  </div>
                  <div class="tr">
                    <div class="label">Website:</div>
                    <div class="control"><input type="text" id="profile_website" value="" /></div>
                  </div>
                </div>
                <div class="groupLabel">Signature:</div>
                <div><textarea id="profile_signature"></textarea></div>
                <div class="groupLabel">About Me:</div>
                <div><textarea id="profile_about"></textarea></div>
                <div class="save"><button id="profile_save">Save</button></div>
              </div>
            </div>
            <script type="text/javascript" src="./client/accordion.js"></script>
          </div>
        </aside>
        <main class="middle" id="main"></main>
        <aside class="right">
          <div class="boxLabel">Who's Online</div>
          <div id="whosOnline"></div>
          <div class="boxLabel">Chat</div>
          <div id="chatWindow"></div>
          <div id="chatControls">
            <input type="text" id="chatMessage" value="" />
            <button id="chatSend">Send</button>
          </div>
        </aside>
      </div>
      <footer class="footer" id="footer">
        Copyright &copy; <?=$config->site->copyright; ?> &middot;
        Powered by <a href="https://github.com/diggydude/bolthole-bbs" target="_blank">Bolthole BBS</a>
      </footer>
    </div>
    <div class="modal" id="forum-post-editor"></div>
    <div class="modal" id="blog-post-editor"></div>
    <div class="modal" id="file-upload-editor"></div>
    <div class="modal" id="generic-modal">
      <div class="modal-content" id="generic-modal-content">
        <div class="modal-title-bar">
          <div class="modal-caption" id="generic-modal-caption"></div>
          <div class="modal-close" id="generic-modal-close-button">&times;</div>
        </div>
        <div id="generic-modal-viewport"></div>
      </div>
    </div>
    <div id="error-message"></div>
    <div id="success-message"></div>
<?foreach (glob(__DIR__ . "/client/template/*.html") as $tpl): ?>
    <textarea class="jst-template" id="tpl-<?=basename($tpl, ".html"); ?>"><?=htmlentities(file_get_contents($tpl)); ?></textarea>
<?endforeach; ?>
    <audio id="welcome-audio" src="./client/sound/welcome.wav" type="audio/wav"></audio>
    <audio id="gotmail-audio" src="./client/sound/gotmail.wav" type="audio/wav"></audio>
    <audio id="goodbye-audio" src="./client/sound/goodbye.wav" type="audio/wav"></audio>
    <audio id="chime-audio"   src="./client/sound/chime.wav"   type="audio/wav"></audio>
    <audio id="gong-audio"    src="./client/sound/gong.wav"    type="audio/wav"></audio>
    <script type="text/javascript">
      window.addEventListener('load',
        function()
        {
          Client.init();
          Session.showForm('sign-in');
          EventHandlers.apply();
        }
      );
      window.addEventListener('beforeunload',
        function()
        {
          Session.logout();
        }
      );
      window.addEventListener('unload',
        function()
        {
          Session.logout();
        }
      );
    </script>
  </body>
</html>