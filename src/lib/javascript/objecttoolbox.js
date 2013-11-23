////////////////////////////////////////////////////////////////////// TOOLBOX-MANAGER

//Konstruktor
var ObjectToolboxManager =  { toolboxes: {} };



ObjectToolboxManager.identifier = 0;



//Toolbox registrieren
ObjectToolboxManager.register = function(object) {
	++ObjectToolboxManager.identifier;
	this.toolboxes[ObjectToolboxManager.identifier] = object;
	return ObjectToolboxManager.identifier;
}



//Toolbox entfernen
ObjectToolboxManager.unregister = function(identifier) {
	if ( YAHOO.lang.isObject(this.toolboxes[identifier]) ) {
		this.toolboxes[identifier] = null;
	}
}



//Toolbox zurückgeben
ObjectToolboxManager.get = function(identifier) {
	if ( YAHOO.lang.isObject(this.toolboxes[identifier]) ) {
		return this.toolboxes[identifier];
	}
	else {
		return null;
	}
}



////////////////////////////////////////////////////////////////////// OBJECT-TOOLBOX

var ObjectToolbox = function(field, actionCreate, params, addnlFields) {
	this.actionCreate = actionCreate;
	this.params = params!='undefined' ? params : '';
	this.field = field;
	this.addnlFields = YAHOO.lang.isArray(addnlFields) ? addnlFields : new Array();
	
	//Toolbox registrieren
	this.identifier = ObjectToolboxManager.register(this);
	
	//Neu
	this.createImage = document.createElement('img');
	this.createImage.src = 'design/sadd.gif';
	this.createImage.onclick = this.handleCreateClick.bind(this);
	yDom.setStyle(this.createImage, 'cursor', 'pointer');
	
	//DOM hinzufügen
	yDom.insertAfter(this.createImage, field);
	yDom.insertAfter(document.createTextNode(' '), field);
}



//Overlay-Object
ObjectToolbox.prototype.overlay = null;



//OnClick: Neues Objekt erzeugen
ObjectToolbox.prototype.handleCreateClick = function() {
	if ( !YAHOO.lang.isNull(this.overlay) && this.overlay.isVisible() ) return;
	this.overlay = new ObjectToolboxOverlay('action.php?action='+this.actionCreate+'&updateparent='+this.identifier+(this.params ? '&'+this.params : ''));
}



//Ausgewähltes Objekt aktualisieren
ObjectToolbox.prototype.updateObject = function(innerHTML) {
	
	//Feld aktualisieren
	this.updateField(this.getField(this.field), innerHTML);
		
	//Synchronisierte Felder aktualisieren
	for ( var i=0; i<this.addnlFields.length; i++ ) {
		var field = this.addnlFields[i];
		field = this.getField(field);
		var selection = this.getSelection(field);
		this.updateField(field, innerHTML);
		field = this.getField(field);
		this.setSelection(field, selection);
	}
	
	//Overlay löschen
	this.overlay.close();
	this.overlay = null;
}



//Feld auslesen
ObjectToolbox.prototype.getField = function(field) {
	if ( YAHOO.env.ua.ie ) {
		var debugField = yDom.get('iebugId_'+field.name);
		if ( YAHOO.lang.isObject(debugField) ) {
			return debugField;
		}
		else {
			return field;
		}
	}
	else {
		return field;
	}
}



//Feld aktualisieren
ObjectToolbox.prototype.updateField = function(field, innerHTML) {
	if ( YAHOO.env.ua.ie ) {
		field.outerHTML = '<select name="'+field.name+'" id="iebugId_'+field.name+'">'+innerHTML+'</select>';
	}
	else {
		field.innerHTML = innerHTML;
	}
}



//Selektion in einem Feld auslesen
ObjectToolbox.prototype.getSelection = function(field) {
	var selection = new Array();
	for ( var i=0; i<field.options.length; i++ ) {
		if ( field.options[i].selected ) {
			selection.push(field.options[i].value);
		}
	}
	return selection;
}



//Selektion in einem Feld setzen
ObjectToolbox.prototype.setSelection = function(field, selection) {
	for ( var i=0; i<field.options.length; i++ ) {
		field.options[i].selected = selection.indexOf(field.options[i].value)!=-1;
	}
}



////////////////////////////////////////////////////////////////////// OVERLAY

var ObjectToolboxOverlay = function(url) {
	this.visible = true;
	
	var viewportWidth = yDom.getViewportWidth();
	var viewportHeight = yDom.getViewportHeight();
	var docWidth = yDom.getDocumentWidth();
	var docHeight = yDom.getDocumentHeight();
	var scrollTop = yDom.getDocumentScrollTop();
	
	//Abblende-Layer erzeugen
	this.blacklayer = document.createElement('div');
	this.blacklayer.className = 'actionoverlay_black';
	yDom.setStyle(this.blacklayer, 'width', docWidth+'px');
	yDom.setStyle(this.blacklayer, 'height', docHeight+'px');
	document.body.appendChild(this.blacklayer);
	
	//Container erzeugen
	this.container = document.createElement('div');
	this.container.className = 'actionoverlay';
	yDom.setStyle(this.container, 'top', (scrollTop+20)+'px');
	yDom.setStyle(this.container, 'width', (viewportWidth-40)+'px');
	yDom.setStyle(this.container, 'height', (viewportHeight-40)+'px');
	
	//Closebutton
	var close = document.createElement('img');
	close.className = 'close';
	close.alt = 'X';
	close.src = 'design/close.gif';
	close.onclick = this.close.bind(this);
	
	//Iframe
	var iframe = document.createElement('iframe');
	iframe.src = url;
	iframe.border = 0;
	iframe.frameBorder = 0;
	
	this.container.appendChild(close);
	this.container.appendChild(iframe);
	document.body.appendChild(this.container);
	
	//Scroll-Listener
	yEvent.addListener(window, 'scroll', this.handleScrolling.bind(this));
	
	//Resize-Listener
	yEvent.addListener(window, 'resize', this.handleResize.bind(this));
}



//Layer schließen
ObjectToolboxOverlay.prototype.close = function() {
	this.visible = false;
	this.blacklayer.parentNode.removeChild(this.blacklayer);
	this.container.parentNode.removeChild(this.container);
}



//Overlay sichtbar?
ObjectToolboxOverlay.prototype.isVisible = function() {
	return this.visible;
}



//Scrolling
ObjectToolboxOverlay.prototype.handleScrolling = function() {
	var scrollTop = yDom.getDocumentScrollTop();
	yDom.setStyle(this.container, 'top', (scrollTop+20)+'px');
}


//Resize
ObjectToolboxOverlay.prototype.handleResize = function() {
	var viewportWidth = yDom.getViewportWidth();
	var viewportHeight = yDom.getViewportHeight();
	var docWidth = yDom.getDocumentWidth();
	var docHeight = yDom.getDocumentHeight();
	
	//Blacklayer anpassen
	yDom.setStyle(this.blacklayer, 'width', docWidth+'px');
	yDom.setStyle(this.blacklayer, 'height', docHeight+'px');
	
	//Iframe anpassen
	yDom.setStyle(this.container, 'width', (viewportWidth-40)+'px');
	yDom.setStyle(this.container, 'height', (viewportHeight-40)+'px');
}
