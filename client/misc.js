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
  this.value = u.getFullYear().toString()
             + "-" + (u.getMonth() + 1).toString().padStart(2, '0')
             + "-" + u.getDate().toString().padStart(2, '0')
             + " " + u.getHours().toString().padStart(2, '0')
             + ":" + u.getMinutes().toString().padStart(2, '0')
             + ":" + u.getSeconds().toString().padStart(2, '0');
  return this.value;
}; // String.prototype.toLocalTime

String.prototype.toElapsedTime = function()
{
  var elapsed, num;
  var msInSec = 1000;
  var msInMin = 60000;
  var msInHr  = 3600000;
  var msInDay = 86400000;
  var msInMon = 2629800000;
  var msInYr  = 31557600000;
  var m = this.match(/^(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})$/);
  var then = new Date();
  var now  = new Date();
  then.setFullYear(parseInt(m[1]));
  then.setMonth(parseInt(m[2]) - 1, parseInt(m[3]));
  then.setHours(parseInt(m[4]), parseInt(m[5]), parseInt(m[6]));
  elapsed = Math.abs(now - then);
  if (elapsed >= msInYr) {
    num = Math.floor(elapsed / msInYr);
    this.value = (num == 1) ? "a year ago" : num.toString()  + " years ago";
    return this.value;
  }
  if (elapsed >= msInMon) {
    num = Math.floor(elapsed / msInMon);
    this.value = (num == 1) ? "a month ago" : num.toString() + " months ago";
    return this.value;
  }
  if (elapsed >= msInDay) {
    num = Math.floor(elapsed / msInDay);
    this.value = (num == 1) ? "yesterday" : num.toString() + " days ago";
    return this.value;
  }
  if (elapsed >= msInHr)  {
    num = Math.floor(elapsed / msInHr);
    this.value = (num == 1) ? "an hour ago" : num.toString()  + " hours ago";
    return this.value;
  }
  if (elapsed >= msInMin) {
    num = Math.floor(elapsed / msInMin);
    this.value = (num == 1) ? "a minute ago" : num.toString() + " minutes ago";
    return this.value;
  }
  num = Math.floor(elapsed / msInSec);
  if (num <= 5) {
    this.value = "just now";
    return this,value;
  }
  this.value = num.toString() + " seconds ago";
  return this.value;
} // String.prototype.toElapsedTime

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
