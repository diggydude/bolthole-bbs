var EventHandlers = {

  "handlers"  : [
                  {"event" : "click",
                   "id"    : "show-forum-link",
                   "class" : null,
                   "func"  : function()
                             {
                               Forum.show();
                             }
                  },
                  {"event" : "click",
                   "id"    : "list-profiles-link",
                   "class" : null,
                   "func"  : function()
                             {
                               Profile.listProfiles();
                             }
                  },
                  {"event" : "click",
                   "id"    : "help-link",
                   "class" : null,
                   "func"  : function()
                             {
                               Client.showHelp();
                             }
                  },
                  {"event" : "click",
                   "id"    : "sign-in-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Session.login();
                             }
                  },
                  {"event" : "click",
                   "id"    : "lost-password-link",
                   "class" : null,
                   "func"  : function()
                             {
                               Session.showForm('lost-password');
                             }
                  },
                  {"event" : "click",
                   "id"    : "sign-up-link",
                   "class" : null,
                   "func"  : function()
                             {
                               Session.showForm('sign-up');
                             }
                  },
                  {"event" : "click",
                   "id"    : "sign-up-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Session.register();
                             }
                  },
                  {"event" : "click",
                   "id"    : "change-password-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Session.changePassword();
                             }
                  },
                  {"event" : "click",
                   "id"    : "lost-password-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Session.getQuestion();
                             }
                  },
                  {"event" : "click",
                   "id"    : "recover-account-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Session.recoverAccount();
                             }
                  },
                  {"event" : "click",
                   "id"    : "sign-out-link",
                   "class" : null,
                   "func"  : function()
                             {
                               Session.logout();
                             }
                  },
                  {"event" : "click",
                   "id"    : "view-my-profile-link",
                   "class" : null,
                   "func"  : function()
                             {
                               Session.profile.show();
                             }
                  },
                  {"event" : "click",
                   "id"    : "settings-change-password-link",
                   "class" : null,
                   "func"  : function()
                             {
                               Session.showForm('change-password', Session.userId);
                             }
                  },
                  {"event" : "click",
                   "id"    : "profile_save",
                   "class" : null,
                   "func"  : function()
                             {
                               Session.profile.save();
                             }
                  },
                  {"event" : "click",
                   "id"    : "success-message",
                   "class" : null,
                   "func"  : function()
                             {
                               this.style.display = "none";
                             }
                  },
                  {"event" : "click",
                   "id"    : "error-message",
                   "class" : null,
                   "func"  : function()
                             {
                               this.style.display = "none";
                             }
                  },
                  {"event" : "click",
                   "id"    : null,
                   "class" : "profile-link",
                   "func"  : function()
                             {
                               var userId  = parseInt(this.getAttribute('data-userId'));
                               var profile = new Profile();
                               profile.fetch(userId);
                             }
                  },
                  {"event" : "click",
                   "id"    : null,
                   "class" : "hashtag-link",
                   "func"  : function()
                             {
                               var hashtag = this.getAttribute('data-hashtag');
                               Search.hashtag(hashtag);
                             }
                  },
                  {"event" : "keyup",
                   "id"    : "chatMessage",
                   "class" : null,
                   "func"  : function(event)
                             {
                               $('chatSend').disabled = ($('chatMessage').value == "");
                               var code = event.charCode || event.keyCode;
                               if (code == 13) {
                                 Chat.send();
                               }
                             }
                  },
                  {"event" : "click",
                   "id"    : "chatSend",
                   "class" : null,
                   "func"  : function()
                             {
                               Chat.send();
                             }
                  },
                  {"event" : "click",
                   "id"    : "delete-mail-button",
                   "class" : null,
                   "func"  : function()
                             {
                               var messageId = parseInt(this.getAttribute('data-messageId'));
                               if (confirm('Do you really want to delete this message?')) {
                                 Mailbox.delete(messageId);
                               }
                             }
                  },
                  {"event" : "click",
                   "id"    : "follow-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Following.follow($('follow-userId').value, $('follow-username').value);
                             }
                  },
                  {"event" : "click",
                   "id"    : "unfollow-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Following.unfollow($('follow-userId').value);
                             }
                  },
                  {"event" : "keyup",
                   "id"    : "following_search_terms",
                   "class" : null,
                   "func"  : function(event)
                             {
                               var code = event.charCode || event.keyCode;
                               if (this.value == "") {
                                 $('following_search').disabled = true;
                                 return;
                               }
                               $('following_search').disabled = false;
                               if (code == 13) {
                                 Following.search();
                                 this.value = "";
                                 $('following_search').disabled = true;
                               }
                             }
                  },
                  {"event" : "click",
                   "id"    : "following_search",
                   "class" : null,
                   "func"  : function()
                             {
                               Following.search();
                               $('following_search_terms').value = "";
                               this.disabled = true;
                             }
                  },
                  {"event" : "click",
                   "id"    : "profile-comment-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Profile.postComment();
                             }
                  },
                  {"event" : "click",
                   "id"    : null,
                   "class" : "blog-post-link",
                   "func"  : function()
                             {
                               var postId = parseInt(this.getAttribute('data-postId'));
                               Blog.showPost(postId);
                             }
                  },
                  {"event" : "click",
                   "id"    : null,
                   "class" : "file-details-link",
                   "func"  : function()
                             {
                               var fileId = parseInt(this.getAttribute('data-fileId'));
                               UserFiles.showFileDetails(fileId);
                             }
                  },
                  {"event" : "click",
                   "id"    : "new-blog-post-link",
                   "class" : "",
                   "func"  : function()
                             {
                               Blog.currentPost = null;
                               Blog.showPostEditor();
                             }
                  },
                  {"event" : "click",
                   "id"    : "blog-post-send-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Blog.savePost();
                             }
                  },
                  {"event" : "click",
                   "id"    : "blog-post-cancel-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Blog.clearPostEditor();
                               Blog.hidePostEditor();
                             }
                  },
                  {"event" : "click",
                   "id"    : "blog-post-close-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Blog.hidePostEditor();
                             }
                  },
                  {"event" : "click",
                   "id"    : window,
                   "class" : null,
                   "func"  : function(event)
                             {
                               if (event.target == $('blog-post-editor')) {
                                 Blog.clearPostEditor();
                                 Blog.hidePostEditor();
                               }
                             }
                  },
                  {"event" : "click",
                   "id"    : "blog-post-comment-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Blog.postComment();
                             }
                  },
                  {"event" : "keyup",
                   "id"    : "compose_subject",
                   "class" : null,
                   "func"  : function()
                             {
                               if ((this.value == "") || ($('compose_body').value == "")) {
                                 $('compose_send').disabled = true;
                                 return;
                               }
                               $('compose_send').disabled = false;
                             }
                  },
                  {"event" : "keyup",
                   "id"    : "compose_body",
                   "class" : null,
                   "func"  : function()
                             {
                               if ((this.value == "") || ($('compose_subject').value == "")) {
                                 $('compose_send').disabled = true;
                                 return;
                               }
                               $('compose_send').disabled = false;
                             }
                  },
                  {"event" : "click",
                   "id"    : "compose_send",
                   "class" : null,
                   "func"  : function()
                             {
                               Mailbox.send();
                               Mailbox.clearEditor();
                             }
                  },
                  {"event" : "click",
                   "id"    : "compose_cancel",
                   "class" : null,
                   "func"  : function()
                             {
                               Mailbox.clearEditor();
                             }
                  },
                  {"event" : "click",
                   "id"    : "inbox_search",
                   "class" : null,
                   "func"  : function()
                             {
                               Mailbox.search('inbox');
                             }
                  },
                  {"event" : "keyup",
                   "id"    : "inbox_search_terms",
                   "class" : null,
                   "func"  : function(event)
                             {
                               var code = event.charCode || event.keyCode;
                               if (this.value == "") {
                                 $('inboxSearchResults').innerHTML = "";
                                 $('inboxSearchResults').style.display = "none";
                                 $('inbox').style.display = "block";
                                 $('inbox_search').disabled = true;
                                 return;
                               }
                               $('inbox_search').disabled = false;
                               if (code == 13) {
                                 Mailbox.search('inbox');
                               }
                             }
                  },
                  {"event" : "click",
                   "id"    : "outbox_search",
                   "class" : null,
                   "func"  : function()
                             {
                               Mailbox.search('outbox');
                             }
                  },
                  {"event" : "keyup",
                   "id"    : "outbox_search_terms",
                   "class" : null,
                   "func"  : function()
                             {
                               var code = event.charCode || event.keyCode;
                               if (this.value == "") {
                                 $('outboxSearchResults').innerHTML = "";
                                 $('outboxSearchResults').style.display = "none";
                                 $('outbox').style.display = "block";
                                 $('outbox_search').disabled = true;
                                 return;
                               }
                               $('outbox_search').disabled = false;
                               if (code == 13) {
                                 Mailbox.search('outbox');
                               }
                             }
                  },
                  {"event" : "click",
                   "id"    : "alerts_clear",
                   "class" : null,
                   "func"  : function()
                             {
                               Mailbox.clearAlerts();
                             }
                  },
                  {"event" : "click",
                   "id"    : null,
                   "class" : "mail-message-link",
                   "func"  : function()
                             {
                               var messageId = parseInt(this.getAttribute('data-messageId'));
                               Mailbox.showMessage(messageId);
                             }
                  },
                  {"event" : "click",
                   "id"    : "forum-post-send-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Forum.postMessage();
                             }
                  },
                  {"event" : "click",
                   "id"    : "forum-post-cancel-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Forum.clearPostEditor();
                               Forum.hidePostEditor();
                             }
                  },
                  {"event" : "click",
                   "id"    : "forum-post-close-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Forum.hidePostEditor();
                             }
                  },
                  {"event" : "click",
                   "id"    : window,
                   "class" : "",
                   "func"  : function(event)
                             {
                               if (event.target == $('forum-post-editor')) {
                                 Forum.clearPostEditor();
                                 Forum.hidePostEditor();
                               }
                             }
                  },
                  {"event" : "click",
                   "id"    : null,
                   "class" : "forum-thread-link",
                   "func"  : function()
                             {
                               var threadId = parseInt(this.getAttribute('data-threadId'));
                               Forum.showTopic(threadId);
                             }
                  },
                  {"event" : "click",
                   "id"    : "forum-post-topic-button",
                   "class" : null,
                   "func"  : function()
                             {
                               $('forumPost_replyTo').value = 0;
                               $('forum-post-editor-caption').innerHTML = "Post New Topic";
                               Forum.showPostEditor();
                             }
                  },
                  {"event" : "click",
                   "id"    : "forum-clear-search-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Forum.searchResults = null;
                               Forum.init();
                             }
                  },
                  {"event" : "click",
                   "id"    : "forum-post-reply-button",
                   "class" : null,
                   "func"  : function()
                             {
                               $('forumPost_replyTo').value = Forum.currentPostId;
                               $('forum-post-editor-caption').innerHTML = "Reply to Post";
                               if (Forum.getPostById(Forum.currentPostId).topic.indexOf('Re: ') !== 0) {
                                 $('forumPost_topic').value = "Re: " + Forum.getPostById(Forum.currentPostId).topic;
                               }
                               else {
                                 $('forumPost_topic').value = Forum.getPostById(Forum.currentPostId).topic;
                               }
                               Forum.showPostEditor();
                             }
                  },
                  {"event" : "click",
                   "id"    : "forum-branch-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Forum.showBranch();
                             }
                  },
                  {"event" : "click",
                   "id"    : "forum-limb-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Forum.showLimb();
                             }
                  },
                  {"event" : "click",
                   "id"    : "forum-stem-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Forum.showStem();
                             }
                  },
                  {"event" : "click",
                   "id"    : null,
                   "class" : "forum-post-link",
                   "func"  : function()
                             {
                               Forum.destination = parseInt(this.getAttribute('data-postId'));
                               Forum.show();
                             }
                  },
                  {"event" : "click",
                   "id"    : "settings_save",
                   "class" : null,
                   "func"  : function()
                             {
                               Preferences.save();
                             }
                  },
                  {"event" : "change",
                   "id"    : "settings_theme",
                   "class" : null,
                   "func"  : function()
                             {
                               var theme = this.options[this.selectedIndex].value;
                               $('theme').setAttribute('href', './client/theme/' + theme + '.css');
                               Preferences.theme = theme;
                             }
                  },
                  {"event" : "change",
                   "id"    : "settings_cursor",
                   "class" : null,
                   "func"  : function()
                             {
                               Client.showError('You must save your preferences and reload the page for this setting to take effect.');
                             }
                  },
                  {"event" : "change",
                   "id"    : "settings_sounds",
                   "class" : null,
                   "func"  : function()
                             {
                               Preferences.sounds = (parseInt(this.options[this.selectedIndex].value) == 1);
                             }
                  },
                  {"event" : "click",
                   "id"    : "generic-modal-close-button",
                   "class" : null,
                   "func"  : function()
                             {
                               Client.clearModal();
                             }
                  },
                  {"event" : "click",
                   "id"    : "file-upload-link",
                   "class" : null,
                   "func"  : function()
                             {
                               UserFiles.showUploader();
                             }
                  },
                  {"event" : "click",
                   "id"    : "download-button",
                   "class" : null,
                   "func"  : function()
                             {
                               var fileId = this.getAttribute('data-fileId');
                               window.open('/files.php?fileId=' + fileId);
                             }
                  },
                  {"event" : "click",
                   "id"    : "user-file-comment-button",
                   "class" : null,
                   "func"  : function()
                             {
                               UserFiles.postComment();
                             }
                  },
                  {"event" : "click",
                   "id"    : "file-upload-cancel-button",
                   "class" : null,
                   "func"  : function()
                             {
                               UserFiles.hideUploader();
                             }
                  },
                  {"event" : "click",
                   "id"    : "file-upload-send-button",
                   "class" : null,
                   "func"  : function()
                             {
                               UserFiles.uploadFile();
                             }
                  },
                  {"event" : "click",
                   "id"    : window,
                   "class" : null,
                   "func"  : function(event)
                             {
                               if (event.target == $('file-upload-editor')) {
                                 UserFiles.hideUploader();
                               }
                             }
                  },
                  {"event" : "keyup",
                   "id"    : "site-search-terms",
                   "class" : null,
                   "func"  : function(event)
                             {
                               var code = event.charCode || event.keyCode;
                               if (this.value == "") {
                                 $('site-search-button').disabled = true;
                                 return;
                               }
                               $('site-search-button').disabled = false;
                               if (code == 13) {
                                 Search.search();
                               }
                             }
                  },
                  {"event" : "click",
                   "id"    : "site-search-button",
                   "class" : null,
                   "func"  : function(event)
                             {
                               Search.search();
                             }
                  },
                  {"event" : "click",
                   "id"    : null,
                   "class" : "spoiler-decode-button",
                   "func"  : function()
                             {
                               var id  = this.getAttribute('data-spoilerId');
                               var div = $('spoiler-' + id);
                               var txt = div.innerText.rot13();
                               div.innerText = txt;
                             }
                  }
                ],

  "apply"     : function()
                {
                  var i, j, handler, element, elements;
                  for (i = 0; i < this.handlers.length; i++) {
                    handler = this.handlers[i];
                    if (handler.id == window) {
                      window.removeEventListener(handler.event, handler.func);
                      window.addEventListener(handler.event, handler.func);
                    }
                    else if (handler.id != null) {
                      if ((element = $(handler.id)) == null) {
                        continue;
                      }
                      element.removeEventListener(handler.event, handler.func);
                      element.addEventListener(handler.event, handler.func);
                    }
                    else {
                      elements = document.querySelectorAll('.' + handler.class);
                      for (j = 0; j < elements.length; j++) {
                        elements[j].removeEventListener(handler.event, handler.func);
                        elements[j].addEventListener(handler.event, handler.func);
                      }
                    }
                  }
                } // apply

}; // EventHandlers