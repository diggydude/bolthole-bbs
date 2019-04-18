/*******************************************************************
* File    : JSFX_Layer.js  © JavaScript-FX.com
* Created : 2001/04/11
* Author  : Roy Whittle  (Roy@Whittle.com) www.Roy.Whittle.com
* Purpose : To create a cross browser dynamic layer API.
* History
* Date         Version        Description
* 2001-03-17    3.0     Completely re-witten for use by javascript-fx
* 2001-09-08    3.1     Added the ability for child layers
* 2001-09-23    3.2     Save a reference so we can use a self referencing timer
* 2001-09-28    3.3     Add a width for Netscape 4.x
* 2001-09-28    3.4     Remove width for Netscape 4.x create layer (Not needed)
* 2002-01-21    3.5     Declare only one instance of variables in createLayer
* 2002-06-12    3.6     Correct a major bug in JSFX.findLayer (Basically the same bug as
*                   in JSFX.findImg - must brush up on recursion)
* 2003-05-19    3.7     Change the id creation for the Layer/Timer functions
***********************************************************************/
var ns4 = (navigator.appName.indexOf("Netscape") != -1 && !document.getElementById);

if(!window.JSFX)
    JSFX=new Object();

JSFX.layerNo=0;
/**********************************************************************************/
JSFX.createLayer = function(htmlStr, parent)
{
    //Declare all variables first
    var elem = null;
    var xName;
    var txt;

    if(document.layers)
    {
        xName="xLayer" + JSFX.layerNo++;
        if(parent == null)
            elem=new Layer(2000);
        else
            elem=new Layer(2000, parent.elem);

        elem.document.open();
        elem.document.write(htmlStr);
        elem.document.close();
        elem.moveTo(0,0);
        elem.innerHTML = htmlStr;
    }
    else
    if(document.all)
    {
        if(parent == null)
            parent=document.body;
        else
            parent=parent.elem;

        xName = "xLayer" + JSFX.layerNo++;
        txt = '<DIV ID="' + xName + '"'
            + ' STYLE="position:absolute;left:0;top:0;visibility:hidden">'
            + htmlStr
            + '</DIV>';

            parent.insertAdjacentHTML("BeforeEnd",txt);

        elem = document.all[xName];
    }
    else
    if (document.getElementById)
    {
        if(parent == null)
            parent=document.body;
        else
            parent=parent.elem;

        xName="xLayer" + JSFX.layerNo++;
        txt = ""
            + "position:absolute;left:0px;top:0px;visibility:hidden";

        var newRange = document.createRange();

        elem = document.createElement("DIV");
        elem.setAttribute("style",txt);
        elem.setAttribute("id", xName);

        parent.appendChild(elem);

        newRange.setStartBefore(elem);
        strFrag = newRange.createContextualFragment(htmlStr);
        elem.appendChild(strFrag);
    }

    return elem;
}
/**********************************************************************************/
JSFX.Layer = function(newLayer, parent)
{
    if(!newLayer)
        return;

    if(typeof newLayer == "string")
        this.elem = JSFX.createLayer(newLayer, parent);
    else
        this.elem=newLayer;

    if(document.layers)
    {
        this.images     = this.elem.document.images;
        this.parent     = parent;
        this.style      = this.elem;
        if(parent != null)
            this.style.visibility = "inherit";
    }
    else
    {
        this.images  = document.images;
        this.parent  = parent;
        this.style   = this.elem.style;
    }
    window[ this.id = "jsfx_" + this.elem.id ]=this; //save a reference to this
}
/**********************************************************************************/
JSFX.getLayer = function(theDiv, d)
{
    var theLayer = d.layers[theDiv];
    for(var i=0 ; i<d.layers.length && theLayer==null ; i++)
        theLayer = JSFX.getLayer(theDiv, d.layers[i].document);

    return theLayer;
}
JSFX.findLayer = function(theDiv, d)
{
    if(document.layers)
        return(JSFX.getLayer(theDiv, document));
    else
    if(document.all)
        return(document.all[theDiv]);
    else
    if(document.getElementById)
        return(document.getElementById(theDiv));
    else
        return("Undefined.....");
}

/**********************************************************************************/
/*** moveTo (x,y) ***/
JSFX.Layer.prototype.moveTo = function(x,y)
{
    this.style.left = x+"px";
    this.style.top = y+"px";
}
if(ns4)
    JSFX.Layer.prototype.moveTo = function(x,y) { this.elem.moveTo(x,y); }
/**********************************************************************************/
/*** show()/hide() Visibility ***/
JSFX.Layer.prototype.show       = function()    { this.style.visibility = "visible"; }
JSFX.Layer.prototype.hide       = function()    { this.style.visibility = "hidden"; }
JSFX.Layer.prototype.isVisible  = function()    { return this.style.visibility == "visible"; }
if(ns4)
{
    JSFX.Layer.prototype.show       = function()    { this.style.visibility = "show"; }
    JSFX.Layer.prototype.hide       = function()    { this.style.visibility = "hide"; }
    JSFX.Layer.prototype.isVisible  = function()    { return this.style.visibility == "show"; }
}
/**********************************************************************************/
/*** zIndex ***/
JSFX.Layer.prototype.setzIndex  = function(z)   { this.style.zIndex = z; }
JSFX.Layer.prototype.getzIndex  = function()    { return this.style.zIndex; }
/**********************************************************************************/
/*** ForeGround (text) Color ***/
JSFX.Layer.prototype.setColor   = function(c){this.style.color=c;}
if(ns4)
    JSFX.Layer.prototype.setColor   = function(c)
    {
        this.elem.document.write("<FONT COLOR='"+c+"'>"+this.elem.innerHTML+"</FONT>");
        this.elem.document.close();
    }
/**********************************************************************************/
/*** BackGround Color ***/
JSFX.Layer.prototype.setBgColor = function(color) { this.style.backgroundColor = color==null?'transparent':color; }
if(ns4)
    JSFX.Layer.prototype.setBgColor     = function(color) { this.elem.bgColor = color; }
/**********************************************************************************/
/*** BackGround Image ***/
JSFX.Layer.prototype.setBgImage = function(image) { this.style.backgroundImage = "url("+image+")"; }
if(ns4)
    JSFX.Layer.prototype.setBgImage     = function(image) { this.style.background.src = image; }
/**********************************************************************************/
/*** set Content***/
JSFX.Layer.prototype.setContent   = function(xHtml) { this.elem.innerHTML=xHtml; }
if(ns4)
    JSFX.Layer.prototype.setContent   = function(xHtml)
    {
        this.elem.document.write(xHtml);
        this.elem.document.close();
        this.elem.innerHTML = xHtml;
    }

/**********************************************************************************/
/*** Clipping ***/
JSFX.Layer.prototype.clip = function(x1,y1, x2,y2){ this.style.clip="rect("+y1+" "+x2+" "+y2+" "+x1+")"; }
if(ns4)
    JSFX.Layer.prototype.clip = function(x1,y1, x2,y2)
    {
        this.style.clip.top =y1;
        this.style.clip.left    =x1;
        this.style.clip.bottom  =y2;
        this.style.clip.right   =x2;
    }
/**********************************************************************************/
/*** Resize ***/
JSFX.Layer.prototype.resizeTo = function(w,h)
{
    this.style.width    =w + "px";
    this.style.height   =h + "px";
}
if(ns4)
    JSFX.Layer.prototype.resizeTo = function(w,h)
    {
        this.style.clip.width   =w;
        this.style.clip.height  =h;
    }
/**********************************************************************************/
/*** getX/Y ***/
JSFX.Layer.prototype.getX   = function()    { return parseInt(this.style.left); }
JSFX.Layer.prototype.getY   = function()    { return parseInt(this.style.top); }
if(ns4)
{
    JSFX.Layer.prototype.getX   = function()    { return this.style.left; }
    JSFX.Layer.prototype.getY   = function()    { return this.style.top; }
}
/**********************************************************************************/
/*** getWidth/Height ***/
JSFX.Layer.prototype.getWidth       = function()    { return this.elem.offsetWidth; }
JSFX.Layer.prototype.getHeight  = function()    { return this.elem.offsetHeight; }
if(!document.getElementById)
    JSFX.Layer.prototype.getWidth       = function()
    {
        //Extra processing here for clip
        return this.elem.scrollWidth;
    }

if(ns4)
{
    JSFX.Layer.prototype.getWidth       = function()    { return this.style.clip.right; }
    JSFX.Layer.prototype.getHeight  = function()    { return this.style.clip.bottom; }
}
/**********************************************************************************/
/*** Opacity ***/
if(ns4)
{
    JSFX.Layer.prototype.setOpacity = function(pc) {return 0;}
}
else if(document.all)
{
    JSFX.Layer.prototype.setOpacity = function(pc)
    {
        if(this.style.filter=="")
            this.style.filter="alpha(opacity=100);";
        this.elem.filters.alpha.opacity=pc;
    }
}
else
{
/*** Assume NS6 ***/
    JSFX.Layer.prototype.setOpacity = function(pc){ this.style.MozOpacity=pc/100 }
}
/**************************************************************************/
/*** Event Handling - Start ***/
/*** NS4 ***/
if(ns4)
{
    JSFX.eventmasks = {
          onabort:Event.ABORT, onblur:Event.BLUR, onchange:Event.CHANGE,
          onclick:Event.CLICK, ondblclick:Event.DBLCLICK,
          ondragdrop:Event.DRAGDROP, onerror:Event.ERROR,
          onfocus:Event.FOCUS, onkeydown:Event.KEYDOWN,
          onkeypress:Event.KEYPRESS, onkeyup:Event.KEYUP, onload:Event.LOAD,
          onmousedown:Event.MOUSEDOWN, onmousemove:Event.MOUSEMOVE,
          onmouseout:Event.MOUSEOUT, onmouseover:Event.MOUSEOVER,
          onmouseup:Event.MOUSEUP, onmove:Event.MOVE, onreset:Event.RESET,
          onresize:Event.RESIZE, onselect:Event.SELECT, onsubmit:Event.SUBMIT,
          onunload:Event.UNLOAD
    };
    JSFX.Layer.prototype.addEventHandler = function(eventname, handler)
    {
          this.elem.captureEvents(JSFX.eventmasks[eventname]);
          var xl = this;
        this.elem[eventname] = function(event) {
        event.clientX   = event.pageX;
        event.clientY   = event.pageY;
        event.button    = event.which;
        event.keyCode   = event.which;
        event.altKey    =((event.modifiers & Event.ALT_MASK) != 0);
        event.ctrlKey   =((event.modifiers & Event.CONTROL_MASK) != 0);
        event.shiftKey  =((event.modifiers & Event.SHIFT_MASK) != 0);
            return handler(xl, event);
        }
    }
    JSFX.Layer.prototype.removeEventHandler = function(eventName)
    {
        this.elem.releaseEvents(JSFX.eventmasks[eventName]);
        this.elem[eventName] = null;
    }
}
/**************************************************************************/
/** IE 4/5+***/
else
if(document.all)
{
    JSFX.Layer.prototype.addEventHandler = function(eventName, handler)
    {
        var xl = this;
        this.elem[eventName] = function()
        {
                var e = window.event;
                e.cancelBubble = true;
            if(document.getElementById)
            {
                e.layerX = e.offsetX;
                e.layerY = e.offsetY;
            }
            else
            {
                /*** Work around for IE 4 : clone window.event ***/
                ev = new Object();
                for(i in e)
                    ev[i] = e[i];
                ev.layerX   = e.offsetX;
                ev.layerY   = e.offsetY;
                e = ev;
            }

                return handler(xl, e);
        }
    }
    JSFX.Layer.prototype.removeEventHandler = function(eventName)
    {
        this.elem[eventName] = null;
    }
}
/**************************************************************************/
/*** Assume NS6 ***/
else
{
    JSFX.Layer.prototype.addEventHandler = function(eventName, handler)
    {
        var xl = this;
        this.elem[eventName] = function(e)
        {
                e.cancelBubble = true;
                return handler(xl, e);
        }
    }
    JSFX.Layer.prototype.removeEventHandler = function(eventName)
    {
        this.elem[eventName] = null;
    }
}
/*** Event Handling - End ***/
/**************************************************************************/
JSFX.Layer.prototype.setTimeout = function(f, t)
{
    setTimeout("window."+this.id+"."+f, t);
}

/*************************************************************************/
/* Sort out bandwidth stealers */
/*************************************************************************/
var JSFX_WEB = new Array("javascript-fx", "javascriptfx", "jsfx", "js-fx");
var str = new String(window.location);
var isValid = false;
for(var i=0 ; i<JSFX_WEB.length ; i++)
    if(str.indexOf(JSFX_WEB[i]) != -1)
    {
        isValid = true;
        break;
    }
//if(!isValid) setTimeout("JSFX.logo()", 4000);
/*
var logoText = "<CENTER>The script on this page is hosted by<br><a href='http://www.javascript-fx.com' target='_top'><font color='00FF00'>www.JavaScript-FX.com</font></a><br>NOTE: This message is only here because the webmaster<br>of this site is hotlinking to javascript-fx.com<br>instead of uploading scripts to thier own host.</CENTER>";
var logoStr = "<TABLE BORDER=0 CELLSPACING=0 CELLPADDING='2' BGCOLOR='#FF0000'><TR><TD><TABLE BORDER=0 CELLSPACING=0 CELLPADDING=0 BGCOLOR='777777'><TR><TD><FONT FACE='Arial'>" + logoText + "</FONT></TD></TR></TABLE></TD></TR></TABLE>";
var logo;
var logoX=-400;
JSFX.logo = function()
{
    logo = new JSFX.Layer(logoStr);
    logo.moveTo(logoX, 0);
    logo.show();
    JSFX.logoAnim();
}
JSFX.logoAnim = function()
{
    logoX += 4;
    logo.moveTo(logoX, 0);
    if(logoX != 0)
        setTimeout("JSFX.logoAnim()", 40);
}
*/
/*************************************************************************
* Author  : Roy Whittle www.Roy.Whittle.com
*
* Purpose : To create a cross browser "Browser" object.
*       JSFX.Browser library will allow scripts to query parameters
*       about the current browser window.
*
* History
* Date         Version        Description
* 2001-03-17    2.0     Converted for javascript-fx
***********************************************************************/
if(!window.JSFX)
    JSFX=new Object();

if(!JSFX.Browser)
    JSFX.Browser = new Object();

if(navigator.appName.indexOf("Netscape") != -1)
{
    JSFX.Browser.getCanvasWidth = function() {return innerWidth;}
    JSFX.Browser.getCanvasHeight    = function() {return innerHeight;}
    JSFX.Browser.getWindowWidth     = function() {return outerWidth;}
    JSFX.Browser.getWindowHeight    = function() {return outerHeight;}
    JSFX.Browser.getScreenWidth     = function() {return screen.width;}
    JSFX.Browser.getScreenHeight    = function() {return screen.height;}
    JSFX.Browser.getMinX        = function() {return(pageXOffset);}
    JSFX.Browser.getMinY        = function() {return(pageYOffset);}
    JSFX.Browser.getMaxX        = function() {return(pageXOffset+innerWidth);}
    JSFX.Browser.getMaxY        = function() {return(pageYOffset+innerHeight);}

}
else    if(document.all)    {
    JSFX.Browser.getCanvasWidth = function() {return document.body.clientWidth;}
    JSFX.Browser.getCanvasHeight    = function() {return document.body.clientHeight;}
    JSFX.Browser.getWindowWidth     = function() {return document.body.clientWidth;}
    JSFX.Browser.getWindowHeight    = function() {return document.body.clientHeight;}
    JSFX.Browser.getScreenWidth = function() {return screen.width;}
    JSFX.Browser.getScreenHeight    = function() {return screen.height;}
    JSFX.Browser.getMinX        = function() {return(document.body.scrollLeft);}
    JSFX.Browser.getMinY        = function() {return(document.body.scrollTop);}
    JSFX.Browser.getMaxX        = function() {
        return(document.body.scrollLeft
            +document.body.clientWidth);
    }
    JSFX.Browser.getMaxY        = function() {
            return(document.body.scrollTop
                +document.body.clientHeight);
    }
}
/*** End  ***/
/*******************************************************************
*
* File    : JSFX_Mouse.js © JavaScript-FX.com
*
* Created : 2000/07/15
*
* Author  : Roy Whittle  (Roy@Whittle.com) www.Roy.Whittle.com
*
* Purpose : To create a cross browser "Mouse" object.
*       This library will allow scripts to query the current x,y
*       coordinates of the mouse.
*
* History
* Date         Version        Description
* 2000-06-08    2.0     Converted for javascript-fx
* 2001-08-26    2.1     Corrected a bug where IE6 was not detected.
***********************************************************************/
if(!window.JSFX)
    JSFX=new Object();
if(!JSFX.Browser)
    JSFX.Browser = new Object();

JSFX.Browser.mouseX = 0;
JSFX.Browser.mouseY = 0;

if(navigator.appName.indexOf("Netscape") != -1)
{
    JSFX.Browser.captureMouseXY = function (evnt)
    {
        JSFX.Browser.mouseX=evnt.pageX;
        JSFX.Browser.mouseY=evnt.pageY;
    }

    window.captureEvents(Event.MOUSEMOVE);
    window.onmousemove = JSFX.Browser.captureMouseXY;
}
else if(document.all)
{
    if(document.getElementById)
        JSFX.Browser.captureMouseXY = function ()
        {
            JSFX.Browser.mouseX = event.x + document.body.scrollLeft;
            JSFX.Browser.mouseY = event.y + document.body.scrollTop;
        }
    else
        JSFX.Browser.captureMouseXY = function ()
        {
            JSFX.Browser.mouseX = event.x;
            JSFX.Browser.mouseY = event.y;
        }
    document.onmousemove = JSFX.Browser.captureMouseXY;
}
/*** End  ***/
/*******************************************************************
* File    : JSFX_Circle.js  © JavaScript-FX.com
* Created : 2001/08/19
* Author  : Roy Whittle  (Roy@Whittle.com) www.Roy.Whittle.com
* Purpose : A mouse trailer based on a script by Doc Ozone - www.ozones.com
*           as seen at http://www.wsabstract.com/script/script2/cursoreffect.shtml
* History
* Date         Version        Description
* 2001-08-19    1.0     First converted version
* 2001-08-28    1.1     Added the text version
* 2001-12-06    1.2     Removed the need for JSFX_PlayField.js
***********************************************************************/
/*** Create a static method for creating Circle Objects ***/
JSFX.Circle = function(theImage, max, radius, speed)
{
    return new JSFX.CircleObj(theImage, max, radius, speed);
}

/*
 * Class CircleObj extends Object
 */
JSFX.CircleObj = function(theImage, max, radius, speed)
{
    var i;
    var htmlStr = "<IMG SRC='"+theImage+"'>";
    if(max == null) max=10;
    if(radius == null) radius = 200;
    if(speed == null) speed = 0.2;

    this.id = "JSFX_CircleObj_"+JSFX.CircleObj.count++;
    this.sprites = new Array();

    for(i=0 ; i<max ; i++)
        this.sprites[i] = new JSFX.CircleSprite(htmlStr, i, max, radius, speed);

    window[this.id]=this;
    this.animate();
}
JSFX.CircleObj.count = 0;
JSFX.CircleObj.prototype.animate = function()
{
    setTimeout("window."+this.id+".animate()", 40);

    for(i=0 ; i<this.sprites.length ; i++)
        this.sprites[i].animate();

}
/*** END Class CircleObj***/

/*** Create a static method for creating Circle Text Objects ***/
JSFX.CircleText = function(theStr, font, color, size, radius, speed)
{
    return new JSFX.CircleTextObj(theStr, font, color, size, radius, speed);
}

/*
 * Class CircleTextObj extends Object
 */
JSFX.CircleTextObj = function(theStr, font, color, size, radius, speed)
{
    var i;
    var max=theStr.length;
    if(radius == null) radius = 200;
    if(speed == null) speed = 0.2;

    this.id = "JSFX_CircleObj_"+JSFX.CircleObj.count++;
    this.sprites = new Array();

    for(i=0 ; i<max ; i++)
        this.sprites[i] = new JSFX.CircleSprite("<FONT FACE='"+font+"' SIZE='"+size+"' COLOR='"+color+"'>"+theStr.charAt(i)+"</FONT>", i, max, radius, speed);

    window[this.id]=this;
    this.animate();
}
JSFX.CircleTextObj.prototype.animate = JSFX.CircleObj.prototype.animate;

/*
 * Class CircleSprite extends Layer
 */
JSFX.CircleSprite = function(htmlStr, n, max, radius, speed)
{
    this.superC = JSFX.Layer;
    this.superC(htmlStr);

    this.x  = Math.random() * (JSFX.Browser.getMaxX()-40);
    this.y  = Math.random() * (JSFX.Browser.getMaxY()-40);
    this.r  = radius;
    this.s  = speed;
    this.a  = -(2 * n * Math.PI/max);
    this.targetX = 200 + this.r * Math.sin(this.a);
    this.targetY = 200 + this.r * Math.cos(this.a);
    this.show();
}
JSFX.CircleSprite.prototype = new JSFX.Layer;

JSFX.CircleSprite.prototype.animate = function()
{
    var mx = JSFX.Browser.mouseX;
    var my = JSFX.Browser.mouseY;
    var dir = this.s;

    if(my < JSFX.Browser.getMaxY()/2)
    {
        if(mx < JSFX.Browser.getMaxX()/2)
            dir = -dir;
    }
    else
    {
        if(mx > JSFX.Browser.getMaxX()/2)
            dir = -dir;
    }
this.setzIndex(1000);
    this.a += dir
    this.targetX = mx + this.r * Math.sin(this.a) - 10;
    this.targetY = my + this.r * Math.cos(this.a) - 10;
    this.x += (this.targetX - this.x)/20;
    this.y += (this.targetY - this.y)/20;

    this.moveTo(this.x, this.y);
}

function JSFX_StartEffects()
{
    JSFX.CircleText("......",                  "Arial", "#FF0000", 4,  50, -.1);
    JSFX.CircleText("Powered by Bolthole BBS", "Arial", "#00FF00", 4, 100, .1);
    JSFX.CircleText(Config.siteName + "    ",  "Arial", "#0000FF", 4, 150, -.1);
}

JSFX_StartEffects();
