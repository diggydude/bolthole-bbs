var Forum = {

  "threads"           : [],
  "posts"             : [],
  "currentPostId"     : 0,
  "destination"       : 0,
  "treeView"          : "branch",
  "searchResults"     : null,

  "init"              : function()
                        {
                          $('forum-post-editor').innerHTML = Client.render('forum_post_editor', {});
                          this.show();
                        }, // init

  "show"              : function()
                        {
                          if (this.searchResults != null) {
                            this.threads = this.searchResults.threads;
                            this.posts   = this.searchResults.posts;
                            if (this.threads.length > 0) {
                              if (this.destination > 0) {
                                this.showTopic(this.getPostById(this.destination).inThread);
                                this.currentPostId = this.destination;
                                this.destination   = 0;
                                $('forum-clear-search-button').style.display = "inline";
                                this.showPost(this.currentPostId);
                                return;
                              }
                              $('forum-clear-search-button').style.display = "inline";
                              this.showTopic(this.threads[0].threadId);
                              return;
                            }
                            EventHandlers.apply();
                            return;
                          }
                          var uri = "/forum.php";
                          var formData = new FormData();
                          formData.append('command', 'getRecent');
                          with (Client.request) {
                            open('POST', uri, true);
                            onload = function()
                                     {console.log(this.responseText);
                                       var response = JSON.parse(this.responseText);
                                       if (response.success) {
                                         Forum.threads = response.results.threads;
                                         Forum.posts   = response.results.posts;
                                         $('main').innerHTML = Client.render('forum', Forum);
                                         if (Forum.threads.length > 0) {
                                           if (Forum.destination > 0) {
                                             Forum.showTopic(Forum.getPostById(Forum.destination).inThread);
                                             Forum.currentPostId = Forum.destination;
                                             Forum.destination   = 0;
                                             $('forum-clear-search-button').style.display = "none";
                                             Forum.showPost(Forum.currentPostId);
                                             return;
                                           }
                                           $('forum-clear-search-button').style.display = "none";
                                           Forum.showTopic(Forum.threads[0].threadId);
                                           return;
                                         }
						                 $('forum-clear-search-button').style.display = "none";
                                         EventHandlers.apply();
                                         return;
                                       }
                                       Client.showError(response.message);
                                     };
                            send(formData);
                          }
                        }, // show

  "showTopic"         : function(threadId)
                        {
                          var tree, currentPostId;
                          var store = [];
                          for (var i = 0; i < this.posts.length; i++) {
                            if (this.posts[i].inThread == threadId) {
                              store.push(this.posts[i]);
                              if (this.posts[i].inReplyTo == 0) {
                                this.currentPostId = this.posts[i].postId;
                              }
                            }
                          }
                          tree = new Tree();
                          tree.importStore('postId', 'inReplyTo', store);
                          $('forum-post-tree').innerHTML = tree.graph('forum_post_tree_item');
                          this.showPost(this.currentPostId);
                          EventHandlers.apply();
                        }, // showTopic

  "showPost"          : function(postId)
                        {
                          var post;
                          if ((post = this.getPostById(postId)) == null) {
                            Client.showError('Post not found. Try reloading the forum.');
                            return;
                          }
                          $('forum-post-view').innerHTML = Client.render('forum_post', post);
                          switch (this.treeView) {
                            case "branch":
                            default:
                              this.showBranch();
                              break;
                            case "limb":
                              this.showLimb();
                              break;
                            case "stem":
                              this.showStem();
                              break;
                          }
                          EventHandlers.apply();
                        }, // showPost

  "showBranch"        : function()
                        {
                          var store     = [];
                          var threadId  = this.getPostById(this.currentPostId).inThread;
                          var tree      = new Tree();
                          this.treeView = "branch";
                          for (var i = 0; i < this.posts.length; i++) {
                            if (this.posts[i].inThread == threadId) {
                              store.push(this.posts[i]);
                            }
                          }
                          tree.importStore('postId', 'inReplyTo', store);
                          $('forum-post-tree').innerHTML = tree.graph('forum_post_tree_item');
                          EventHandlers.apply();
                        }, // showBranch

  "showLimb"          : function()
                        {
                          var limb;
                          var store     = [];
                          var threadId  = this.getPostById(this.currentPostId).inThread;
                          var tree      = new Tree();
                          this.treeView = "limb";
                          for (var i = 0; i < this.posts.length; i++) {
                            if (this.posts[i].inThread == threadId) {
                              store.push(this.posts[i]);
                            }
                          }
                          tree.importStore('postId', 'inReplyTo', store);
                          limb = tree.getNodeById(this.currentPostId).getLimb();
                          $('forum-post-tree').innerHTML = limb.graph('forum_post_tree_item');
                          EventHandlers.apply();
                        }, // showLimb

  "showStem"       : function()
                        {
                          var stem;
                          var store     = [];
                          var threadId  = this.getPostById(this.currentPostId).inThread;
                          var tree      = new Tree();
                          this.treeView = "stem";
                          for (var i = 0; i < this.posts.length; i++) {
                            if (this.posts[i].inThread == threadId) {
                              store.push(this.posts[i]);
                            }
                          }
                          tree.importStore('postId', 'inReplyTo', store);
                          stem = tree.getNodeById(this.currentPostId).getStem();
                          $('forum-post-tree').innerHTML = stem.graph('forum_post_tree_item');
                          EventHandlers.apply();
                        }, // showStem

  "showPostEditor"    : function()
                        {
                          $('forum-post-editor').style.display = "block";
                        }, // showPostEditor

  "clearPostEditor"   : function()
                        {
                          $('forumPost_replyTo').value = "";
                          $('forumPost_topic').value   = "";
                          $('forumPost_body').value    = "";
                        }, // clearPostEditor

  "hidePostEditor"    : function()
                        {
                          $('forum-post-editor').style.display = "none";
                        }, // hidePostEditor

  "postMessage"       : function()
                        {
                          var uri      = "/forum.php";
                          var formData = new FormData();
                          with (formData) {
                            append('command',   'postMessage');
                            append('inReplyTo', $('forumPost_replyTo').value);
                            append('topic',     $('forumPost_topic').value);
                            append('body',      $('forumPost_body').value);
                          }
                          with (Client.request) {
                            open('POST', uri, true);
                            onload = function()
                                     {console.log(this.responseText);
                                       var response = JSON.parse(this.responseText);
                                       if (response.success) {
                                         Client.showSuccess(response.message);
                                         Forum.clearPostEditor();
                                         Forum.hidePostEditor();
                                         Forum.threads       = response.results.threads;
                                         Forum.posts         = response.results.posts;
                                         Forum.destination   = response.results.postId;
                                         $('main').innerHTML = Client.render('forum', Forum);
                                         Forum.showTopic(Forum.getPostById(Forum.destination).inThread);
                                         Forum.currentPostId = Forum.destination;
                                         Forum.destination   = 0;
                                         Forum.showPost(Forum.currentPostId);
                                         return;
                                       }
                                       Client.showError(response.message);
                                     };
                            send(formData);
                          }
                        }, // postMessage

  "getPostById"       : function(postId)
                        {
                          for (var i = 0; i < this.posts.length; i++) {
                            if (this.posts[i].postId == postId) {
                              return this.posts[i];
                            }
                          }
                          return null;
                        }, // getPostById

  "search"            : function(terms)
                        {
                          var uri = "/forum.php";
                          var formData = new FormData();
                          with (formData) {
                            append('command', 'search');
                            append('terms',   terms);
                          }
                          with (Client.request) {
                            open('POST', uri, true);
                            onload = function()
                                     {console.log(this.responseText);
                                       var response = JSON.parse(this.responseText);
                                       if (response.success) {
                                         Forum.searchResults = {
                                                                 "threads" : response.results.threads,
                                                                 "posts"   : response.results.posts
                                                               };
                                         Forum.treeView = "branch";
                                         Forum.threads  = response.results.threads;
                                         Forum.posts    = response.results.posts;
                                         $('main').innerHTML = Client.render('forum', Forum);
                                         if (Forum.threads.length > 0) {
                                           Forum.showTopic(Forum.threads[0].threadId);
                                           return;
                                         }
                                         EventHandlers.apply();
                                         return;
                                       }
                                       Client.showError(response.message);
                                     };
                            send(formData);
                          }
                        } // search

}; // Forum