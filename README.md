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

* All of the old school HTML tags (and some new ones) emulated in bbCode, e.g., `[blink]`, `[marquee]`, `[rainbow]`, `[gradient]`, `[spoiler]`, `[ansi]`, and `[youtube]`

* AOL sound effects

* IRC-style `/me` and `/action` commands -- more commands coming!

* Modern social media features like `#hashtags`, `@mentions`, follows, and comments

* Unique threaded discussion system with multiple tree view filters

* User settings and private messages stored in localStorage for maximum privacy

* All features powered by Comet (XMLHttpRequest + JSON) for bandwidth efficiency, and rendered on the client side with JavaScript templates

A more comprehensive description and explanation of features, along with their design rationale, will appear at the end of this file.

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

* Generate more themes, including dark mode

* Automatic dark mode scheduling

**Rationale**

All timestamps are stored as Greenwich Mean Time and automatically converted to the user's local timezone when displayed. (This is broken in a couple of places but will be fixed soon.) See `client/misc.js` for the conversion function.

The Preferences and Following modules operate completely on the client side. No data is sent to the server. As a consequence, no one will be able to see who you're following, or know you're following them. Mister Obvious envisioned this feature as a way to bookmark profiles of interest rather than as a means of collecting followers and fishing for follow-backs, as seen on Twitter. I only labeled the list "Following" because that's a concept most users will be familiar with.

Similarly, alert filtering is done on the client side. No one can see what kinds of alerts you choose to receive.

In general, the platform is specifically designed not to encourage narcissism and fame-whoring. Pursuant to that end, there is no "like" button or any other kind of voting or scoring mechanism. The expectation is that user-generated content will stand on its own merits, and that users who don't fit into the community will leave in short order. There are no "algorithms" to manipulate your endorphine levels and what-not. Users will enjoy the platform because it's quirky and just plain fun.

The platform is intended to support closed communities, and isn't particularly well-suited to reaching a vast audience or promoting content. Since all displayed content is rendered in JavaScript templates and activated by event listeners (all hyperlinks have "#" as their href), there is no way for search engines to crawl the site, nor for users to bookmark or link to specific content from outside the website. For that matter, no one can even see any content unless they're logged in.

Some features are deliberately less automated than they could be. Mister Obvious wanted to encourage users to learn how things work at a somewhat lower level, rather than have everything done for them. For example, YouTube URLs are not automatically converted to embedded video players. The user will have to paste the video ID into `[youtube]` tags to accomplish that. Not all tags are allowed in all areas. Users can post whatever they want on their profiles and blogs, but many tags are disallowed in the forum and chat to keep the noise level down. Likewise, embedding ANSI art isn't very straightforward, as will be explained later.
