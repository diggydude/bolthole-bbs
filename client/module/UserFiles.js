var UserFiles = {

  "showFileDetails" : function(fileId)
                      {
                        var uri = "/files.php";
                        var formData = new FormData();
                        with (formData) {
                          append('command', 'getDetails');
                          append('fileId',  fileId);
                        }
                        with (Client.request) {
                          open('POST', uri, true);
                          onload = function()
                                   {
                                     var response = JSON.parse(this.responseText);
                                     if (response.success) {
                                       $('main').innerHTML = Client.render('user_file_details', response.results);
                                       EventHandlers.apply();
                                       return;
                                     }
                                     Client.showError(response.message);
                                   };
                          send(formData);
                        }
                      }, // showFileDetails

  "showUploader"    : function()
                      {
                        $('file-upload-editor').innerHTML = Client.render('user_file_uploader', {});
                        EventHandlers.apply();
                        $('file-upload-editor').style.display = "block";
                      }, // showUploader

  "hideUploader"    : function()
                      {
                        $('file-upload-editor').style.display = "none";
                      }, // hideUploader

  "uploadFile"      : function()
                      {
                        var uri      = "/files.php";
                        var formData = new FormData();
                        with (formData) {
                          append('MAX_FILE_SIZE', Config.maxUploadSize);
                          append('command',     'upload');
                          append('upload',      $('file-upload-file').files[0]);
                          append('description', $('file-upload-description').value);
                          append('inLibrary',   Session.profile.libraryId);
                        }
                        with (Client.request) {
                          open('POST', uri, true);
                          onload  = function()
                                    {
                                      var response = JSON.parse(this.responseText);
                                      if (response.success) {
                                        Client.showSuccess(response.message);
                                        UserFiles.hideUploader();
                                        $('main').innerHTML = Client.render('user_file_details', response.results);
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
                      }, // uploadFile

  "postComment"     : function()
                      {
                        var uri      = "/files.php";
                        var formData = new FormData();
                        with (formData) {
                          append('command',  'postComment');
                          append('fileId',   $('user-file-comment-id').value);
                          append('postedBy', Session.userId);
                          append('body',     $('user-file-comment-entry').value);
                        }
                        with (Client.request) {
                          open('POST', uri, true);
                          onload  = function()
                                    {
                                      var response = JSON.parse(this.responseText);
                                      if (response.success) {
                                        Client.showSuccess(response.message);
                                        $('main').innerHTML = Client.render('user_file_details', response.results);
                                        $('main').scrollTop = $('main').scrollHeight;
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
                      }, // postComment

  "search"         : function(terms)
                     {
                        var uri      = "/files.php";
                        var formData = new FormData();
                        with (formData) {
                          append('command', 'search');
                          append('terms',   terms);
                        }
                        with (Client.request) {
                          open('POST', uri, true);
                          onload  = function()
                                    {console.log(this.responseText);
                                      var response = JSON.parse(this.responseText);
                                      if (response.success) {
                                        $('main').innerHTML = Client.render('user_file_search_results', response.results);
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
                     } // search

} // UserFiles