var Preferences = {

  "theme"              : 0,
  "cursor"             : 2,
  "sounds"             : 1,
  "notifyReply"        : true,
  "notifyVisit"        : true,
  "notifyMention"      : true,
  "notifyComment"      : true,
  "notifyDownload"     : true,
  "notifyBkmkProfile"  : true,
  "notifyBkmkBlogPost" : true,
  "notifyBkmkUpload"   : true,
  "notifyAnyProfile"   : false,
  "notifyAnyBlogPost"  : false,
  "notifyAnyUpload"    : false,
  "notifyUserSignup"   : true,
  "notifyUserBanned"   : true,

  "init"               : function()
                         {
                           if (Client.getStorage('preferences') == null) {
                             this.apply();
                             this.setCursorEffect();
                             this.save();
                           }
                           else {
                             this.load();
                           }
                           EventHandlers.apply();
                         }, // init

  "load"               : function()
                         {
                           var prefs = Client.getStorage('preferences');
                           for (var p in prefs) {
                             this[p] = prefs[p];
                           }
                           this.setCursorEffect();
                           this.apply();
                         }, // load

  "save"               : function()
                         {
                           this.theme               = $('settings_theme').selectedIndex;
                           this.cursor              = $('settings_cursor').selectedIndex;
                           this.sounds              = $('settings_sounds').selectedIndex;
                           this.notifyReply         = $('settings_notifyReply').checked;
                           this.notifyVisit         = $('settings_notifyVisit').checked;
                           this.notifyMention       = $('settings_notifyMention').checked;
                           this.notifyComment       = $('settings_notifyComment').checked;
                           this.notifyDownload      = $('settings_notifyDownload').checked;
                           this.notifyBkmkProfile   = $('settings_notifyBkmkProfile').checked;
                           this.notifyBkmkBlogPost  = $('settings_notifyBkmkBlogPost').checked;
                           this.notifyBkmkUpload    = $('settings_notifyBkmkUpload').checked;
                           this.notifyAnyProfile    = $('settings_notifyAnyProfile').checked;
                           this.notifyAnyBlogPost   = $('settings_notifyAnyBlogPost').checked;
                           this.notifyAnyUpload     = $('settings_notifyAnyUpload').checked;
                           this.notifyUserSignup    = $('settings_notifyUserSignup').checked;
                           this.notifyUserBanned    = $('settings_notifyUserBanned').checked;
                           Client.putStorage('preferences', this);
                           Client.showSuccess('Your preferences have been saved.');
                           this.apply();
                         }, // save

  "apply"              : function()
                         {
                           $('settings_theme').selectedIndex  = this.theme;
                           $('settings_cursor').selectedIndex = this.cursor;
                           $('settings_sounds').selectedIndex = (this.sounds) ? 1 : 0;
                           for (var p in this) {
                             if (p.indexOf('notify') > -1) {
                               $('settings_' + p).checked = this[p];
                             }
                           }
                           $('theme').setAttribute('href', './client/theme/' + $('settings_theme').options[this.theme].value + '.css');
                         }, // apply

  "setCursorEffect"    : function()
                         {
                           var script;
                           if (this.cursor == 0) {
                             return;
                           }
                           script = document.createElement('SCRIPT');
                           document.getElementsByTagName('HEAD')[0].appendChild(script);
                           script.setAttribute('id', 'cursor-effect');
                           script.setAttribute('src', './client/cursor/' + $('settings_cursor').options[this.cursor].value + '.js');
                         } // setCursorEffect

}; // Preferences
