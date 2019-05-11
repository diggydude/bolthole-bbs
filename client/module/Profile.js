function Profile()
{

  this.userId      = 0;
  this.username    = "";
  this.displayName = "";
  this.title       = "";
  this.avatar      = "";
  this.banner      = "";
  this.website     = "";
  this.signature   = "";
  this.about       = "";
  this.joined      = "";
  this.comments    = [];
  this.blogId      = 0;
  this.blogPosts   = [];
  this.libraryId   = 0;
  this.files       = [];

  this.fetch       = function(userId)
                     {
                       var profile = this;
                       var uri     = "/profile.php?userId=" + userId + "&viewerId=" + Session.userId;
                       with (Client.request) {
                         open('GET', uri, true);
                         onload = function()
                                  {
                                    var response = JSON.parse(this.responseText);
                                    if (response.success) {
                                      profile.load(response.results);
                                      return;
                                    }
                                    Client.showError(response.message);
                                  };
                         send();
                       }
                     }; // fetch

  this.load        = function(params)
                     {
                       this.userId      = parseInt(params.userId);
                       this.username    = params.username;
                       this.displayName = params.displayName;
                       this.title       = params.title;
                       this.avatar      = params.avatar;
                       this.banner      = params.banner;
                       this.website     = params.website;
                       this.signature   = params.signature;
                       this.about       = params.about;
                       this.rendered    = params.rendered;
                       this.joined      = params.joined;
                       this.comments    = params.comments;
                       this.blogId      = params.blogId;
                       this.blogPosts   = params.blogPosts;
                       this.libraryId   = params.libraryId;
                       this.files       = params.files;
                       if (this.userId == Session.userId) {
                         this.edit();
                       }
                       this.show();
                     }; // load

  this.save        = function()
                     {
                       var profile  = this;
                       var uri      = "/profile.php";
                       var formData = new FormData();
                       with (formData) {
                         append('MAX_FILE_SIZE', Config.maxAvatarSize);
                         append('command',       'save');
                         append('userId',        this.userId);
                         append('displayName',   $('profile_display_name').value);
                         append('title',         $('profile_title').value);
                         append('avatar',        $('profile_avatar').files[0]);
                         append('banner',        $('profile_banner').files[0]);
                         append('website',       $('profile_website').value);
                         append('signature',     $('profile_signature').value);
                         append('about',         $('profile_about').value);
                       }
                       with (Client.request) {
                         open('POST', uri, true);
                         onload  = function()
                                   {
                                     var response = JSON.parse(this.responseText);
                                     if (response.success) {
                                       profile.load(response.results);
                                       Client.showSuccess('Your profile has been saved.');
                                       return;
                                     }
                                     Client.showError(response.message);
                                   };
                         onerror = function()
                                   {
                                     Client.showError('Error ' + this.status + ': ' + this.statusText);
                                   };
                         send(formData);
                       }
                     }; // save

  this.edit        = function()
                     {
                       $('profile_display_name').value = this.displayName;
                       $('profile_title').value        = this.title;
                       $('profile_website').value      = this.website;
                       $('profile_signature').value    = this.signature;
                       $('profile_about').value        = this.about;
                     }; // edit

  this.clearForm   = function()
                     {
                       if (this.userId != Session.userId) {
                         return;
                       }
                       $('profile_display_name').value = "";
                       $('profile_title').value        = "";
                       $('profile_website').value      = "";
                       $('profile_signature').value    = "";
                       $('profile_about').value        = "";
                     }; // clearForm

  this.show        = function()
                     {
                       $('main').innerHTML = Client.render('profile', this);
                       $("defaultTab").click();
                       if (Following.follows(this.userId)) {
                         $('follow-button').style.display   = "none";
                         $('unfollow-button').style.display = "block";
                       }
                       else {
                         $('unfollow-button').style.display = "none";
                         $('follow-button').style.display   = "block";
                       }
                       EventHandlers.apply();
                     }; // show

} // Profile

Profile.postComment  = function()
                       {
                         var uri      = "/profile.php";
                         var formData = new FormData();
                         with (formData) {
                           append('command',   'postComment');
                           append('profileId', $('profile-comment-id').value);
                           append('postedBy',  Session.userId);
                           append('body',      $('profile-comment-entry').value);
                         }
                         with (Client.request) {
                           open('POST', uri, true);
                           onload  = function()
                                     {
                                       var profile;
                                       var response = JSON.parse(this.responseText);
                                       if (response.success) {
                                         Client.showSuccess(response.message);
                                         profile = new Profile();
                                         profile.load(response.results);
                                         profile.show();
                                         $('main').scrollTop = $('main').scrollHeight;
                                         return;
                                       }
                                       Client.showError(response.message);
                                     };
                           onerror = function()
                                     {
                                       Client.showError('Error ' + this.status + ': ' + this.statusText);
                                     };
                           send(formData);
                         }
                       }; // Profile.postComment

Profile.listProfiles = function()
                       {
                         var uri      = "profile.php";
                         var formData = new FormData();
                         formData.append('command', 'listProfiles');
                         with (Client.request) {
                           open('POST', uri, true);
                           onload  = function()
                                     {
                                       var response = JSON.parse(this.responseText);
                                       if (response.success) {
                                         $('main').innerHTML = Client.render('profile_list', response.results);
                                         EventHandlers.apply();
                                         return;
                                       }
                                       Client.showError(response.message);
                                     };
                           onerror = function()
                                     {
                                       Client.showError('Error ' + this.status + ': ' + this.statusText);
                                     };
                           send(formData);
                         }
                       }; // Profile.listProfiles

Profile.search       = function(terms)
                       {
                         var uri      = "profile.php";
                         var formData = new FormData();
                         with (formData) {
                           append('command', 'search');
                           append('terms',   terms);
                         }
                         if (arguments.length > 1) {
                           formData.append('userIds', arguments[1]);
                         }
                         with (Client.request) {
                           open('POST', uri, true);
                           onload  = function()
                                     {
                                       var response = JSON.parse(this.responseText);
                                       if (response.success) {
                                         $('main').innerHTML = Client.render('profile_list', response.results);
                                         EventHandlers.apply();
                                         return;
                                       }
                                       Client.showError(response.message);
                                     };
                           onerror = function()
                                     {
                                       Client.showError('Error ' + this.status + ': ' + this.statusText);
                                     };
                           send(formData);
                         }
                       }; // Profile.search
