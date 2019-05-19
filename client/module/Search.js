var Search = {

  "search"  : function()
              {
                var terms = $('site-search-terms').value;
                var where = $('site-search-where').options[$('site-search-where').selectedIndex].value;
                switch (where) {
                  case "forum":
                  default:
                    Forum.search(terms);
                    return;
                  case "profiles":
                    Profile.search(terms);
                    return;
                  case "files":
                    UserFiles.search(terms);
                    return;
                  case "blog":
                    Blog.search(terms);
                    return;
                }
              }, // search

  "hashtag" : function(tag)
              {
                var uri = "/hashtag.php";
                var formData = new FormData();
                with (formData) {
                  append('command', 'go');
                  append('tag',     tag);
                }
                with (Client.request) {
                  open('POST', uri, true);
                  onload  = function()
                            {
                              var hashtagResults, i, postId;
                              var response = JSON.parse(this.responseText);
                              if (response.success) {
                                Forum.searchResults = {
                                  "threads" : response.results.forum.threads,
                                  "posts"   : response.results.forum.posts
                                };
                                hashtagResults = {
                                  "hashtag"  : response.results.hashtag,
                                  "profiles" : response.results.profiles,
                                  "blogs"    : response.results.blogs,
                                  "files"    : response.results.files,
                                  "posts"    : []
                                };
                                for (i = 0; i < response.results.forum.posts.length; i++) {
                                  postId = response.results.forum.posts[i].postId;
                                  if (response.results.forum.relevant.includes(postId)) {
                                    hashtagResults.posts.push(response.results.forum.posts[i]);
                                  }
                                }
                                $('main').innerHTML = Client.render('hashtag', hashtagResults);
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
              } // hashtag

}; // Search