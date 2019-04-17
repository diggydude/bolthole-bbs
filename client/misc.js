function $(id)
{
  return document.getElementById(id);
} // $

function sleep(milliseconds)
{
  var start = new Date().getTime();
  for (var i = 0; i < Number.MAX_SAFE_INTEGER; i++) {
    if ((new Date().getTime() - start) > milliseconds) {
      break;
    }
  }
} // sleep

String.random = function(length)
{
  var chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
  var str   = "";
  while (str.length < length) {
    str += chars.charAt(Math.floor(Math.random() * chars.length));
  }
  return str;
}; // String.random

String.prototype.toLocalTime = function()
{
  var m = this.match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/);
  var u = new Date();
  u.setUTCFullYear(parseInt(m[1]));
  u.setUTCMonth(parseInt(m[2]) - 1, parseInt(m[3]));
  u.setUTCHours(parseInt(m[4]), parseInt(m[5]), parseInt(m[6]));
  var l = u.getFullYear().toString() + "-" + (u.getMonth() + 1).toString().padStart(2, '0')
        + "-" + u.getDate().toString().padStart(2, '0') + " " + u.getHours().toString().padStart(2, '0')
		+ ":" + u.getMinutes().toString().padStart(2, '0');
  return l;
}; // String.prototype.toLocalTime

Array.prototype.sortByProperty = function(property)
{
  var propCmp = function(a, b)
  {
    if (!(property in a)) return 1;
    if (!(property in b)) return -1;
    return (a[property] == b[property]) ? 0 : ((a[property] < b[property]) ? -1 : 1);
  };
  this.sort(propCmp);
} // Array.prototype.sortByProperty