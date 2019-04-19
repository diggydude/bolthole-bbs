var Session = {

  "id"             : Cookies.get('session_id'),
  "userId"         : 0,
  "username"       : "",
  "profile"        : null,

  "showForm"       : function(form)
                     {
                       switch (form) {
                         case "sign-in":
                           Client.showModal('Sign In', 'sign_in', {}, false);
                           break;
                         case "sign-up":
                           Client.showModal('Sign Up', 'sign_up', {}, false);
                           break;
                         case "change-password":
                           Client.showModal('Change Password', 'change_password', {"userId" : arguments[1]}, false);
                           break;
                         case "lost-password":
                           Client.showModal('Recover Account (Step 1 of 2)', 'lost_password', {}, false);
                           break;
                         case "recover-account":
                           Client.showModal('Recover Account (Step 2 of 2)', 'recover_account',
                             {"question" : arguments[1], "username" :arguments[2]}, false
                           );
                           break;
                         default:
                           return;
                       }
                     }, // showForm

  "login"          : function()
                     {
                       var uri      = "/account.php";
                       var formData = new FormData();
                       formData.append('command',  'signIn');
                       formData.append('username', $('sign-in-username').value);
                       formData.append('password', $('sign-in-password').value);
                       with (Client.request) {
                         open('POST', uri, true);
                         onload  = function()
                                   {
                                     var response = JSON.parse(this.responseText);
                                     if (response.success) {
                                       Session.load(response.results);
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
                     }, // login

  "logout"         : function()
                     {
                       var uri = "/account.php";
                       var formData = new FormData;
                       formData.append('command', 'signOut');
                       formData.append('userId',  this.userId);
                       with (Client.request) {
                         open('POST', uri, true);
                         onload  = function()
                                   {
                                     var response = JSON.parse(this.responseText);
                                     if (response.success) {
                                       Session.quit();
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
                     }, // logout

  "register"       : function()
                     {
                       var uri      = "/account.php";
                       var formData = new FormData();
                       formData.append('command',  'signUp');
                       formData.append('username', $('sign-up-username').value);
                       formData.append('password', $('sign-up-password').value);
                       formData.append('again',    $('sign-up-password-again').value);
                       formData.append('question', $('sign-up-question').value);
                       formData.append('answer',   $('sign-up-answer').value);
                       with (Client.request) {
                         open('POST', uri, true);
                         onload  = function()
                                   {
                                     var response = JSON.parse(this.responseText);
                                     if (response.success) {
                                       Session.load(response.results);
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
                     }, // register

  "changePassword" : function()
                     {
                       var uri = "/account.php";
                       var formData = new FormData();
                       formData.append('command',  'changePassword');
                       formData.append('userId',   $('chg-pwd-userId').value);
                       formData.append('password', $('chg-pwd-password').value);
                       formData.append('again',    $('chg-pwd-again').value);
                       with (Client.request) {
                         open('POST', uri, true);
                         onload  = function()
                                   {
                                     var response = JSON.parse(this.responseText);
                                     if (response.success) {
                                       Session.load(response.results);
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
                     }, // changePassword

  "getQuestion"    : function()
                     {
                       var uri      = "/account.php";
                       var formData = new FormData();
                       formData.append('command',  'lostPassword');
                       formData.append('username', $('recover-username').value);
                       with (Client.request) {
                         open('POST', uri, true);
                         onload  = function()
                                   {
                                     var response = JSON.parse(this.responseText);
                                     if (response.success) {
                                       Session.showForm('recover-account', response.results.question, response.results.username);
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
                     }, // getQuestion

  "recoverAccount" : function()
                     {
                       var uri      = "/account.php";
                       var formData = new FormData();
                       formData.append('command',   'recover');
                       formData.append('username',  $('recover-username').value);
                       formData.append('answer',    $('recover-answer').value);
                       with (Client.request) {
                         open('POST', uri, true);
                         onload  = function()
                                   {
                                     var params;
                                     var response = JSON.parse(this.responseText);
                                     if (response.success) {
                                       Session.showForm('change-password', response.results.userId);
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
                     }, // recoverAccount

  "load"           : function(params)
                     {
                       this.userId   = params.userId;
                       this.username = params.username;
                       this.profile  = new Profile();
                       this.profile.load(params);
                       Preferences.init();
                       Following.init();
                       Mailbox.init();
                       $('welcome').innerHTML = Client.render('welcome', {"username" : this.username});
                       if (Preferences.sounds) {
                         $('welcome-audio').play();
                       }
                       Client.clearModal();
                       Chat.init();
                       Forum.init();
                       Blog.init(this.profile.blogId);
                     }, // load

  "quit"           : function()
                     {
                       if (Preferences.sounds) {
                         $('goodbye-audio').play();
                       }
                       Mailbox.quit();
                       Chat.quit();
                       Session.profile.clearForm();
                       Session.userId   = 0;
                       Session.username = "";
                       Session.profile  = null;
                       Session.showForm('sign-in');
                       Cookies.remove('session_id');
                       $('welcome').innerHTML = "Please sign in or register.";
                       $('main').innerHTML = "";
                     } // quit

}; // Session
