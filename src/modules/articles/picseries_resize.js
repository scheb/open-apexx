//***************** WINDOW RESIZE ******************
var scrwidth = screen.width;
var scrheight = screen.height;

	if ( resizex>scrwidth ) resizex=scrwidth;
	if ( resizey>scrheight ) resizey=scrheight;
	
var winxpos = (scrwidth/2)-(resizex/2);		
var winypos = (scrheight/2)-(resizey/2);		

window.moveTo(winxpos,winypos);
window.resizeTo(resizex,resizey);