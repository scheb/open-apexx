// TODO: Zitierfunktion mit Angabe des Namen

function quote_insertion(id,start,end,gets_inserted) {

		var obj=getobject(id);
		//InternetExplorer
		if ( document.selection ) {
		obj.focus();
		slctn=document.selection.createRange();

		if ( gets_inserted==null || gets_inserted=='undefined' ) var gets_inserted=slctn.text;
		var value=start+gets_inserted+end;
		slctn.text=value;
		
		if ( gets_inserted=='' ) {
			slctn.moveStart('character',-1*end.length);
			slctn.moveEnd('character',-1*end.length);
		}
		
		slctn.select();
	}
	
	//MozillaFirefox
	else if ( obj.selectionStart || obj.selectionStart== 0) {
		var startpos=obj.selectionStart;
		var endpos=obj.selectionEnd;
		if ( gets_inserted==null || gets_inserted=='undefined' ) var gets_inserted=obj.value.substring(startpos,endpos);
		var value=start+gets_inserted+end;
		
		obj.value=obj.value.substring(0,startpos)
		+value
		+obj.value.substring(endpos,obj.value.length);
		obj.focus();
		
		if ( gets_inserted=='' ) {
			obj.selectionStart=startpos+start.length;
			obj.selectionEnd=startpos+start.length;
		}
		else {
			obj.selectionStart=obj.selectionEnd;
		}
	}
	
	//Else
	else {
		obj.value += start+end;
		obj.focus();
	}
}



function quote(id,fkt,name) {
//	quote_insertion(id,'['+fkt+'='+name+']','[/'+fkt+']');
	quote_insertion(id,'['+fkt+']','[/'+fkt+']');
}