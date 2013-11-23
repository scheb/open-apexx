////////////////////////////////////////////////////////////////////// OVERLAY

var ToolOverlay = function(url) {
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
	yDom.setStyle(this.container, 'top', (scrollTop+50)+'px');
	yDom.setStyle(this.container, 'width', (viewportWidth-100)+'px');
	yDom.setStyle(this.container, 'height', (viewportHeight-100)+'px');
	
	//Closebutton
	var close = document.createElement('img');
	close.className = 'close';
	close.alt = 'X';
	close.title = 'Schließen';
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
ToolOverlay.prototype.close = function() {
	this.visible = false;
	this.blacklayer.parentNode.removeChild(this.blacklayer);
	this.container.parentNode.removeChild(this.container);
}



//Overlay sichtbar?
ToolOverlay.prototype.isVisible = function() {
	return this.visible;
}



//Scrolling
ToolOverlay.prototype.handleScrolling = function() {
	var scrollTop = yDom.getDocumentScrollTop();
	yDom.setStyle(this.container, 'top', (scrollTop+50)+'px');
}


//Resize
ToolOverlay.prototype.handleResize = function() {
	var viewportWidth = yDom.getViewportWidth();
	var viewportHeight = yDom.getViewportHeight();
	var docWidth = yDom.getDocumentWidth();
	var docHeight = yDom.getDocumentHeight();
	
	//Blacklayer anpassen
	yDom.setStyle(this.blacklayer, 'width', docWidth+'px');
	yDom.setStyle(this.blacklayer, 'height', docHeight+'px');
	
	//Iframe anpassen
	yDom.setStyle(this.container, 'width', (viewportWidth-100)+'px');
	yDom.setStyle(this.container, 'height', (viewportHeight-100)+'px');
}