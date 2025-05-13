   NS4 = (document.layers);
   IE4 = (document.all);
  ver4 = (NS4 || IE4);   
 isMac = (navigator.appVersion.indexOf("Mac") != -1);
isMenu = (NS4 || (IE4 && !isMac));

function popUp(){return};
function popDown(){return};

if (!ver4) event = null;

if (isMenu) {
    menuVersion = 3;
    
    menuWidth = 200;
    childOverlap = 2;
    childOffset = 2;
    perCentOver = null;
    secondsVisible = .5;
    
    fntCol = "#000000";
    fntSiz = "8";
    fntBold = false;
    fntItal = false;
    fntFam = "Verdana, Tahoma, Arial";
    
    backCol = "#AEB6DA";
    overCol = "#000000";
    overFnt = "#FFFFFF";
    
    borWid = 1;
    borCol = "#000000";
    borSty = "solid";
    itemPad = 3;
    
    imgSrc = "images/arrow.gif";
    imgSiz = 8;
    
    separator = 1;
    separatorCol = "#AEB6DA";
    
    isFrames = false;      // <-- IMPORTANT for full window
    navFrLoc = "left";     // <-- display. see below
    
    keepHilite = true; 
    NSfontOver = true;
    clickStart = false;
    clickKill = false;
}

