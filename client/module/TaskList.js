function TaskList(interval)
{
  var tasks    = {"fast" : {}, "slow" : {}};
  var taskList = this;
  this.timer   = null;

  this.run     = function()
                 {
                   this.timer = window.setInterval(function(){taskList.process();}, interval);
                 }; // run

  this.halt    = function()
                 {
                   window.clearInterval(this.timer);
                 }; // halt

  this.add     = function(task)
                 {
                   var handle = String.random(16);
				   if ((arguments.length > 1) && (arguments[1] == "fast")) {
                     tasks.fast[handle] = task;
				   }
				   else {
					 tasks.slow[handle] = task;
				   }
                   return handle;
                 }; // add

  this.remove  = function(handle)
                 {
					if (tasks.fast.hasOwnProperty(handle)) {
                      delete tasks.fast[handle];
					  return;
					}
					if (tasks.slow.hasOwnProperty(handle)) {
                      delete tasks.slow[handle];
					}
                 }; // remove

  this.process = function()
                 {
                   for (var p in tasks.fast) {
                     if (tasks.fast.hasOwnProperty(p)) {
                       (tasks.fast[p])();
                     }
				   }
				   if (((new Date).getSeconds() % 7) == 0) {
                     for (var p in tasks.slow) {
						if (tasks.slow.hasOwnProperty(p)) {
                          (tasks.slow[p])();
                        }
                     }
				   }
                 }; // process

}; // taskList