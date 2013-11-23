/***************************************************************\
|                                                               |
|                   apexx CMS & Portalsystem                    |
|                 ============================                  |
|           (c) Copyright 2005-2009, Christian Scheb            |
|                  http://www.stylemotion.de                    |
|                                                               |
|---------------------------------------------------------------|
| THIS SOFTWARE IS NOT FREE! MAKE SURE YOU OWN A VALID LICENSE! |
| DO NOT REMOVE ANY COPYRIGHTS WITHOUT PERMISSION!              |
| SOFTWARE BELONGS TO ITS AUTHORS!                              |
\***************************************************************/


//***************** WINDOW RESIZE ******************
var scrwidth = screen.width;
var scrheight = screen.height;

	if ( resizex>scrwidth ) resizex=scrwidth;
	if ( resizey>scrheight ) resizey=scrheight;
	
var winxpos = (scrwidth/2)-(resizex/2);		
var winypos = (scrheight/2)-(resizey/2);		

window.moveTo(winxpos,winypos);
window.resizeTo(resizex,resizey);