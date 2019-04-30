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
                              var response = JSON.parse(this.responseText);
                              if (response.success) {
                                $('main').innerHTML = Client.render('hashtag', response.results);
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