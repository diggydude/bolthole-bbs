var Following = {

  "followed" : [],

  "init"     : function()
               {
                 if (window.localStorage.getItem('following') == null) {
                   window.localStorage.setItem('following', JSON.stringify([]));
                 }
                 this.followed = JSON.parse(window.localStorage.getItem('following'));
                 this.show();
               }, // init

  "follow"   : function(userId, username)
               {
                 userId = parseInt(userId);
                 this.followed[userId] = username;
                 window.localStorage.setItem('following', JSON.stringify(this.followed));
                 $('follow-button').style.display   = "none";
                 $('unfollow-button').style.display = "block";
                 this.show();
               }, // follow

  "unfollow" : function(userId)
               {
                 userId = parseInt(userId);
                 if (userId in this.followed) {
                   this.followed.splice(userId, 1);
                   window.localStorage.setItem('following', JSON.stringify(this.followed));
                   $('unfollow-button').style.display = "none";
                   $('follow-button').style.display   = "block";
                   this.show();
                 }
               }, // unfollow

  "follows"  : function(userId)
               {
                 userId = parseInt(userId);
                 return (userId in this.followed);
               }, // follows

  "show"     : function()
               {
                 var link;
                 $('following').innerHTML = "";
                 for (var i = 0; i < this.followed.length; i++) {
                   if (typeof(this.followed[i]) === "string") {
                     link = "<div><a href=\"#\" class=\"profile-link\" data-userId=\"" + i + "\" \">" + this.followed[i] + "</a></div>";
                     $('following').innerHTML += link;
                   }
                 }
               } // show

}; // Following

window.addEventListener('load',
  function()
  {
    Following.init();
  }
);