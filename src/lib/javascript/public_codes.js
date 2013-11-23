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



//***************** GRÖSSE DES TEXTFELDS ******************

var fieldStartRows = -1;

function field_expand( field ) {
	if ( fieldStartRows==-1 ) fieldStartRows = field.rows;
	field.rows+=3;
}

function field_collapse( field ) {
	if ( fieldStartRows==-1 ) fieldStartRows = field.rows;
	if ( field.rows-3>=fieldStartRows ) {
		field.rows-=3;
	}
}



//***************** TEXT EINFÜGEN ******************

//Text an aktueller Position einfügen
function insert_code(id,first,last,selection_value) {
	if ( (typeof id)=='string' ) {
		var obj=getobject(id);
	}
	else {
		var obj=getobject(id);
	}
	
	//IE
	if ( document.selection ) {
		obj.focus();
		sel=document.selection.createRange();
		if ( selection_value==null || selection_value=='undefined' ) var selection_value=sel.text;
		var value=first+selection_value+last;
		sel.text=value;
		
		//Nichts gewählt => Zwischen den Tags
		if ( selection_value=='' ) {
			sel.moveStart('character',-1*last.length);
			sel.moveEnd('character',-1*last.length);
		}
		
		sel.select();
	}
	
	//Mozilla
	else if ( obj.selectionStart || obj.selectionStart== 0) {
		var startpos=obj.selectionStart;
		var endpos=obj.selectionEnd;
		if ( selection_value==null || selection_value=='undefined' ) var selection_value=obj.value.substring(startpos,endpos);
		var value=first+selection_value+last;
		
		obj.value=obj.value.substring(0,startpos)
		+value
		+obj.value.substring(endpos,obj.value.length);
		obj.focus();
		
		//Nichts gewählt => Zwischen den Tags
		if ( selection_value=='' ) {
			obj.selectionStart=startpos+first.length;
			obj.selectionEnd=startpos+first.length;
		}
		
		//Auswahl => Ans Ende
		else {
			obj.selectionStart=startpos+value.length;
			obj.selectionEnd=startpos+value.length;
		}
	}
	
	//ELSE
	else {
		obj.value+='['+thecode+'][/'+thecode+']';
		obj.focus();
	}
}



//Einfache Codes
function simplecode(objid,thecode) {
	insert_code(objid,'['+thecode+']','[/'+thecode+']');
}



//Text-Formatierung
function formatcode(objid,thecode,selectbox) {
	if ( selectbox.options[selectbox.selectedIndex].value=='' ) return;
	param=selectbox.options[selectbox.selectedIndex].value;
	insert_code(objid,'['+thecode+'='+param+']','[/'+thecode+']');
	selectbox.selectedIndex=0;
}



//Einfache Codes
function linkcodenew(objid,thecode) {
	var message='';
	if ( thecode=='IMG' ) message=lang_enterimage;
	else if ( thecode=='URL' ) message=lang_enterurl;
	else if ( thecode=='EMAIL' ) message=lang_entermail;
	else return;
	
	param=prompt(message+':','');
	
	if ( param!=null && param!='' ) {
		if ( thecode=='IMG' ) insert_code(objid,'['+thecode+']','[/'+thecode+']',param);
		else insert_code(objid,'['+thecode+'='+param+']','[/'+thecode+']');
	}
}



//Einfache Codes
function listcode(objid,listtype) {
	
	//Numerische und Alphabetische Listen
	if ( listtype=='1' || listtype=='a' ) {
		liststart='[LIST='+listtype+"]\n";
		listend='[/LIST]';
	}
	
	//Normale Liste
	else {
		liststart="[LIST]\n";
		listend='[/LIST]';
	}
	
	//Listenanfang und Ende einfügen
	insert_code(objid,liststart,listend,'');
	
	//Listenpunkte einlesen
	theentry='whatever';
	while ( theentry!='' && theentry!=null ) {
		theentry=prompt(lang_enterlist,'');
		
		if ( theentry!='' && theentry!=null ) {
			insert_code(objid,'[*]'+theentry+"\n",'','');
		}
	}
}



//**************************** ALTE FUNKTIONEN ****************************

//Simple Codes
function code(thecode,prptext,formname,fieldname) {
	console.log(arguments);
	
	//Voreinstellungen
	if ( formname ) eval("form=document."+formname);
	else form=document.textform;
	if ( fieldname ) eval("field=form."+fieldname);
	else field=form.text;
	
	simplecode(field, thecode);
}



//Links
function linkcode(thetype,formname,fieldname) {
	console.log(arguments);
	//Voreinstellungen
	if ( formname ) eval("form=document."+formname);
	else form=document.textform;
	if ( fieldname ) eval("field=form."+fieldname);
	else field=form.text;
	
	linkcodenew(field, thetype);
}