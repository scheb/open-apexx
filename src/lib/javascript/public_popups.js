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


//***************** POPUP SMILIES ******************
function showsmilies(subdir,formname,fieldname) {
	if ( !formname || formname=='undefined' || formname==null ) formname='textform';
	if ( !fieldname || fieldname=='undefined' || fieldname==null ) fieldname='text';
	if ( !subdir || subdir=='undefined' || subdir==null ) subdir='';
	else subdir+='/';
	fenster = window.open(subdir+"misc.php?action=smilies&form="+formname+"&field="+fieldname,"showsmilies","toolbar=no,scrollbars=yes,resizable=yes,dependent=yes,width=320,height=450");
	fenster.focus();
}


//***************** POPUP CODES ******************
function showcodes(subdir,formname,fieldname) {
	if ( !formname || formname=='undefined' || formname==null ) formname='textform';
	if ( !fieldname || fieldname=='undefined' || fieldname==null ) fieldname='text';
	if ( !subdir || subdir=='undefined' || subdir==null ) subdir='';
	else subdir+='/';
	fenster = window.open(subdir+"misc.php?action=codes&form="+formname+"&field="+fieldname,"showcodes","toolbar=no,resizable=yes,scrollbars=yes,dependent=yes,width=450,height=350");
	fenster.focus();
}


//***************** POPUP WINDOW ******************
function popupwin(url,width,height,resizeable,center) {
	
	//Standardwerte
	if ( center=='' || center=='undefined' || center==null ) center=1;
	if ( resizeable=='' || resizeable=='undefined' || resizeable==null ) resizeable=0;
	
	//Resizeable
	if ( resizeable!=0 ) resizeable='yes';
	
	//Zentrieren
	if ( center!=0 && center!='' && center!=null ) {
		var positionx=(screen.availWidth-width)/2;
		var positiony=(screen.availHeight-height)/2;
		fenster = window.open(url,"_blank","toolbar=no,resizable="+resizeable+",scrollbars="+resizeable+",width="+width+",height="+height+",screenx="+positionx+",screeny="+positiony+",left="+positionx+",top="+positiony);
	}
	
	//Nicht zentrieren
	else {
		fenster = window.open(url,"popwin"+wincount,"Toolbar=no,resizable=yes,scrollbars=yes,width="+width+",height="+height);
	}
	
	fenster.focus();
}


//***************** POPUP PICTURE ******************
function popuppic(url,width,height) {
	scrolling=0;
	
	//Bild zu breit?
	if ( width>screen.availWidth ) {
		height=parseInt(height)+20;
		scrolling=1;
	}
	
	//Bild zu hoch?
	if ( height>screen.availHeight ) {
		width=parseInt(width)+20;
		scrolling=1;
	}
	
	//Fenster öffnen
	popupwin(url,width,height,scrolling,1);
}