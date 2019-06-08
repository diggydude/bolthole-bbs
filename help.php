<?php

  ini_set('display_errors', 0);
  require_once(__DIR__ . '/lib/class/System/Config.php');
  require_once(__DIR__ . '/lib/class/Messaging/Emoticons.php');
  $config    = Config::instance(__DIR__ . '/conf/config.conf');
  $emotes    = Emoticons::instance();
  $emoticons = $emotes->listIcons();
  date_default_timezone_set($config->site->timezone);
  session_start();

?>
<!DOCTYPE html>
<html lang="en-us">
  <head>
    <meta charset="UTF-8" />
    <title>Bolthole Software Help</title>
    <style type="text/css">
      html,
      body,
      .full-screen {
        height: 100%;
      }
      body {
        font-family: sans-serif;
        margin: 0;
        clip-path: padding-box;
        overflow: hidden;
      }
      a:not([name]) {
        color: #0000ff;
        text-decoration: none;
      }
      a:not([name]):hover {
        text-decoration: underline;
      }
      .full-screen {
        display: flex;
        overflow: hidden;
        flex-direction: column;
      }
      .full-screen > * {
        flex: 0 0 auto;
        overflow: auto;
      }
      .full-screen > .header {
        width: 100%;
        background-color: #aaaaaa;
        /* padding: 5px;
        display: flex; */
        text-align: center;
        overflow: hidden;
      }
      .full-screen > .main {
        flex: 1 1 auto;
        display: flex;
        overflow: hidden;
      }
      .full-screen > .main > * {
        flex: 0 0 auto;
        overflow: auto;
      }
      .full-screen > .main > .left {
        display: flex;
        flex-direction: column;
        font-size: 14px;
        width: 350px;
        background-color: #cccccc;
        padding: 0px 5px 0px 5px;
      }
      .full-screen > .main > .middle {
        flex: 1 1 auto;
        padding: 0px 5px 0px 5px;
      }
      .bbcode-list, .emoticon-list, .command-list {
        border: 1px solid #cccccc;
        width: 100%;
      }
      .bbcode-list > tbody > tr > td,
      .emoticon-list > tbody > tr > td,
      .command-list > tbody > tr > td {
        padding: 3px;
      }
      #code-tag-examples > tbody > tr > td {
        font-size: 12px;
      }
      th {
        color: #000000;
        background-color: #cccccc;
      }
      .spoiler-container {
        padding: 5px;
        border: 1px dotted #000000;
        margin: 10px;
      }
      .spoiler-header {
        font-weight: bold;
        padding: 5px;
        margin: 0px;
      }
      .spoiler-text {
        padding: 5px;
        border: #000000 dotted;
        border-width: 1px 0px 1px 0px;
      }
      .spoiler-button-bar {
        text-align: center;
        padding: 5px;
        margin: 0px;
      }
      .chat-author {
        font-weight: bold;
      }
      .chat-action {
        font-style: italic;
      }
    </style>
    <link type="text/css" rel="stylesheet" href="./client/marquee.css" />
    <script type="text/javascript" src="./client/rot13.js"></script>
  </head>
  <body>
    <div class="full-screen">
      <header class="header"><div><h1>Bolthole Software Help</h1></div></header>
      <div class="main">
        <aside class="left">
          <h2>Contents</h2>
          <ul>
            <li><a href="#intro">Introduction</a></li>
            <li><a href="#bbcode-usage">1. BBCode</a>
                <ul>
                  <li><a href="#basic-markup">1.1 Basic Markup</a></li>
                  <li><a href="#img-tag">1.2 The [img] Tag</a></li>
                  <li><a href="#spoiler-tag">1.3 The [spoiler] Tag</a></li>
                  <li><a href="#code-tag">1.4 The [code] Tag</a></li>
                  <li><a href="#youtube-tag">1.5 The [youtube] Tag</a></li>
                  <li><a href="#ansi-tag">1.6 The [ansi] Tag</a></li>
                  <li><a href="#allowed-tags">1.7 Where Tags May Be Used</a></li>
                </ul>
            </li>
            <li><a href="#emojis-hashtags-mentions">2. Emojis, Hashtags, and Mentions</a></li>
            <li><a href="#emoticons">3. Emoticons</a></li>
            <li><a href="#chat-commands">4. Chat Commands</a>
                <ul>
                  <li><a href="#me-and-action">4.1 The /me and /action Commands</a></li>
                  <li><a href="#kick-and-ban">4.2 The /kick and /ban Commands</a></li>
                </ul>
            </li>
            <li><a href="#user-controls">5. Control Panel</a>
                <ul>
                  <li><a href="#private-messages">5.1 Private Messaging</a>
                      <ul>
                        <li><a href="#compose">5.1.1 Message Composer<a/></li>
                        <li><a href="#inbox-and-outbox">5.1.2 Inbox and Outbox</a></li>
                      </ul>
                  </li>
                  <li><a href="#alerts">5.2 Alerts</a></li>
                  <li><a href="#following">5.3 Following</a></li>
                  <li><a href="#preferences">5.4 Preferences</a>
                      <ul>
                        <li><a href="#change-password">5.4.1 Change Password</a></li>
                        <li><a href="#theme">5.4.2 Theme</a></li>
                        <li><a href="#cursor">5.4.3 Cursor Trail</a></li>
                        <li><a href="#sound">5.4.4 Sound Effects</a></li>
                        <li><a href="#dark-mode">5.4.5 Dark Mode</a></li>
                        <li><a href="#alert-filters">5.4.6 Alert Filters</a></li>
                      </ul>
                  </li>
                  <li><a href="#profile">5.5 Profile Editor</a>
                      <ul>
                        <li><a href="#view-profile">5.5.1 View My Profile</a></li>
                        <li><a href="#blog-post">5.5.2 New Blog Post</a></li>
                        <li><a href="#upload">5.5.3 Upload File</a></li>
                        <li><a href="#display-name">5.5.4 Display Name</a></li>
                        <li><a href="#custom-title">5.5.5 Custom Title</a></li>
                        <li><a href="#avatar">5.5.6 Avatar</a></li>
                        <li><a href="#website">5.5.7 Website</a></li>
                        <li><a href="#signature">5.5.8 Signature</a></li>
                        <li><a href="#about-me">5.5.9 About Me</a></li>
                      </ul>
                  </li>
                </ul>
            </li>
            <li><a href="#bbs">6. Bulletin Board</a>
                <ul>
                  <li><a href="#topic-list">6.1 Topic List Pane</a></li>
                  <li><a href="#new-topic">6.2 Starting a Discussion</a></li>
                  <li><a href="#post-view">6.3 Post View Pane</a>
                  <li><a href="#new-topic">6.4 Replying to a Post</a></li>
                  <li><a href="#tree-view">6.5 Tree View Pane</a></li>
                  <li><a href="#tree-filters">6.6 Tree View Filters</a></li>
                </ul>
            </li>
            <li><a href="#profiles">7. Profiles</a>
                <ul>
                  <li><a href="#follow-button">7.1 The "Follow" Button</a></li>
                  <li><a href="#about-tab">7.2 The "About" Tab</a></li>
                  <li><a href="#blog-tab">7.3 The "Blog" Tab</a></li>
                  <li><a href="#files-tab">7.4 The "Files" Tab</a></li>
                </ul>
            </li>
            <li><a href="#chat">8. Chat</a></li>
            <li><a href="#comments">9. Comments</a></li>
          </ul>
        </aside>
        <main class="middle" id="main">
          <a name="intro"><h2>Introduction</h2></a>
          <p>Bolthole is a Web community program packed with unique interactive features
             designed to give it a more "live action" feel compared to most other websites. We want you
             to experience cyberspace as a real physical place, as we did back in the day when computer
             networking was new and exciting. To that end, we've incorporated the coolest
             features from over thirty years of online community software into one program, while
             avoiding as many of the negative aspects of those forebears as possible.</p>
          <a name="bbcode-usage"><h2>1. BBCode</h2></a>
          <p>BBCode is a markup language that enables users to add style to their text and embed
             media elements in a Web page. It's typically employed by Web forum programs for ease of use,
             as well as to prevent malicious users from abusing HTML to deface the website. In Bolthole,
             we've resurrected several now-defunct HTML tags and JavaScript text effects from the early
             Web as BBCode tags. We've also created some new tags that have never been seen before.</p>
          <a name="basic-markup"><h3>1.1 Basic Markup</h3></a>
          <p>Whether or not a tag may be nested inside other tags depends on the tags.
             Generally, placing other tags inside a tag that modifies its own content,
             such as <code>[rainbow]</code>, <code>[gradient]</code>, or <code>[spoiler]</code>,
             will not work as expected. Placing other tags inside of <code>[i]</code>,
             <code>[b]</code>, <code>[u]</code>, <code>[size]</code>, or <code>[color]</code>
             tags usually works.</p>
          <table class="bbcode-list">
            <thead>
              <th>Tag</th>
              <th>Result</th>
            </thead>
            <tbody>
              <tr>
                <td>[i]italic text[/i]</td>
                <td><span style="font-style: italic;">italic text</span></td>
              </tr>
              <tr>
                <td>[b]bold text[/b]</td>
                <td><span style="font-weight: bold;">bold text</span></td>
              </tr>
              <tr>
                <td>[u]underlined text[/u]</td>
                <td><span style="text-decoration: underline;">underlined text</span></td>
              </tr>
              <tr>
                <td>[color=green]green text[/color]</td>
                <td><span style="color: #00ff00;">green text</span></td>
              </tr>
              <tr>
                <td>[size=18pt]eighteen-point text[/size]</td>
                <td><span style="font-size: 18pt;">eighteen-point text</span></td>
              </tr>
              <tr>
                <td>[url=http://example.org/]Click here![/url]</td>
                <td><a href="http://example.org/" target="_blank">Click here!</a></td>
              </tr>
              <tr>
                <td>[rainbow]rainbow text[/rainbow]</td>
                <td><span style="color: rgb(243,128,31);">r</span><span style="color: rgb(196,196,2);">a</span><span style="color: rgb(127,243,12);">i</span><span style="color: rgb(59,253,59);">n</span><span style="color: rgb(12,223,128);">b</span><span style="color: rgb(2,163,196);">o</span><span style="color: rgb(32,92,243);">w</span> <span style="color: rgb(92,32,253);">t</span><span style="color: rgb(163,2,223);">e</span><span style="color: rgb(224,12,163);">x</span><span style="color: rgb(253,59,92);">t</span></td>
              </tr>
              <tr>
                <td>[gradient start=yellow end=blue]gradient text[/gradient]</td>
                <td><span style="color: rgb(0,128,0);">g</span><span style="color: rgb(0,117,21);">r</span><span style="color: rgb(0,106,42);">a</span><span style="color: rgb(0,96,63);">d</span><span style="color: rgb(0,85,85);">i</span><span style="color: rgb(0,74,106);">e</span><span style="color: rgb(0,64,127);">n</span><span style="color: rgb(0,53,148);">t</span> <span style="color: rgb(0,42,170);">t</span><span style="color: rgb(0,32,191);">e</span><span style="color: rgb(0,21,212);">x</span><span style="color: rgb(0,10,233);">t</span></td>
              </tr>
              <tr>
                <td>[blink]blinking text[/blink]</td>
                <td><span class="blinking-text">blinking text</span></td>
              </tr>
              <tr>
                <td>[marquee]scrolling text[/marquee]</td>
                <td><div class="marquee"><div class="marquee-text">scrolling text</div></div></td>
              </tr>
            </tbody>
          </table>
          <p><a href="#intro">Back to Top</a></p>
          <a name="img-tag"><h3>1.2 The [img] Tag</h3></a>
          <p>This tag may only be used with images that you or other members have uploaded. Placing
             the URL of an image from another website inside the <code>[img]</code> tag will not work.
             When you upload a file, you'll see a page of information about the file. One of the items
             shown is a 32-character MD5 hash of the file. Copy and paste the MD5 hash value between
             the <code>[img]</code> tags.</p>
          <table class="bbcode-list">
            <thead>
              <th>Tag</th>
              <th>Result</th>
            </thead>
            <tbody>
              <tr>
                <td style="vertical-align: top;">[img]11e21d7da3d9d8d679e116233aa8255a[/img]</td>
                <td><img src="./assets/user_files/11e21d7da3d9d8d679e116233aa8255a" alt="" /></td>
              </tr>
            </tbody>
          </table>
          <p><a href="#intro">Back to Top</a></p>
          <a name="spoiler-tag"><h3>1.3 The [spoiler] Tag</h3></a>
          <p>This tag scrambles text. Click the Decode button to unscramble it.</p>
          <table class="bbcode-list">
            <thead>
              <th>Tag</th>
              <th>Result</th>
            </thead>
            <tbody>
              <tr>
                <td style="vertical-align: top; width: 50%;">[spoiler]Han Solo shot first.[/spoiler]</td>
                <td style="width: 50%;"><div class="spoiler-container"><div class="spoiler-header">Spoiler:</div><div class="spoiler-text" id="example-spoiler">Una Fbyb fubg svefg.</div><div class="spoiler-button-bar"><button class="spoiler-decode-button">Decode</button></div></div>
                  <script type="text/javascript">
                    document.getElementById('spoiler-decode-button'),addEventListener('click',
                      function()
                      {
                        var div = document.getElementById('example-spoiler');
                        var txt = div.innerText.rot13();
                        div.innerText = txt;
                      }
                    );
                  </script>
                </td>
              </tr>
            </tbody>
          </table>
          <p><a href="#intro">Back to Top</a></p>
          <a name="code-tag"><h3>1.4 The [code] Tag</h3></a>
          <p>This tag can be used with or without syntax highlighting. For syntax highlighting,
             specify the programming language in the opening tag. For ASCII art or command line
             input and output, omit the language. In the examples below, we display the same
             JavaScript code with and without syntax highlighting.</p>
          <table class="bbcode-list" id="code-tag-examples">
            <thead>
              <th>Tag</th>
              <th>Result</th>
            </thead>
            <tbody>
              <tr>
                <td>
<pre style="font-family: sans-serif">[code=javascript]
function sleep(milliseconds)
{
  var start = new Date().getTime();
  for (var i = 0; i < Number.MAX_SAFE_INTEGER; i++) {
    if ((new Date().getTime() - start) > milliseconds) {
      break;
    }
  }
} // sleep
[/code]</pre></td>
     <td>
       <pre class="javascript" style="font-family:monospace;">&nbsp;
<span style="color: #000066; font-weight: bold;">function</span> sleep<span style="color: #009900;">&#40;</span>milliseconds<span style="color: #009900;">&#41;</span>
<span style="color: #009900;">&#123;</span>
  <span style="color: #000066; font-weight: bold;">var</span> start <span style="color: #339933;">=</span> <span style="color: #000066; font-weight: bold;">new</span> <span style="">Date</span><span style="color: #009900;">&#40;</span><span style="color: #009900;">&#41;</span>.<span style="color: #660066;">getTime</span><span style="color: #009900;">&#40;</span><span style="color: #009900;">&#41;</span><span style="color: #339933;">;</span>
  <span style="color: #000066; font-weight: bold;">for</span> <span style="color: #009900;">&#40;</span><span style="color: #000066; font-weight: bold;">var</span> i <span style="color: #339933;">=</span> <span style="color: #CC0000;">0</span><span style="color: #339933;">;</span> i <span style="color: #339933;">&lt;</span> <span style="">Number</span>.<span style="color: #660066;">MAX_SAFE_INTEGER</span><span style="color: #339933;">;</span> i<span style="color: #339933;">++</span><span style="color: #009900;">&#41;</span> <span style="color: #009900;">&#123;</span>
    <span style="color: #000066; font-weight: bold;">if</span> <span style="color: #009900;">&#40;</span><span style="color: #009900;">&#40;</span><span style="color: #000066; font-weight: bold;">new</span> <span style="">Date</span><span style="color: #009900;">&#40;</span><span style="color: #009900;">&#41;</span>.<span style="color: #660066;">getTime</span><span style="color: #009900;">&#40;</span><span style="color: #009900;">&#41;</span> <span style="color: #339933;">-</span> start<span style="color: #009900;">&#41;</span> <span style="color: #339933;">&gt;</span> milliseconds<span style="color: #009900;">&#41;</span> <span style="color: #009900;">&#123;</span>
      <span style="color: #000066; font-weight: bold;">break</span><span style="color: #339933;">;</span>
    <span style="color: #009900;">&#125;</span>
  <span style="color: #009900;">&#125;</span>
<span style="color: #009900;">&#125;</span> <span style="color: #006600; font-style: italic;">// sleep</span>
&nbsp;</pre></td>
             </tr>
             <tr>
               <td>
<pre style="font-family: sans-serif">[code]
function sleep(milliseconds)
{
  var start = new Date().getTime();
  for (var i = 0; i < Number.MAX_SAFE_INTEGER; i++) {
    if ((new Date().getTime() - start) > milliseconds) {
      break;
    }
  }
} // sleep
[/code]</pre></td>
               <td>
<pre style="font-family: monospace;">
function sleep(milliseconds)
{
  var start = new Date().getTime();
  for (var i = 0; i < Number.MAX_SAFE_INTEGER; i++) {
    if ((new Date().getTime() - start) > milliseconds) {
      break;
    }
  }
} // sleep
</pre></td>
             </tr>
           </tbody>
         </table>
         <p><a href="#intro">Back to Top</a></p>
         <a name="youtube-tag"><h3>1.5 The [youtube] Tag</h3></a>
         <p>Place the video ID between the tags. For example, if you have this YouTube URL:</p>
         <blockquote><code>https://www.youtube.com/watch?v=gqc8AqIZj4w</code></blockquote>
         <p>you would copy and paste the <code>gqc8AqIZj4w</code> part between the tags.</p>
         <table class="bbcode-list">
           <thead>
             <th>Tag</th>
             <th>Result</th>
           </thead>
           <tbody>
             <tr>
               <td style="vertical-align: top;">[youtube]gqc8AqIZj4w[/youtube]</td>
               <td><iframe width="280" height="157" src="https://www.youtube.com/embed/gqc8AqIZj4w" frameborder="0" allowfullscreen></iframe></td>
             </tr>
           </tbody>
         </table>
         <p><a href="#intro">Back to Top</a></p>
         <a name="ansi-tag"><h3>1.6 The [ansi] Tag</h3></a>
         <p>As with the <code>[img]</code> tag, the <code>[ansi]</code> tag will only work with ANSI art
            files that you or other members have uploaded. Place the file's MD5 hash value between the tags.</p>
         <p>If no parameters are supplied, the tag assumes your ANSI file is an 80-column Code Page 437 file.
            Other character sets and dimensions can be set in the opening tag:</p>
         <blockquote><code>[ansi type=cp437 width=44 height=22]130aada24aeec38442aa53115bc25eb6[/ansi]</code></blockquote>
         <p>Setting tag parameters is an all-or-nothing proposition, i.e., if you specify one parameter, then you must specify
            all three parameters. Accepted values for the type parameter are <code>cp437</code>, <code>bin</code>,
            and <code>tundra</code>.</p>
         <p>The following example uses a PNG image to simulate the result. The actual result
            would be a full-size HTML rendering of the Extended ASCII characters in your file.</p>
         <table class="bbcode-list">
           <thead>
             <th>Tag</th>
             <th>Result</th>
           </thead>
           <tbody>
             <tr>
               <td style="vertical-align: top;">[ansi]ff5d8fbfbb15323fc9d1d9bc538a1cfd[/ansi]</td>
               <td><img src="./client/ansi_example.png" /></td>
             </tr>
           </tbody>
         </table>
         <p><a href="#intro">Back to Top</a></p>
         <a name="allowed-tags"><h3>1.7 Where Tags May Be Used</h3></a>
         <p>The sysop may disable certain tags in certain areas of the site to reduce noise.
            Other tag restrictions are hard-coded into the system.</p>
         <p>All tags are allowed in your profile, blog posts, and private messages.</p>
         <p>No tags are allowed in display names, custom titles, or signatures.</p>
         <p>The following tags are enabled on the bulletin board: [<?=implode("], [", $config->forum->allowedTags); ?>]</p>
         <p>The following tags are enabled in chat: [<?=implode("], [", $config->chat->allowedTags); ?>]</p>
         <p>The following tags are enabled in file descriptions: [<?=implode("], [", $config->files->uploads->allowedTags); ?>]</p>
         <p>The following tags are enabled in comments: [<?=implode("], [", $config->comments->allowedTags); ?>]</p>
         <p><a href="#intro">Back to Top</a></p>
         <a name="emojis-hashtags-mentions"><h2>2. Emojis, Hashtags, and Mentions</h2></a>
         <p>Emojis are allowed in profiles, display names, custom titles, and signatures, but
            are not supported anywhere else on the system.</p>
         <p>Hashtags are allowed in all areas. Placing a "#" sign before any non-whitespace text
            automagically creates a link to a special search results page. Hashtags are the preferred
            method of curating related content.</p>
         <p>Mentions are allowed in all areas. Placing a "@" sign before a registered username
            automagically creates a link to that user's profile. Members may receive notifications
            whenever someone mentions them by checking a box in their Preferences.</p>
         <p><a href="#intro">Back to Top</a></p>
         <a name="emoticons"><h2>3. Emoticons</h2></a>
         <p>Emoticons, or "smileys," or "emotes," are small images that represent emotions. They
            were the forerunners to today's emojis. The earliest emoticons were simple text sequences,
            such as <code>:)</code> to represent a smile. In later years, other small images, such as
            public figures and memes, were added to the vocabulary on some message boards.</p>
         <p>The following emoticons are installed on the system, and may be used in all areas:</p>
         <table class="emoticon-list">
           <thead>
             <th>Code</th>
             <th style="text-align: center;">Result</th>
           </thead>
           <tbody>
             <?foreach ($emoticons as $code => $img): ?>
               <tr>
                 <td><?=$code ?></td>
                 <td style="text-align: center;"><img src="<?=$config->files->emoticons->baseUri; ?>/<?=$img; ?>" alt="<?=$code; ?>" /></td>
               </tr>
            <?endforeach; ?>
           </tbody>
         </table>
         <p><a href="#intro">Back to Top</a></p>
         <a name="chat-commands"><h2>4. Chat Commands</h2></a>
         <p>Chat commands are executed by typing a command in the chatroom.</p>
         <a name="me-and-action"><h3>4.1 The /me and /action Commands</h3></a>
         <p>These two commands are synonymous. They indicate that the user is performing an action.</p>
         <table class="command-list">
           <thead>
             <th>Command</th>
             <th>Result</th>
           </thead>
           <tbody>
             <tr>
               <td>/me pulls MarySmith's pigtails.</td>
               <td><span class="chat-action">JohnDoe pulls MarySmith's pigtails.</span></td>
             </tr>
             <tr>
               <td>/action smacks JohnDoe upside the head.</td>
               <td><span class="chat-action">MarySmith smacks JohnDoe upside the head.</span></td>
             </tr>
           </tbody>
         </table>
         <p><a href="#intro">Back to Top</a></p>
         <a name="kick-and-ban"><h3>4.2 The /kick and /ban Commands</h3></a>
         <p>The /kick command temporarily knocks a user offline, forcing them to login again. Any
            user may kick any other user except the System and Sysop users, who cannot be kicked
            or banned.</p>
         <p>The /ban command permanently bans a user from the system. Only the System and Sysop
            users can ban another user.</p>
         <table class="command-list">
           <thead>
             <th>Command</th>
             <th>Result</th>
           </thead>
           <tbody>
             <tr>
               <td>/kick JohnDoe</td>
               <td><div class="chat-message">&lt;<span class="chat-author">System</span>&gt; MarySmith kicked JohnDoe.</div></td>
             </tr>
             <tr>
               <td>/ban JohnDoe</td>
               <td><div class="chat-message">&lt;<span class="chat-author">System</span>&gt; JohnDoe has been banned.</div></td>
             </tr>
           </tbody>
         </table>
         <p><a href="#intro">Back to Top</a></p>
         <a name="user-controls"><h2>5. Control Panel</h2></a>
         <p>The user control panel occupies the lefthand portion of the screen, and is composed of seven sections:</p>
         <ul>
           <li>Compose</li>
           <li>Inbox</li>
           <li>Outbox</li>
           <li>Alerts</li>
           <li>Following</li>
           <li>Preferences</li>
           <li>Profile</li>
         </ul>
         <p>Clicking one of the seven headings will cause the corresponding control panel to slide open or closed.
            More than one panel may be open at a time. We'll now explain each panel in turn.</p>
         <p><a href="#intro">Back to Top</a></p>
         <a name="private-messages"><h3>5.1 Private Messaging</h3></a>
         <p>The Compose, Inbox, and Outbox panels together constitute the private messaging system, which works
            similarly to a typical e-mail client program.</p>
         <a name="compose"><h4>5.1.1 Message Composer</h4></a>
         <p>Here, you simply choose the recipient's username, enter a subject and message body, and click the Send
            button. Your message will be delivered within seven seconds if the recipient is online, or within seven
            seconds after he or she next logs in.</p>
         <a name="inbox-and-outbox"><h4>5.1.2 Inbox and Outbox</h4></a>
         <p>Like e-mail, your inbox lists messages you've received from other users, while your outbox shows messages
            you've sent. Due to Bolthole's privacy features, you will not be notified when a recipient has read your
            message.</p>
         <p> If you have <a href="#sound">sound effects</a> enabled, you'll hear the classic "You've got mail!" voice
		     announcement from America Online when you receive a new message.</p>
         <p>You can search for specific text within sent or received messages by typing your search terms into the
            field below your inbox or outbox and clicking the corresponding Search button. To clear the search results
            and return to the normal inbox or outbox list, simply delete the text in the search field.</p>
         <p><a href="#intro">Back to Top</a></p>
         <a name="alerts"><h3>5.2 Alerts</h3></a>
         <p>This box shows notifications you've elected to receive. Here, we'll provide only a few examples:</p>
         <ul>
           <li>A new user has registered, or a user is banned</li>
           <li>Another member replies to one of your bulletin board posts</li>
           <li>Someone comments on your profile, one of your blog posts, or a file you've uploaded</li>
           <li>A member you follow (or any member) has performed some action</li>
         </ul>
         <p>Like <a href="#private-messages">private messages</a>, notifications are delivered within seven seconds
		    after an event occurs. If you have <a href="#sound">sound effects</a> enabled, the delivery will be
			accompanied by a shimmering chime sound.</p>
         <p>You can choose which kinds of alerts you receive in your <a href="#preferences">preferences</a>. Due
		    to Bolthole's privacy features, no member can see what kinds of alerts another member receives.</p>
         <p>All alerts you receive during an online session will remain in the list until you either log out or
            click the Clear button below the list box.</p>
         <p><a href="#intro">Back to Top</a></p>
         <a name="following"><h3>5.3 Following</h3></a>
         <p>This box lists members whose <A href="#profiles">profiles</a> you've bookmarked. Unlike social media
		    sites, this feature is designed so as not to encourage "collecting" followers or fishing for follow-backs.
			Due to Bolthole's privacy features, a member cannot see who follows or unfollows him or her, nor how many
			followers any member has.</p>
         <p><a href="#intro">Back to Top</a></p>
         <a name="preferences"><h3>5.4 Preferences</h3></a>
         <p>This is where you change settings that affect your online experience. While some preferences may take
            effect instantly, they'll revert to their previous settings when you log out. You must click the
            Save button below the Preferences panel to make your settings permanent.</p>
         <a name="change-password"><h4>5.4.1 Change Password</h4></a>
         <p>When you click this link, you'll be prompted to enter a new password.</p>
         <a name="theme"><h4>5.4.2 Theme</h4></a>
         <p>This field changes the color scheme of the website. The change will be instant, but
            you must click the Save button to make your selection permanent.</p>
         <p>If you have automatic <a href="#dark-mode">dark mode</a> switching enabled when you change the theme, the color scheme may change
            for a few seconds and then switch back to dark mode. This is normal, and will not affect your saved
            theme selection.</p>
         <a name="cursor"><h4>5.4.3 Cursor Trail</h4></a>
         <p>This field selects one of several variations on a popular visual effect from the 1990s. The effect consists
		    of animated text or images that follow the mouse pointer when you move it around the screen. You must
            save your preferences, reload the page, and log in again for the setting to take effect.</p>
         <a name="sound"><h4>5.4.4 Sound Effects</h4></a>
         <p>This setting enables or disables sound effects.</p>
         <a name="dark-mode"><h4>5.4.5 Dark Mode</h4>
		 <p>Dark mode is a special greyscale color scheme that's easier on the eyes when reading
            from a computer screen at night.</p>
         <p>These fields select the times when the color scheme automatically switches between your chosen
		    <a href="#theme">theme</a> and dark mode. To disable automatic dark mode switching, choose "Never" from
			the Switch On At options.</p>
         <a name="alert-filters"><h4>5.4.6 Alert Filters</h4></a>
         <p>These checkboxes select what kinds of <a href="#alerts">alerts</a> you'll receive.</p>
         <p><a href="#intro">Back to Top</a></p>
		 <a name="profile"><h3>5.5 Profile Editor</h3></a>
         <a name="view-profile"><h4>5.5.1 View My Profile</h4></a>
		 <p>Clicking this link will cause your profile to be displayed in the main content area in the middle of the screen.</p>
		 <a name="blog-post"><h4>5.5.2 New Blog Post</h4></a>
		 <p>This link will open a popup blog post editor dialog.</p>
		 <a name="upload"><h4>5.5.3 Upload File</h4></a>
		 <p>This link will open a popup file upload dialog.</p>
		 <a name="display-name"><h4>5.5.4 Display Name</h4></a>
		 <p>You can choose to display an alternate screen name on your profile and in the headers of your bulletin board posts,
		    blog posts, and uploaded file description pages. The display name may contain any characters, including emojis.</p>
		 <a name="custom-title"><h4>5.5.5 Custom Title</h4></a>
		 <p>Your custom title is the motto or byline that appears below your display name in content you've posted.</p>
		 <a name="avatar"><h4>5.5.6 Avatar</h4></a>
		 <p>Your avatar is a small picture that represents your online persona.</p>
		 <a name="website"><h4>5.5.7 Website</h4></a>
		 <p>If you have your own website, you can put a link to it here.</p>
		 <a name="signature"><h4>5.5.8 Signature</h4></a>
		 <p>Your signature is a small bit of text that appears below all of your posted content.</p>
		 <a name="about-me"><h4>5.5.9 About Me</h4></a>
		 <p>This is the biographical text that will appear on your profile.</p>
         <p><a href="#intro">Back to Top</a></p>
         <a name="bbs"><h2>6. Bulletin Board</h2></a>

         <a name="profiles"><h2>7. Profiles</h2></a>

         <a name="chat"><h2>8. Chat</h2></a>

         <a name="comments"><h2>9. Comments</h2></a>

      </main>
    </div>
  </body>
</html>
