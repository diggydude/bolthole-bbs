# bolthole-bbs
Everything fun about the Internet packed into a tiny website platform!

Documentation coming soon.

**CAUTION: This is a preview release and not recommended for use in production.**

Installation:

(1) Upload the files to your document root (`htdocs`, `public_html`, `www`, or whatever).

(2) Import `conf/schema.sql` using phpMyAdmin, MySQL Query Browser, or the MySQL command line.

(3) Edit `conf/install.php` and run it. Be sure to change the parameters for the System and Sysop accounts at the bottom of the file. These accounts don't have any special privileges yet, but that's subject to change in the near future.

The installer will create a configuration file, `conf/config.conf`. Delete `conf/install.php` or move it outside of your document root after you run it. As `conf/config.conf` will contain your database login credentials, it's highly recommended that you move it outside of your document root also, and edit `conf/init_http.php` to reflect its new location.

Features:

* AJAX chat, instant messaging, instant notifications, blogging, and file sharing

* User-friendly flexbox layout keeps all features and controls one click away

* Instant user-selectable theme changes

* User-selectable old school cursor trails 

* All of the old school HTML tags (and some new ones) emulated in bbCode, e.g., `[blink]`, `[marquee]`, `[rainbow]`, `[gradient]`, `[spoiler]`, and `[youtube]`

* AOL sound effects

* Modern social media features like `#hashtags`, `@mentions`, follows, and comments

* Unique threaded discussion system with multiple tree view filters

* User settings and private messages stored in localStorage for maximum privacy
