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



/* Objekt um Anhänge aufzulisten */
function attachment(objid) {
	this.obj=getobject(objid);
	this.add=attachment_add;
	this.reset=attachment_reset;
	this.manager=attachment_manager;
}


/* Anzeige zurücksetzen */
function attachment_reset() {
	this.obj.innerHTML='';
}


/* Anhang der Liste hinzufügen */
function attachment_add(name,size,icon) {
	this.obj.innerHTML=this.obj.innerHTML+'<div><img src="'+icon+'" alt="" style="vertical-align:middle;" /> '+name+' ('+size+')</div>';
}


/* Fenster öffnen */
function attachment_manager(postid,hash) {
	var width=400;
	var height=350;
	window.open('attachments.php?postid='+postid+'&hash='+hash,'attachments','dependent=yes,menubar=no,resizable=yes,width='+width+',height='+height+',scrollbars=yes,toolbar=no');
}
