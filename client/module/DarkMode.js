var DarkMode = {

  "start"      : 0,
  "end"        : 0,
  "handle"     : null,

  "run"        : function()
                 {
                   if (this.handle) {
                     this.stop();
                   }
                   switch (Preferences.darkModeOn) {
                     case 1:
                       this.start = 17;
                       break;
                     case 2:
                       this.start = 17.5;
                       break;
                     case 3:
                       this.start = 18;
                       break;
                     case 4:
                       this.start = 18.5;
                       break;
                     case 5:
                       this.start = 19;
                       break;
                     case 6:
                       this.start = 19.5;
                       break;
                     case 7:
                       this.start = 20;
                       break;
                     case 8:
                       this.start = 20.5;
                       break;
                     case 9:
                       this.start = 21;
                       break;
                     case 10:
                       this.start = 21.5;
                       break;
                   }
                   switch (Preferences.darkModeOff) {
                     case 0:
                       this.end = 0;
                       break;
                     case 1:
                       this.end = 5;
                       break;
                     case 2:
                       this.end = 5.5;
                       break;
                     case 3:
                       this.end = 6;
                       break;
                     case 4:
                       this.end = 6.5;
                       break;
                     case 5:
                       this.end = 7;
                       break;
                     case 6:
                       this.end = 7.5;
                       break;
                     case 7:
                       this.end = 8;
                       break;
                     case 8:
                       this.end = 8.5;
                       break;
                     case 9:
                       this.end = 9;
                       break;
                     case 10:
                       this.end = 9.5;
                       break;
                   }
                   this.handle = Client.taskList.add(
                     function()
                     {
                       var date = new Date();
                       var now  = date.getHours() + (date.getMinutes() / 60);
                       if ((DarkMode.start == 0) || ((now > DarkMode.end) && (now < DarkMode.start))) {
                         $('theme').setAttribute('href', './client/theme/' + $('settings_theme').options[Preferences.theme].value + '.css');
                         return;
                       }
                       $('theme').setAttribute('href', './client/theme/360.css');
                     }, 'slow'
                   );
                 }, // run

  "stop"       : function()
                 {
                   Client.taskList.remove(this.handle);
                   this.handle = null;
                 } // stop

}; // DarkMode