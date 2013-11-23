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


//***************** MENUE KLAPPEN ******************

//Men¸ Verstecken
function toggle_forum(theid) {
	var obj=getobject('forum_list_'+theid);
	var collapse=getIntArray(getCookie('apx_forum_togglelist'));
	
	//Men¸ ist geschlossen => ˆffnen
	if ( inArray(collapse,theid) ) {
		obj.style.display = '';
		getobject('forum_toggle_'+theid).src='design/collapse.gif';
		collapse=removeFromArray(collapse,theid);
	}
	
	//Men¸ ist offen => schlieﬂen
	else {
		obj.style.display = 'none';
		getobject('forum_toggle_'+theid).src='design/extend.gif';
		collapse[collapse.length]=theid;
	}
	
	setCookie('apx_forum_togglelist',collapse.join('|'));
}


//In einem Array?
function inArray(array,find) {
	for ( i=0; i<array.length; i++ ) {
		if ( array[i]==find ) return true;
	}
	return false;
}


//Position in einem Array
function elementPos(array,find) {
	for ( i=0; i<array.length; i++ ) {
		if ( array[i]==find ) return i;
	}
	return null;
}


//Element aus einem Array entfernen
function removeFromArray(array,element) {
	var pos=elementPos(array,element);
	if ( pos==null ) return array; //Nicht gefunden
	var out=new Array();
	var ii=0;
	for ( i=0; i<pos; i++ ) {
		out[ii++]=array[i];
	}
	for ( i=pos+1; i<array.length; i++ ) {
		out[ii++]=array[i];
	}
	return out;
}


//Cookie setzen
function setCookie(cookieName,value) {
	var a = new Date();
	a = new Date(a.getTime()+1000*60*60*24*100);
	document.cookie=cookieName+"="+value+"; path=/; expires="+a.toGMTString()+";";
}


//Wert eines Cookies auslesen
function getCookie(cookieName) {
	var a_start, an_end;
	if ( document.cookie ) {
		a_start=document.cookie.indexOf(cookieName+'=');
		if ( a_start<0) return '';
		a_start=document.cookie.indexOf('=',a_start)+1;
		an_end=document.cookie.indexOf(';',a_start);
		if ( an_end<0 ) an_end=document.cookie.length;
		return unescape(document.cookie.substring(a_start,an_end));
	}
	else return "";
}


//Integer-Array aus Dash-String
function getIntArray(info) {
	var data=new Array();
	if ( data!=null ) {
		var tempArray=info.split('|');
		var j=0;
		for ( i=0; i<tempArray.length; i++ ) {
			intValue=parseInt(tempArray[i]);
			if ( isNaN(intValue) ) continue;
			data[j++]=intValue;
		}
	}
	return data;
}


//Checkboxen togglen
function checkbox_toggle(form,setit,findname) {
	for (var i=0; i<form.elements.length; i++) {
		var element=form.elements[i];
		
		if ( element.type=='checkbox' && element.name.match(findname)!=null ) {
			element.checked=setit;
		}
	}
}