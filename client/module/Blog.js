var Blog = {

  "blogId"            : 0,
  "ownerId"           : 0,

  "init"              : function(blogId)
                        {
                          var uri      = "blog.php";
                          var formData = new FormData();
                          this.blogId  = parseInt(blogId);
                          with (formData) {
                            append('command', 'getOwnerId');
                            append('blogId',   this.blogId);
                          }
                          with (Client.request) {
                            open('POST', uri, true);
                            onload = function()
                                     {
                                       var response = JSON.parse(this.responseText);
                                       if (response.success) {
                                         Blog.ownerId = parseInt(response.results.ownerId);
                                         return;
                                       }
                                       Blog.blogId  = 0;
                                       Blog.ownerId = 0;
                                       Client.showError(response.message);
                                     };
                            send(formData);
                          }
                          $('blog-post-editor').innerHTML = Client.render('blog_post_editor', {});
                        }, // init

  "showPost"          : function(postId)
                        {
                          var uri      = "blog.php";
                          var formData = new FormData();
                          with (formData) {
                            append('command', 'getPost');
                            append('postId',  postId);
                          }
                          with (Client.request) {
                            open('POST', uri, true);
                            onload = function()
                                     {
                                       var response = JSON.parse(this.responseText);
                                       if (response.success) {
                                         $('main').innerHTML = Client.render('blog_post', response.results);
                                         return;
                                       }
                                       Client.showError(response.message);
                                     };
                            send(formData);
                          }
						}, // showPost

  "savePost"          : function()
                        {
                          var uri      = "/blog.php";
                          var formData = new FormData();
                          with (formData) {
                            append('command', 'savePost');
                            append('inBlog', $('blogPost_inBlog').value);
                            append('postId', $('blogPost_postId').value);
                            append('title',  $('blogPost_title').value);
                            append('body',   $('blogPost_body').value);
                          }
                          with (Client.request) {
                            open('POST', uri, true);
                            onload = function()
                                     { console.log(this.responseText);
                                       var response = JSON.parse(this.responseText);
                                       if (response.success) {
                                         Client.showSuccess(response.message);
                                         Blog.clearPostEditor();
                                         Blog.hidePostEditor();
                                         $('main').innerHTML = Client.render('blog_post', response.results);
                                         return;
                                       }
                                       Client.showError(response.message);
                                     };
                            send(formData);
                          }
                        }, // savePost

  "showPostEditor"    : function()
                        {
                          $('blog-post-editor-caption').innerHTML = "New Blog Post";
                          $('blogPost_postId').value = 0;
                          $('blogPost_inBlog').value = this.blogId;
                          $('blog-post-editor').style.display = "block";
                        }, // showPostEditor

  "clearPostEditor"   : function()
                        {
                          $('blogPost_postId').value = "";
                          $('blogPost_title').value  = "";
                          $('blogPost_body').value   = "";
                        }, // clearPostEditor

  "hidePostEditor"    : function()
                        {
                          $('blog-post-editor').style.display = "none";
                        }, // hidePostEditor

}; // Blog

Blog.postComment = function()
                   {
                     var uri      = "/blog.php";
                     var formData = new FormData();
                     with (formData) {
                       append('command',   'postComment');
                       append('postId',    $('blog-post-comment-id').value);
                       append('postedBy',  Session.userId);
                       append('body',      $('blog-post-comment-entry').value);
                     }
                     with (Client.request) {
                       open('POST', uri, true);
                       onload  = function()
                                 {
                                   var response = JSON.parse(this.responseText);
                                   if (response.success) {
                                     Client.showSuccess(response.message);
                                     $('main').innerHTML = Client.render('blog_post', response.results);
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
                   }; // Blog.postComment
