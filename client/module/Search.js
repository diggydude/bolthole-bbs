var Search = {

  "search" : function()
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
               }
             } // search

}; // Search