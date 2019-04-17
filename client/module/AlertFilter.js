var AlertFilter = {

  "patterns"  : {
                  "notifyReply"      : [
                                         /^(.*) replied to your forum post\.$/
                                       ],
                  "notifyVisit"      : [
                                         /^(.*) viewed your profile\.$/
                                       ],
                  "notifyMention"    : [
                                         /^(.*) mentioned you in a forum post\.$/,
                                         /^(.*) mentioned you in a blog post\.$/,
                                         /^(.*) mentioned you in chat\.$/
                                       ],
                  "notifyComment"    : [
                                         /^(.*) commented on your profile\.$/,
                                         /^(.*) commented on .*\.$/,
                                         /^(.*) commented on your blog post\.$/
                                       ],
                  "notifyDownload"   : [
                                         /^Someone downloaded .* from your library\.$/
                                       ],
                  "notifyProfile"    : [
                                         /^(.*) updated their profile\.$/
                                       ],
                  "notifyBlogPost"   : [
                                         /^(.*) made a new blog post\.$/
                                       ],
                  "notifyUpload"     : [
                                         /^(.*) uploaded .*\.$/
                                       ],
				  "notifyUserSignup" : [
				                         /^New member (.*) has registered\.$/
				                       ]
                }, // pattern

  "filter"    : function(html)
                {
                  var text, i, matches, username;
                  var div = document.createElement("DIV");
                  div.innerHTML = html;
                  text = div.textContent || div.innerText || "";
                  for (var group in this.patterns) {
                    for (i = 0; i < this.patterns[group].length; i++) {
                      matches = text.match(this.patterns[group][i]);
                      if (matches == null) {
                        continue;
                      }
                      switch (group) {
                        case "notifyReply":
                          return Preferences.notifyReply;
                        case "notifyVisit":
                          return Preferences.notifyVisit;
                        case "notifyMention":
                          return Preferences.notifyMention;
                        case "notifyComment":
                          return Preferences.notifyComment;
                        case "notifyDownload":
                          return Preferences.notifyDownload;
                        case "notifyProfile":
                          if (Preferences.notifyAnyProfile) {
                            return true;
                          }
                          if (!Preferences.notifyBkmkProfile) {
                            return false;
                          }
                          username = matches[1];
                          return (Following.followed.indexOf(username) > -1);
                        case "notifyBlogPost":
                          if (Preferences.notifyAnyBlogPost) {
                            return true;
                          }
                          if (!Preferences.notifyBkmkBlogPost) {
                            return false;
                          }
                          username = matches[1];
                          return (Following.followed.indexOf(username) > -1);
                        case "notifyUpload":
                          if (Preferences.notifyAnyUpload) {
                            return true;
                          }
                          if (!Preferences.notifyBkmkUpload) {
                            return false;
                          }
                          username = matches[1];
                          return (Following.followed.indexOf(username) > -1);
                        case "notifyUserSignup":
                          return Preferences.notifyUserSignup;
                        default:
                          return false;
                      }
                    }
                  }
                } // filter

}; // AlertFilter
