var Client = {

  "request"         : new XMLHttpRequest(),
  "taskList"        : new TaskList(1000),

  "init"            : function()
                      {
                        $('welcome-audio').volume = 0.25;
                        $('gotmail-audio').volume = 0.25;
                        $('goodbye-audio').volume = 0.25;
                        $('chime-audio').volume   = 0.25;
                        this.taskList.run();
                      }, // init

  "render"          : function(template, data)
                      {
                        var container = $('tpl-' + template);
                        templateHtml  = container.value || container.innerHTML;
                        Jst.html = "";
                        with (data) {
                          script = Jst.parse(templateHtml);
                          eval(script);
                        }
                        return Jst.html;
                      }, // render

  "showModal"       : function(caption, template, data, allowClose)
                      {
                        $('generic-modal-caption').innerHTML  = caption;
                        $('generic-modal-viewport').innerHTML = this.render(template, data);
                        $('generic-modal-close-button').style.display = (allowClose) ? "block" : "none";
                        $('generic-modal').style.display = "block";
                        EventHandlers.apply();
                      }, // showModal

  "clearModal"      : function()
                      {
                        $('generic-modal-caption').innerHTML  = "";
                        $('generic-modal-viewport').innerHTML = "";
                        $('generic-modal-close-button').style.display = "block";
                        $('generic-modal').style.display = "none";
                      }, // clearModal

  "showError"       : function(message)
                      {
                        $('error-message').innerHTML = message;
                        $('error-message').style.display = "block";
                        window.setTimeout(function(){$('error-message').style.display = "none";}, 5000);
                      }, // showError

  "showSuccess"     : function(message)
                      {
                        $('success-message').innerHTML = message;
                        $('success-message').style.display = "block";
                        window.setTimeout(function(){$('success-message').style.display = "none";}, 5000);
                      } // showSuccess

}; // Client
