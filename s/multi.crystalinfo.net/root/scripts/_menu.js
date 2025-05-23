/**
 * fw_menu 24OCT2000 Version 4.0
 * John Ahlquist, October 2000
 * Copyright (c) 2000 Macromedia, Inc.
 *
 * based on menu.js
 * by gary smith, July 1997
 * Copyright (c) 1997-1999 Netscape Communications Corp.
 *
 * Netscape grants you a royalty free license to use or modify this
 * software provided that this copyright notice appears on all copies.
 * This software is provided "AS IS," without a warranty of any kind.
 */
function Menu(label, mw, mh, fnt, fs, fclr, fhclr, bg, bgh) {
	this.version = "990702 [Menu; menu.js]";
	this.type = "Menu";
	this.menuWidth = mw;
	this.menuItemHeight = mh;
	this.fontSize = fs||12;
	this.fontWeight = "plain";
	this.fontFamily = fnt||"arial,helvetica,verdana,sans-serif";
	this.fontColor = fclr||"#000000";
	this.fontColorHilite = fhclr||"#ffffff";
	this.bgColor = "#555555";
	this.menuBorder = 1;
	this.menuItemBorder = 1;
	this.menuItemBgColor = bg||"#cccccc";
	this.menuLiteBgColor = "#ffffff";
	this.menuBorderBgColor = "#777777";
	this.menuHiliteBgColor = bgh||"#000084";
	this.menuContainerBgColor = "#cccccc";
	this.childMenuIcon = "../images/detay.gif";
	this.items = new Array();
	this.actions = new Array();
	this.childMenus = new Array();

	this.hideOnMouseOut = true;

	this.addMenuItem = addMenuItem;
	this.addMenuSeparator = addMenuSeparator;
	this.writeMenus = writeMenus;
	this.FW_showMenu = FW_showMenu;
	this.onMenuItemOver = onMenuItemOver;
	this.onMenuItemAction = onMenuItemAction;
	this.hideMenu = hideMenu;
	this.hideChildMenu = hideChildMenu;

	if (!window.menus) window.menus = new Array();
	this.label = label || "menuLabel" + window.menus.length;
	window.menus[this.label] = this;
	window.menus[window.menus.length] = this;
	if (!window.activeMenus) window.activeMenus = new Array();
}

function addMenuItem(label, action) {
	this.items[this.items.length] = label;
	this.actions[this.actions.length] = action;
}

function addMenuSeparator() {
	this.items[this.items.length] = "separator";
	this.actions[this.actions.length] = "";
	this.menuItemBorder = 0;
}

// For NS6. 
function FIND(item) {
	if (document.all) return(document.all[item]);
	if (document.getElementById) return(document.getElementById(item));
	return(false);
}

function writeMenus(container) {
	if (window.triedToWriteMenus) return;

	if (!container && document.layers) {
		window.delayWriteMenus = this.writeMenus;
		var timer = setTimeout('delayWriteMenus()', 250);
		container = new Layer(100);
		clearTimeout(timer);
	} else if (document.all || document.hasChildNodes) {
		document.writeln('<SPAN ID="menuContainer"></SPAN>');
		container = FIND("menuContainer");
	}

	window.fwHideMenuTimer = null;
	if (!container) return;	
	window.triedToWriteMenus = true; 
	container.isContainer = true;
	container.menus = new Array();
	for (var i=0; i<window.menus.length; i++) 
		container.menus[i] = window.menus[i];
	window.menus.length = 0;
	var countMenus = 0;
	var countItems = 0;
	var top = 0;
	var content = '';
	var lrs = false;
	var theStat = "";
	var tsc = 0;
	if (document.layers) lrs = true;
	for (var i=0; i<container.menus.length; i++, countMenus++) {
		var menu = container.menus[i];
		if (menu.bgImageUp) {
			menu.menuBorder = 0;
			menu.menuItemBorder = 0;
		}
		if (lrs) {
			var menuLayer = new Layer(100, container);
			var lite = new Layer(100, menuLayer);
			lite.top = menu.menuBorder;
			lite.left = menu.menuBorder;
			var body = new Layer(100, lite);
			body.top = menu.menuBorder;
			body.left = menu.menuBorder;
		} else {
			content += ''+
			'<DIV ID="menuLayer'+ countMenus +'" STYLE="position:absolute;z-index:1;left:10;top:'+ (i * 100) +';visibility:hidden;">\n'+
			'  <DIV ID="menuLite'+ countMenus +'" STYLE="position:absolute;z-index:1;left:'+ menu.menuBorder +';top:'+ menu.menuBorder +';visibility:hide;" onMouseOut="mouseoutMenu();">\n'+
			'	 <DIV ID="menuFg'+ countMenus +'" STYLE="position:absolute;left:'+ menu.menuBorder +';top:'+ menu.menuBorder +';visibility:hide;">\n'+
			'';
		}
		var x=i;

		for (var i=0; i<menu.items.length; i++) {
			var item = menu.items[i];
			var childMenu = false;
			var defaultHeight = menu.fontSize+6;
			var defaultIndent = menu.fontSize;
			if (item.label) {
				item = item.label;
				childMenu = true;
			}
			menu.menuItemHeight = menu.menuItemHeight || defaultHeight;
			menu.menuItemIndent = menu.menuItemIndent || defaultIndent;
			var itemProps = 'font-family:' + menu.fontFamily +';font-weight:' + menu.fontWeight + ';fontSize:' + menu.fontSize + ';';
			if (menu.fontStyle) itemProps += 'font-style:' + menu.fontStyle + ';';
			if (document.all) 
				itemProps += 'font-size:' + menu.fontSize + ';" onMouseOver="onMenuItemOver(null,this);" onClick="onMenuItemAction(null,this);';
			else if (!document.layers) {
				itemProps += 'font-size:' + menu.fontSize + 'px;'; // zilla wants 12px.
			}
			var l;
			if (lrs) {
				l = new Layer(800,body);
			}
      		document.writeln('');
			var dTag	= '<DIV ID="menuItem'+ countItems +'" STYLE="position:absolute;left:0;top:'+ (i * menu.menuItemHeight) +';'+ itemProps +'">';
			var dClose = '</DIV>'
			if (menu.bgImageUp) {
				menu.menuBorder = 0;
				menu.menuItemBorder = 0;
				dTag	= '<DIV ID="menuItem'+ countItems +'" STYLE="background:url('+menu.bgImageUp+');position:absolute;left:0;top:'+ (i * menu.menuItemHeight) +';'+ itemProps +'">';
				if (document.layers) {
					dTag = '<LAYER BACKGROUND="'+menu.bgImageUp+'" ID="menuItem'+ countItems +'" TOP="'+ (i * menu.menuItemHeight) +'" style="' + itemProps +'">';
					dClose = '</LAYER>';
				}
			}
			var textProps = 'position:absolute;left:' + menu.menuItemIndent + ';top:1;';
			if (lrs) {
				textProps +=itemProps;
				dTag = "";
				dClose = "";
			}

			var dText	= '<DIV ID="menuItemText'+ countItems +'" STYLE="' + textProps + 'color:'+ menu.fontColor +';">'+ item +'&nbsp</DIV>\n<DIV ID="menuItemHilite'+ countItems +'" STYLE="' + textProps + 'top:1;color:'+ menu.fontColorHilite +';visibility:hidden;">'+ item +'&nbsp</DIV>';
			if (item == "separator") {
				content += ( dTag + '<DIV ID="menuSeparator'+ countItems +'" STYLE="position:absolute;left:1;top:2;"></DIV>\n<DIV ID="menuSeparatorLite'+ countItems +'" STYLE="position:absolute;left:1;top:2;"></DIV>\n' + dClose);
			} else if (childMenu) {
				content += ( dTag + dText + '<DIV ID="childMenu'+ countItems +'" STYLE="position:absolute;left:0;top:3;"><IMG SRC="'+ menu.childMenuIcon +'"></DIV>\n' + dClose);
			} else {
				content += ( dTag + dText +  dClose);
			}
			if (lrs) {
				l.document.open("text/html");
				l.document.writeln(content);
				l.document.close();	
				content = '';
				theStat += "-";
				tsc++;
				if (tsc > 50) {
					tsc = 0;
					theStat = "";
				}
				status = theStat;
			}
			countItems++;  
		}
		if (lrs) {
			// focus layer
			var focusItem = new Layer(100, body);
			focusItem.visiblity="hidden";
			focusItem.document.open("text/html");
			focusItem.document.writeln("&nbsp;");
			focusItem.document.close();	
		} else {
		  content += '	  <DIV ID="focusItem'+ countMenus +'" STYLE="position:absolute;left:0;top:0;visibility:hide;" onClick="onMenuItemAction(null,this);">&nbsp;</DIV>\n';
		  content += '   </DIV>\n  </DIV>\n</DIV>\n';
		}
		i=x;
	}
	if (document.layers) {		
		container.clip.width = window.innerWidth;
		container.clip.height = window.innerHeight;
		container.onmouseout = mouseoutMenu;
		container.menuContainerBgColor = this.menuContainerBgColor;
		for (var i=0; i<container.document.layers.length; i++) {
			proto = container.menus[i];
			var menu = container.document.layers[i];
			container.menus[i].menuLayer = menu;
			container.menus[i].menuLayer.Menu = container.menus[i];
			container.menus[i].menuLayer.Menu.container = container;
			var body = menu.document.layers[0].document.layers[0];
			body.clip.width = proto.menuWidth || body.clip.width;
			body.clip.height = proto.menuHeight || body.clip.height;
			for (var n=0; n<body.document.layers.length-1; n++) {
				var l = body.document.layers[n];
				l.Menu = container.menus[i];
				l.menuHiliteBgColor = proto.menuHiliteBgColor;
				l.document.bgColor = proto.menuItemBgColor;
				l.saveColor = proto.menuItemBgColor;
				l.onmouseover = proto.onMenuItemOver;
				l.onclick = proto.onMenuItemAction;
				l.action = container.menus[i].actions[n];
				l.focusItem = body.document.layers[body.document.layers.length-1];
				l.clip.width = proto.menuWidth || body.clip.width + proto.menuItemIndent;
				l.clip.height = proto.menuItemHeight || l.clip.height;
				if (n>0) l.top = body.document.layers[n-1].top + body.document.layers[n-1].clip.height + proto.menuItemBorder;
				l.hilite = l.document.layers[1];
				if (proto.bgImageUp) l.background.src = proto.bgImageUp;
				l.document.layers[1].isHilite = true;
				if (l.document.layers[0].id.indexOf("menuSeparator") != -1) {
					l.hilite = null;
					l.clip.height -= l.clip.height / 2;
					l.document.layers[0].document.bgColor = proto.bgColor;
					l.document.layers[0].clip.width = l.clip.width -2;
					l.document.layers[0].clip.height = 1;
					l.document.layers[1].document.bgColor = proto.menuLiteBgColor;
					l.document.layers[1].clip.width = l.clip.width -2;
					l.document.layers[1].clip.height = 1;
					l.document.layers[1].top = l.document.layers[0].top + 1;
				} else if (l.document.layers.length > 2) {
					l.childMenu = container.menus[i].items[n].menuLayer;
					l.document.layers[2].left = l.clip.width -13;
					l.document.layers[2].top = (l.clip.height / 2) -4;
					l.document.layers[2].clip.left += 3;
					l.Menu.childMenus[l.Menu.childMenus.length] = l.childMenu;
				}
			}
			body.document.bgColor = proto.bgColor;
			body.clip.width  = l.clip.width +proto.menuBorder;
			body.clip.height = l.top + l.clip.height +proto.menuBorder;
			var focusItem = body.document.layers[n];
			focusItem.clip.width = body.clip.width;
			focusItem.Menu = l.Menu;
			focusItem.top = -30;
            focusItem.captureEvents(Event.MOUSEDOWN);
            focusItem.onmousedown = onMenuItemDown;
			menu.document.bgColor = proto.menuBorderBgColor;
			var lite = menu.document.layers[0];
			lite.document.bgColor = proto.menuLiteBgColor;
			lite.clip.width = body.clip.width +1;
			lite.clip.height = body.clip.height +1;
			menu.clip.width = body.clip.width + (proto.menuBorder * 3) ;
			menu.clip.height = body.clip.height + (proto.menuBorder * 3);
		}
	} else {
		if ((!document.all) && (container.hasChildNodes)) {
			container.innerHTML=content;
		} else {
			container.document.open("text/html");
			container.document.writeln(content);
			container.document.close();	
		}
		if (!FIND("menuLayer0")) return;
		var menuCount = 0;
		for (var x=0; x<container.menus.length; x++) {
			var menuLayer = FIND("menuLayer" + x);
			container.menus[x].menuLayer = "menuLayer" + x;
			menuLayer.Menu = container.menus[x];
			menuLayer.Menu.container = "menuLayer" + x;
			menuLayer.style.zIndex = 1;
		    var s = menuLayer.style;
			s.top = s.pixelTop = -300;
			s.left = s.pixelLeft = -300;

			var menu = container.menus[x];
			menu.menuItemWidth = menu.menuWidth || menu.menuIEWidth || 140;
			menuLayer.style.backgroundColor = menu.menuBorderBgColor;
			var top = 0;
			for (var i=0; i<container.menus[x].items.length; i++) {
				var l = FIND("menuItem" + menuCount);
				l.Menu = container.menus[x];
				if (l.addEventListener) { // ns6
					l.style.width = menu.menuItemWidth;	
					l.style.height = menu.menuItemHeight;
					l.style.top = top;
					l.addEventListener("mouseover", onMenuItemOver, false);
					l.addEventListener("click", onMenuItemAction, false);
					l.addEventListener("mouseout", mouseoutMenu, false);
				} else { //ie
					l.style.pixelWidth = menu.menuItemWidth;	
					l.style.pixelHeight = menu.menuItemHeight;
					l.style.pixelTop = top;
				}
				top = top + menu.menuItemHeight+menu.menuItemBorder;
				l.style.fontSize = menu.fontSize;
				l.style.backgroundColor = menu.menuItemBgColor;
				l.style.visibility = "inherit";
				l.saveColor = menu.menuItemBgColor;
				l.menuHiliteBgColor = menu.menuHiliteBgColor;
				l.action = container.menus[x].actions[i];
				l.hilite = FIND("menuItemHilite" + menuCount);
				l.focusItem = FIND("focusItem" + x);
				l.focusItem.style.pixelTop = l.focusItem.style.top = -30;
				var childItem = FIND("childMenu" + menuCount);
				if (childItem) {
					l.childMenu = container.menus[x].items[i].menuLayer;
					childItem.style.pixelLeft = childItem.style.left = menu.menuItemWidth -11;
					childItem.style.pixelTop = childItem.style.top =(menu.menuItemHeight /2) -4;
					//childItem.style.pixelWidth = 30 || 7;
					//childItem.style.clip = "rect(0 7 7 3)";
					l.Menu.childMenus[l.Menu.childMenus.length] = l.childMenu;
				}
				var sep = FIND("menuSeparator" + menuCount);
				if (sep) {
					sep.style.clip = "rect(0 " + (menu.menuItemWidth - 3) + " 1 0)";
					sep.style.width = sep.style.pixelWidth = menu.menuItemWidth;	
					sep.style.backgroundColor = menu.bgColor;
					sep = FIND("menuSeparatorLite" + menuCount);
					sep.style.clip = "rect(1 " + (menu.menuItemWidth - 3) + " 2 0)";
					sep.style.width = sep.style.pixelWidth = menu.menuItemWidth;	
					sep.style.backgroundColor = menu.menuLiteBgColor;
					l.style.height = l.style.pixelHeight = menu.menuItemHeight/2;
					l.isSeparator = true
					top -= (menu.menuItemHeight - l.style.pixelHeight)
				} else {
					l.style.cursor = "hand"
				}
				menuCount++;
			}
			menu.menuHeight = top-1;
			var lite = FIND("menuLite" + x);
			var s = lite.style;
			s.height = s.pixelHeight = menu.menuHeight +(menu.menuBorder * 2);
			s.width = s.pixelWidth = menu.menuItemWidth + (menu.menuBorder * 2);
			s.backgroundColor = menu.menuLiteBgColor;

			var body = FIND("menuFg" + x);
			s = body.style;
			s.height = s.pixelHeight = menu.menuHeight + menu.menuBorder;
			s.width = s.pixelWidth = menu.menuItemWidth + menu.menuBorder;
			s.backgroundColor = menu.bgColor;

			s = menuLayer.style;
			s.width = s.pixelWidth  = menu.menuItemWidth + (menu.menuBorder * 4);
			s.height = s.pixelHeight  = menu.menuHeight+(menu.menuBorder*4);
		}
	}
	if (document.captureEvents) {	
		document.captureEvents(Event.MOUSEUP);
	}
	if (document.addEventListener) {	
		document.addEventListener("mouseup", onMenuItemOver, false);
	}
	if (document.layers && window.innerWidth) {
		window.onresize = NS4resize;
		window.NS4sIW = window.innerWidth;
		window.NS4sIH = window.innerHeight;
	}
	document.onmouseup = mouseupMenu;
	window.fwWroteMenu = true;
	status = "";
}

function NS4resize() {
	if (NS4sIW < window.innerWidth || 
		NS4sIW > window.innerWidth || 
		NS4sIH > window.innerHeight || 
		NS4sIH < window.innerHeight ) 
	{
		window.location.reload();
	}
}

function onMenuItemOver(e, l) {
	FW_clearTimeout();
	l = l || this;
	a = window.ActiveMenuItem;
	if (document.layers) {
		if (a) {
			a.document.bgColor = a.saveColor;
			if (a.hilite) a.hilite.visibility = "hidden";
			if (a.Menu.bgImageOver) {
				a.background.src = a.Menu.bgImageUp;
			}
			a.focusItem.top = -100;
			a.clicked = false;
		}
		if (l.hilite) {
			l.document.bgColor = l.menuHiliteBgColor;
			l.zIndex = 1;
			l.hilite.visibility = "inherit";
			l.hilite.zIndex = 2;
			l.document.layers[1].zIndex = 1;
			l.focusItem.zIndex = this.zIndex +2;
		}
		if (l.Menu.bgImageOver) {
			l.background.src = l.Menu.bgImageOver;
		}
		l.focusItem.top = this.top;
		l.Menu.hideChildMenu(l);
	} else if (l.style && l.Menu) {
		if (a) {
			a.style.backgroundColor = a.saveColor;
			if (a.hilite) a.hilite.style.visibility = "hidden";
			if (a.Menu.bgImageUp) {
				a.style.background = "url(" + a.Menu.bgImageUp +")";;
			}
		} 
		if (l.isSeparator) return;
		l.style.backgroundColor = l.menuHiliteBgColor;
		l.zIndex = 1;  // magic IE 4.5 mac happy doohicky.	jba
		if (l.Menu.bgImageOver) {
			l.style.background = "url(" + l.Menu.bgImageOver +")";
		}
		if (l.hilite) {
			l.style.backgroundColor = l.menuHiliteBgColor;
			l.hilite.style.visibility = "inherit";
		}
		l.focusItem.style.top = l.focusItem.style.pixelTop = l.style.pixelTop;
		l.focusItem.style.zIndex = l.zIndex +1;
		l.Menu.hideChildMenu(l);
	} else {
		return; // not a menu - magic IE 4.5 mac happy doohicky.  jba
	}
	window.ActiveMenuItem = l;
}

function onMenuItemAction(e, l) {
	l = window.ActiveMenuItem;
	if (!l) return;
	hideActiveMenus();
	if (l.action) {
		eval("" + l.action);
	}
	window.ActiveMenuItem = 0;
}

function FW_clearTimeout()
{
  hideElms('SELECT');
	if (fwHideMenuTimer) clearTimeout(fwHideMenuTimer);
	fwHideMenuTimer = null;
	fwDHFlag = false;
}
function FW_startTimeout()
{
	fwStart = new Date();
	fwDHFlag = true;
	fwHideMenuTimer = setTimeout("fwDoHide(); showElms('SELECT');", 1000);
  
}

function fwDoHide()
{
	if (!fwDHFlag) return;
	var elapsed = new Date() - fwStart;
	if (elapsed < 1000) {
		fwHideMenuTimer = setTimeout("fwDoHide()", 1100-elapsed);
		return;
	}
	fwDHFlag = false;
	hideActiveMenus();
	window.ActiveMenuItem = 0;
}

function FW_showMenu(menu, x, y, child) {
	if (!window.fwWroteMenu) return;
	FW_clearTimeout();
	if (document.layers) {
		if (menu) {
			var l = menu.menuLayer || menu;
			l.left = 1;
			l.top = 1;
			hideActiveMenus();
			if (this.visibility) l = this;
			window.ActiveMenu = l;
		} else {
			var l = child;
		}
		if (!l) return;
		for (var i=0; i<l.layers.length; i++) { 			   
			if (!l.layers[i].isHilite) 
				l.layers[i].visibility = "inherit";
			if (l.layers[i].document.layers.length > 0) 
				FW_showMenu(null, "relative", "relative", l.layers[i]);
		}
		if (l.parentLayer) {
			if (x != "relative") 
				l.parentLayer.left = x || window.pageX || 0;
			if (l.parentLayer.left + l.clip.width > window.innerWidth) 
				l.parentLayer.left -= (l.parentLayer.left + l.clip.width - window.innerWidth);
			if (y != "relative") 
				l.parentLayer.top = y || window.pageY || 0;
			if (l.parentLayer.isContainer) {
				l.Menu.xOffset = window.pageXOffset;
				l.Menu.yOffset = window.pageYOffset;
				l.parentLayer.clip.width = window.ActiveMenu.clip.width +2;
				l.parentLayer.clip.height = window.ActiveMenu.clip.height +2;
				if (l.parentLayer.menuContainerBgColor) l.parentLayer.document.bgColor = l.parentLayer.menuContainerBgColor;
			}
		}
		l.visibility = "inherit";
		if (l.Menu) l.Menu.container.visibility = "inherit";
	} else if (FIND("menuItem0")) {
		var l = menu.menuLayer || menu;	
		hideActiveMenus();
		if (typeof(l) == "string") {
			l = FIND(l);
		}
		window.ActiveMenu = l;
		var s = l.style;
		s.visibility = "inherit";
		if (x != "relative") 
			s.left = s.pixelLeft = x || (window.pageX + document.body.scrollLeft) || 0;
		if (y != "relative") 
			s.top = s.pixelTop = y || (window.pageY + document.body.scrollTop) || 0;
		l.Menu.xOffset = document.body.scrollLeft;
		l.Menu.yOffset = document.body.scrollTop;
	}
	if (menu) {
		window.activeMenus[window.activeMenus.length] = l;
	}
}

function onMenuItemDown(e, l) {
	var a = window.ActiveMenuItem;
	if (document.layers) {
		if (a) {
			a.eX = e.pageX;
			a.eY = e.pageY;
			a.clicked = true;
		}
    }
}

function mouseupMenu(e)
{
	hideMenu(true, e);
	hideActiveMenus();
	return true;
}

function mouseoutMenu()
{
	hideMenu(false, false);
	return true;
}


function hideMenu(mouseup, e) {
	var a = window.ActiveMenuItem;
	if (a && document.layers) {
		a.document.bgColor = a.saveColor;
		a.focusItem.top = -30;
		if (a.hilite) a.hilite.visibility = "hidden";
		if (mouseup && a.action && a.clicked && window.ActiveMenu) {
 			if (a.eX <= e.pageX+15 && a.eX >= e.pageX-15 && a.eY <= e.pageY+10 && a.eY >= e.pageY-10) {
				setTimeout('window.ActiveMenu.Menu.onMenuItemAction();', 2);
			}
		}
		a.clicked = false;
		if (a.Menu.bgImageOver) {
			a.background.src = a.Menu.bgImageUp;
		}
	} else if (window.ActiveMenu && FIND("menuItem0")) {
		if (a) {
			a.style.backgroundColor = a.saveColor;
			if (a.hilite) a.hilite.style.visibility = "hidden";
			if (a.Menu.bgImageUp) {
				a.style.background = "url(" + a.Menu.bgImageUp +")";;
			}
		}
	}
	if (!mouseup && window.ActiveMenu) {
		if (window.ActiveMenu.Menu) {
			if (window.ActiveMenu.Menu.hideOnMouseOut) {
				FW_startTimeout();
			}
			return(true);
		}
	}
	return(true);
}

function PxToNum(pxStr)
{ // pxStr == 27px, we want 27.
	if (pxStr.length > 2) {
		n = Number(pxStr.substr(0, pxStr.length-2));
		return(n);
	}
	return(0);
}

function hideChildMenu(hcmLayer) {
	FW_clearTimeout();
	var l = hcmLayer;
	for (var i=0; i < l.Menu.childMenus.length; i++) {
		var theLayer = l.Menu.childMenus[i];
		if (document.layers) {
			theLayer.visibility = "hidden";
		} else {
			theLayer = FIND(theLayer);
			theLayer.style.visibility = "hidden";
		}
		theLayer.Menu.hideChildMenu(theLayer);
	}

	if (l.childMenu) {
		var childMenu = l.childMenu;
		if (document.layers) {
			l.Menu.FW_showMenu(null,null,null,childMenu.layers[0]);
			childMenu.zIndex = l.parentLayer.zIndex +1;
			childMenu.top = l.top + l.parentLayer.top + l.Menu.menuLayer.top + l.Menu.menuItemHeight/3;
			if (childMenu.left + childMenu.clip.width > window.innerWidth) {
				childMenu.left = l.parentLayer.left - childMenu.clip.width + l.Menu.menuLayer.left + 15;
				l.Menu.container.clip.left -= childMenu.clip.width;
			} else {
				childMenu.left = l.parentLayer.left + l.parentLayer.clip.width  + l.Menu.menuLayer.left -5;
			}
			var w = childMenu.clip.width+childMenu.left-l.Menu.container.clip.left;
			if (w > l.Menu.container.clip.width)  
				l.Menu.container.clip.width = w;
			var h = childMenu.clip.height+childMenu.top-l.Menu.container.clip.top;
			if (h > l.Menu.container.clip.height) l.Menu.container.clip.height = h;
			l.document.layers[1].zIndex = 0;
			childMenu.visibility = "inherit";
		} else if (FIND("menuItem0")) {
			childMenu = FIND(l.childMenu);
			var menuLayer = FIND(l.Menu.menuLayer);
			var s = childMenu.style;
			s.zIndex = menuLayer.style.zIndex+1;
			if (document.all) { // ie case.
				s.pixelTop = l.style.pixelTop + menuLayer.style.pixelTop + l.Menu.menuItemHeight/3;
				s.left = s.pixelLeft = (menuLayer.style.pixelWidth) + menuLayer.style.pixelLeft -5;
			} else { // zilla case
				var top = PxToNum(l.style.top) + PxToNum(menuLayer.style.top) + l.Menu.menuItemHeight/3;
				var left = (PxToNum(menuLayer.style.width)) + PxToNum(menuLayer.style.left) -5;
				s.top = top;
				s.left = left;
			}
			childMenu.style.visibility = "inherit";
		} else {
			return;
		}
		window.activeMenus[window.activeMenus.length] = childMenu;
	}
}

function hideActiveMenus() {
	if (!window.activeMenus) return;
	for (var i=0; i < window.activeMenus.length; i++) {
		if (!activeMenus[i]) continue;
		if (activeMenus[i].visibility && activeMenus[i].Menu) {
			activeMenus[i].visibility = "hidden";
			activeMenus[i].Menu.container.visibility = "hidden";
			activeMenus[i].Menu.container.clip.left = 0;
		} else if (activeMenus[i].style) {
			var s = activeMenus[i].style;
			s.visibility = "hidden";
			s.left = -200;
			s.top = -200;
		}
	}
	if (window.ActiveMenuItem) {
		hideMenu(false, false);
	}
	window.activeMenus.length = 0;
}


// Added 10.05.2002
function hideElms(elmTag) {
	for (i=0; i<document.all.tags(elmTag).length; i++){
		obj = document.all.tags(elmTag)[i];
		if (!obj || !obj.offsetParent) continue;
		obj.style.visibility = "hidden";
	}
}
function showElms(elmTag) {
	for (i=0; i<document.all.tags(elmTag).length; i++){
		obj = document.all.tags(elmTag)[i];
		if (!obj || !obj.offsetParent) continue;
		obj.style.visibility = "";
	}
}



