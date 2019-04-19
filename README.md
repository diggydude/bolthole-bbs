# bolthole-bbs
Everything fun about the Internet packed into a tiny website platform!

Documentation coming soon.

**CAUTION: This is a preview release and not recommended for use in production.**

**Installation:**

(1) Upload the files to your document root (`htdocs`, `public_html`, `www`, or whatever).

(2) Import `conf/schema.sql` using phpMyAdmin, MySQL Query Browser, or the MySQL command line.

(3) Edit `conf/install.php` and run it. Be sure to change the parameters for the System and Sysop accounts at the bottom of the file. These accounts don't have any special privileges yet, but that's subject to change in the near future.

The installer will create a configuration file, `conf/config.conf`. Delete `conf/install.php` or move it outside of your document root after you run it. As `conf/config.conf` will contain your database login credentials, it's highly recommended that you move it outside of your document root also, and edit `conf/init_http.php` to reflect its new location.

If you're running a Windows server and want to enable inline ANSI art in profiles and blog posts, you'll need to download ansifilter and set its path in the installer.

http://www.andre-simon.de/doku/ansifilter/en/ansifilter.php

This feature isn't available for Linux yet.

**Features:**

* Instant messaging, instant notifications, blogging, and file sharing

* User-friendly flexbox layout keeps all features and controls one click away

* Instant user-selectable theme changes

* User-selectable old school cursor trails 

* All of the old school HTML tags (and some new ones) emulated in bbCode, e.g., `[blink]`, `[marquee]`, `[rainbow]`, `[gradient]`, `[spoiler]`, and `[youtube]`

* AOL sound effects

* IRC-style `/me` and `/action` commands -- more commands coming!

* Modern social media features like `#hashtags`, `@mentions`, follows, and comments

* Unique threaded discussion system with multiple tree view filters

* User settings and private messages stored in localStorage for maximum privacy

* All features powered by Comet (XMLHttpRequest + JSON) for bandwidth efficiency, and rendered on the client side with JavaScript templates

**Acknowledgements:**

Many thanks to Mister Obvious for inspiring this project, providing numerous ideas and suggestions, and reminding me what made the 1990s Web so damned much fun!

http://sectual.com

Thanks to Mark Turansky for the Better JavaScript Templates rendering engine that powers this platform.

http://blog.markturansky.com/BetterJavascriptTemplates.html

Thanks to Roy Whittle, Doctor Thaddeus Ozone, and Tim Holman for the 1990s cursor trail effects, as recently featured on Stack Overflow.

http://www.ozones.com/

https://github.com/tholman/cursor-effects

Thanks to Klaus Hartl and Fagner Brack for JavaScript Cookie.

https://github.com/js-cookie/js-cookie

Thanks to Nigel McNie, Benny Baumann, and Milian Wolff for GeSHi syntax highlighter.

http://qbnz.com/highlighter/

**To Do:**

* Implement proper PHP password security

* Minify JavaScript and CSS files

* Make `#hashtag` links actually do something

* IRC `/kick` and `/ban` commands

* HTML frontend for installer

* ANSI art viewer for Linux hosts

* Spoiler decoder
