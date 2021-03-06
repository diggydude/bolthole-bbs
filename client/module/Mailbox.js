var Mailbox = {

  "inbox"       : null,
  "outbox"      : null,
  "request"     : new XMLHttpRequest(),
  "handle"      : null,

  "init"        : function()
                  {
                    if ((this.inbox = Client.getStorage('inbox')) == null) {
                      this.inbox = [];
                    }
                    if ((this.outbox = Client.getStorage('outbox')) == null) {
                      this.outbox = [];
                    }
                    this.refreshView();
                    EventHandlers.apply();
                    $('compose_send').disabled  = true;
                    $('inbox_search').disabled  = true;
                    $('outbox_search').disabled = true;
                    this.handle = Client.taskList.add(
                      function() {
                        Mailbox.poll();
                      }, 'slow'
                    );
                  }, // init

  "update"      : function(response)
                  {
                    var links;
                    if (response.inbox.length > 0) {
                      for (var i = 0; i < response.inbox.length; i++) {
						response.inbox[i].read = false;
                        this.inbox.push(response.inbox[i]);
                      }
                      Client.putStorage('inbox', this.inbox);
                      if (Preferences.sounds) {
                        $('gotmail-audio').play();
                      }
                    }
                    if (response.outbox.length > 0) {
                      for (i = 0; i < response.outbox.length; i++) {
                        this.outbox.push(response.outbox[i]);
                      }
                      Client.putStorage('outbox', this.outbox);
                    }
                    this.refreshView();
                    if (response.alerts.length > 0) {
                      for (i = 0; i < response.alerts.length; i++) {
                        if (this.filterAlert(response.alerts[i].data) == true) {
                          $('alerts').innerHTML += "<div><img src=\"./client/icons/alert_icon.png\" alt=\"alert\" /> " + response.alerts[i].data + "</div>";
                          $('alerts').scrollTop = $('alerts').scrollHeight;
                          if (Preferences.sounds) {
                            $('chime-audio').play();
                          }
                        }
                      }
                    }
                    EventHandlers.apply();
                  }, // update

  "refreshView" : function()
                  {
                    this.inbox.sortByProperty('postedAt');
                    this.inbox.reverse();
                    $('inbox').innerHTML = Client.render('inbox_items', {"messages" : this.inbox});
                    this.outbox.sortByProperty('postedAt');
                    this.outbox.reverse();
                    $('outbox').innerHTML = Client.render('outbox_items', {"messages" : this.outbox});
                    EventHandlers.apply();
                  }, // refreshView

  "filterAlert" : function(html)
                  {
                    return AlertFilter.filter(html);
                  }, // filterAlert

  "search"      : function(where)
                  {
                    var i       = 0;
                    var terms   = "";
                    var results = [];
                    if (where == 'inbox') {
                      terms = $('inbox_search_terms').value;
                      for (i = 0; i < this.inbox.length; i++) {
                        if (this.inbox[i].sender.indexOf(terms) > -1) {
                          results.push(this.inbox[i]);
                        }
                        if (this.inbox[i].subject.indexOf(terms) > -1) {
                          results.push(this.inbox[i]);
                        }
                        if (this.inbox[i].body.indexOf(terms) > -1) {
                          results.push(this.inbox[i]);
                        }
                      }
                      $('inboxSearchResults').innerHTML = Client.render('inbox_items', {"messages" : results});
                      $('inbox').style.display = "none";
                      $('inboxSearchResults').style.display = "block";
                    }
                    if (where == 'outbox') {
                      terms = $('outbox_search_terms').value;
                      for (i = 0; i < this.outbox.length; i++) {
                        if (this.outbox[i].recipient.indexOf(terms) > -1) {
                          results.push(this.outbox[i]);
                        }
                        if (this.outbox[i].subject.indexOf(terms) > -1) {
                          results.push(this.outbox[i]);
                        }
                        if (this.outbox[i].body.indexOf(terms) > -1) {
                          results.push(this.outbox[i]);
                        }
                      }
                      $('outboxSearchResults').innerHTML = Client.render('outbox_items', {"messages" : results});
                      $('outbox').style.display = "none";
                      $('outboxSearchResults').style.display = "block";
                    }
                  }, // search

  "send"        : function()
                  {
                    var uri = "/mail.php";
                    var formData = new FormData();
                    with (formData) {
                      append('command', 'sendMail');
                      append('to',      $('compose_to').options[$('compose_to').selectedIndex].value);
                      append('from',    Session.userId);
                      append('subject', $('compose_subject').value);
                      append('body',    $('compose_body').value);
                    };
                    with (Client.request) {
                      open('POST', uri, true);
                      onload  = function()
                                {
                                  var response = JSON.parse(this.responseText);
                                  if (response.success) {
                                    Mailbox.update(response.results);
                                    if (response.message != "") {
                                      Client.showSuccess(response.message);
                                      $('compose_send').disabled = true;
                                    }
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
                  }, // send

  "showMessage" : function(messageId)
                  {
                    var message = null;
                    for (var i = 0; i < this.inbox.length; i++) {
                      if (parseInt(this.inbox[i].messageId) == messageId) {
                        message = this.inbox[i];
                        Client.showModal(message.subject, 'inbox_message', message, true);
						message.read = true;
						Client.putStorage('inbox', this.inbox);
                      }
                    }
                    if (message == null) {
                      for (var i = 0; i < this.outbox.length; i++) {
                        if (parseInt(this.outbox[i].messageId) == messageId) {
                          message = this.outbox[i];
                          Client.showModal(message.subject, 'outbox_message', message, true);
                        }
                      }
                    }
                    EventHandlers.apply();
                  }, // showMessage

  "delete"      : function(messageId)
                  {
                    var found = false;
                    for (var i = 0; i < this.inbox.length; i++) {
                      if (parseInt(this.inbox[i].messageId) == messageId) {
                        this.inbox.splice(i, 1);
						Client.putStorage('inbox', this.inbox);
                        found = true;
                        break;
                      }
                    }
                    for (var i = 0; i < this.outbox.length; i++) {
                      if (parseInt(this.outbox[i].messageId) == messageId) {
                        this.outbox.splice(i, 1);
						Client.putStorage('outbox', this.outbox);
                        found = true;
                        break;
                      }
                    }
                    Client.clearModal();
                    if (found) {
                      this.refreshView();
                      Client.showSuccess('Message deleted.');
                    }
                  }, // delete

  "poll"        : function()
                  {
                    var uri = "/mail.php";
                    var formData = new FormData();
                    with (formData) {
                      append('command', 'checkMail');
                      append('for',     Session.userId);
                    };
                    with (this.request) {
                      open('POST', uri, true);
                      onload  = function()
                                {
                                  var response = JSON.parse(this.responseText);
                                  if (response.success) {
                                    Mailbox.update(response.results);
                                    if (response.message != "") {
                                      Client.showSuccess(response.message);
                                    }
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
                  }, // poll

  "clearAlerts" : function()
                  {
                    $('alerts').innerHTML = "";
                  }, //clearAlerts

  "clearEditor" : function()
                  {
                    $('compose_to').value      = "";
                    $('compose_subject').value = "";
                    $('compose_body').value    = "";
                  }, // clearEditor

  "quit"        : function()
                  {
                    Client.taskList.remove(this.handle);
                    $('inbox').innerHTML  = "";
                    $('outbox').innerHTML = "";
                    this.clearAlerts();
                    this.clearEditor();
                    $('compose_send').disabled  = true;
                    $('inbox_search').disabled  = true;
                    $('outbox_search').disabled = true;
                  } // quit

}; // Mailbox
