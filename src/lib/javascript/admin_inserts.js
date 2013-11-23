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


//***************** MEDIENDATEN EINFÜGEN ******************

function insert_image(id,path) {
	/*if ( path.match('-thumb.')!=null ) dopopup=true; //confirm(lang_imgtotext);
	else dopopup=false;*/

	if ( editor_status==1 && in_array(id,editors) ) {
		/*if ( dopopup===true ) insert_toeditor(id,'%POPUP('+path+')%');*/
		insert_in_editor(id,'<img src="'+path+'" alt="" />');
	}
	else {
		/*if ( dopopup===true ) eval('document.textform.'+id+'.value+="%POPUP('+path+')%";');*/
		insert_in_textarea(id,'<img src="'+path+'" alt="" />');
	}
	
	getobject(id).focus();
}

function insert_add(id,path) {
	if ( editor_status==1 && in_array(id,editors) ) {
		insert_in_editor(id,path);
	}
	else {
		insert_in_textarea(id,path);
	}
}

function insert_replace(id,path) {
	obj=getobject(id);
	if ( obj==null || obj=='undefined' ) return;
	obj.value=path;
	window.focus();
}


//***************** INSERTS ******************

function insert_in_editor(id,html) {
	var editor = CKEDITOR.instances[id];
	editor.insertHtml(html);
}

function insert_in_textarea(id,value) {
	obj=getobject(id);
	
	//IE support
	if ( document.selection ) {
		obj.focus();
		sel=document.selection.createRange();
		sel.text=value;
		obj.focus();
	}
	
	//MOZILLA/NETSCAPE
	else if ( obj.selectionStart || obj.selectionStart== 0) {
		var startPos=obj.selectionStart;
		var endPos=obj.selectionEnd;
		obj.value=obj.value.substring(0,startPos)
		+value
		+obj.value.substring(endPos,obj.value.length);
		obj.focus();
		obj.selectionStart=startPos+value.length;
		obj.selectionEnd=startPos+value.length;
	}
	
	//ELSE
	else {
		obj.value+=value;
		obj.focus();
	}
}


//***************** TEMPLATES ******************

function insert_template(dropdown,eid) {
	if ( dropdown==null || dropdown=='undefined' || dropdown=='' ) return;
	if ( dropdown.options[dropdown.options.selectedIndex].value=='' ) return;
	//eval("if ( this.form."+eid+"==null || this.form."+eid+"=='undefined' || this.form."+eid+"=='' ) return;");

addcode=templates[dropdown.options[dropdown.options.selectedIndex].value];

	if ( editor_status==1 && in_array(eid,editors) ) {
		insert_in_editor(eid,addcode);
	}
	else {
		insert_in_textarea(eid,addcode);
	}
	
	dropdown.options.selectedIndex=0;
}