var Chat = {

  "request"        : new XMLHttpRequest(),
  "lastMsgRcvd"    : 0,
  "handle"         : null,

  "init"           : function()
                     {
                       var uri  = "/chat.php";
                       var formData = new FormData();
                       with (formData) {
                         append('command',  'join');
                         append('username', Session.username);
                       }
                       with (this.request) {
                         open('POST', uri, false);
                         onload = function()
                                  {
                                    var response = JSON.parse(this.responseText);
                                    if (response.success) {
                                      Chat.lastMsgRcvd = parseInt(response.results.lastMessageId);
                                      return;
                                    }
                                    Client.showError(response.message);
                                  };
                         send(formData);
                       }
                       this.handle = Client.taskList.add(
                         function() {
                           Chat.poll();
                         }, 'fast'
                       );
                     }, // init

  "update"         : function(data)
                     {
                       var links;
                       $('whosOnline').innerHTML = Client.render('whos_online', {"online" : data.online});
                       links = document.querySelectorAll('.whos-online-link');
                       for (i = 0; i < links.length; i++) {
                         links[i].addEventListener('click',
                           function()
                           {
                             var userId  = parseInt(this.getAttribute('data-userId'));
                             var profile = new Profile();
                             profile.fetch(userId);
                           }
                         );
                       }
                       for (i = 0; i < data.chats.length; i++) {
                         if ((data.chats[i].body.indexOf("/action") == 0) || (data.chats[i].body.indexOf("/me") == 0)) {
                           data.chats[i].body = data.chats[i].body.substring(data.chats[i].body.indexOf(' '));
                           $('chatWindow').innerHTML += Client.render('chat_action', data.chats[i]);
                         }
                         else {
                           $('chatWindow').innerHTML += Client.render('chat_message', data.chats[i]);
                         }
                         this.lastMsgRcvd = parseInt(data.chats[i].id);
                         $('chatWindow').scrollTop = $('chatWindow').scrollHeight;
                       }
                       EventHandlers.apply();
                     }, // update

  "send"           : function()
                     {
                       var formData;
                       var uri  = "/chat.php";
                       var body = $('chatMessage').value.trim();
                       if (body.length == 0) {
                         return;
                       }
                       formData = new FormData();
                       with (formData) {
                         append('command',  'sendMessage');
                         append('postedBy', Session.userId);
                         append('body',     body);
                       }
                       with (Client.request) {
                         open('POST', uri, true);
                         onload = function()
                                  {
                                    var response = JSON.parse(this.responseText);
                                    if (!response.success) {
                                      client.showError(response.message);
                                    }
                                  };
                         send(formData);
                       }
                       $('chatMessage').value = "";
                       $('chatSend').disabled = true;
                     }, // send

  "poll"           : function()
                     {
                       var uri  = "/chat.php";
                       var formData = new FormData();
                       with (formData) {
                         append('command',     'getMessages');
                         append('lastMsgRcvd', this.lastMsgRcvd);
                       }
                       with (this.request) {
                         open('POST', uri, true);
                         onload = function()
                                  {
                                    var response = JSON.parse(this.responseText);
                                    if (response.success) {
                                      Chat.update(response.results);
                                      return;
                                    }
                                    Client.showError(response.message);
                                  };
                         send(formData);
                       }
                     }, // poll

  "quit"           : function()
                     {
                       var uri  = "/chat.php";
                       var formData = new FormData();
                       with (formData) {
                         append('command',  'quit');
                         append('username', Config.username);
                       }
                       with (this.request) {
                         open('POST', uri, false);
                         onload = function()
                                  {
                                    var response = JSON.parse(this.responseText);
                                    if (!response.success) {
                                      Client.showError(response.message);
                                    }
                                  };
                         send(formData);
                       }
                       Client.taskList.remove(this.handle);
                       $('whosOnline').innerHTML = "";
                       $('chatWindow').innerHTML = "";
                       $('chatSend').setAttribute('disabled', true);
                     } // quit

}; // Chat
