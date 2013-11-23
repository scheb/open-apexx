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


//***************** MEDIAMANAGER ÖFFNEN ******************

//***************** POPUP CODES ******************
function openmm(url) {
	var positionx=(screen.availWidth-800)/2;
	var positiony=(screen.availHeight-600)/2;
	mm = window.open(url,"mmanager","toolbar=yes,scrollbars=yes,resizable=yes,width=800,height=600,screenx="+positionx+",screeny="+positiony+",left="+positionx+",top="+positiony);
	mm.focus();
}
