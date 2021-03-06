var Following = {

  "followed" : [],

  "init"     : function()
               {
                 if (Client.getStorage('following') == null) {
                   Client.putStorage('following', []);
                 }
                 this.followed = Client.getStorage('following');
                 this.show();
                 $('following_search').disabled = true;
               }, // init

  "follow"   : function(userId, username)
               {
                 userId = parseInt(userId);
                 this.followed[userId] = username;
                 Client.putStorage('following', this.followed);
                 $('follow-button').style.display   = "none";
                 $('unfollow-button').style.display = "block";
                 this.show();
               }, // follow

  "unfollow" : function(userId)
               {
                 userId = parseInt(userId);
                 if (userId in this.followed) {
                   this.followed.splice(userId, 1);
                   Client.putStorage('following', this.followed);
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
                     link = "<div><img src=\"./client/icons/bookmark_icon.png\" alt=\"bookmarked\" /> <a href=\"#\" class=\"profile-link\" data-userId=\"" + i + "\" \">" + this.followed[i] + "</a></div>";
                     $('following').innerHTML += link;
                   }
                 }
               }, // show

  "search"   : function()
               {
                 var terms   = $('following_search_terms').value;
                 var userIds = "";
                 var ids     = [];
                 for (var id in this.followed) {
                   ids.push(id);
                 }
                 userIds = ids.join(",");
                 Profile.search(terms, userIds);
               } // search

}; // Following
