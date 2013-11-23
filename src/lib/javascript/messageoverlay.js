///////////////////////////////////////////////////////////////////// OVERLAY-MANAGER

//Layer-Manager
var MessageOverlayManager = {};



//Aktueller Layer
MessageOverlayManager.layer = null;



//Layer erzeugen
MessageOverlayManager.createLayer = function(url) {
	this.layer = new MessageOverlay(url);
}



//Layer schlieﬂen
MessageOverlayManager.closeLayer = function() {
	if ( this.layer ) {
		this.layer.close();
	}
	this.layer = null;
}



///////////////////////////////////////////////////////////////////// OVERLAY

var MessageOverlay = function(url) {
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
	var scrollTop = yDom.getDocumentScrollTop();
	this.container = document.createElement('div');
	this.container.className = 'messageoverlay';
	yDom.setStyle(this.container, 'top', scrollTop+'px');
	yDom.setStyle(this.container, 'width', viewportWidth+'px');
	yDom.setStyle(this.container, 'height', viewportHeight+'px');
	
	//Iframe
	var iframe = document.createElement('iframe');
	iframe.allowTransparency = true;
	iframe.scrolling = 'no';
	iframe.src = url;
	iframe.border = 0;
	iframe.frameBorder = 0;
	
	//Ab IE7 ¸ber onreadystate gehen
	if ( YAHOO.env.ua.ie>=7 ) {
		iframe.onreadystatechange = function() {
			if ( iframe.readyState=="complete" ) {
				iframe.style.display = '';
			}
		}
		iframe.style.display = 'none';
	}
	
	//Opera kennt kein Onload
	else if ( !YAHOO.env.ua.opera ) {
		iframe.onload = function() {
			iframe.style.display = '';
		};
		iframe.style.display = 'none';
	}
	
	this.container.appendChild(iframe);
	document.body.appendChild(this.container);
	
	if ( YAHOO.env.ua.ie ) {
		window.focus();
	}
	else {
		document.forms['removefocus'].style.display = '';
		document.forms['removefocus'].elements[0].focus();
		document.forms['removefocus'].elements[0].blur();
		document.forms['removefocus'].style.display = 'none';
	}
	
	//Scroll-Listener
	yEvent.addListener(window, 'scroll', this.handleScrolling.bind(this));
	
	//Resize-Listener
	yEvent.addListener(window, 'resize', this.handleResize.bind(this));
}



//Layer schlieﬂen
MessageOverlay.prototype.close = function() {
	this.visible = false;
	this.blacklayer.parentNode.removeChild(this.blacklayer);
	this.container.parentNode.removeChild(this.container);
}



//Overlay sichtbar?
MessageOverlay.prototype.isVisible = function() {
	return this.visible;
}



//Scrolling
MessageOverlay.prototype.handleScrolling = function() {
	var scrollTop = yDom.getDocumentScrollTop();
	yDom.setStyle(this.container, 'top', scrollTop+'px');
}



//Resize
MessageOverlay.prototype.handleResize = function() {
	var viewportWidth = yDom.getViewportWidth();
	var viewportHeight = yDom.getViewportHeight();
	var docWidth = yDom.getDocumentWidth();
	var docHeight = yDom.getDocumentHeight();
	
	//Blacklayer anpassen
	yDom.setStyle(this.blacklayer, 'width', docWidth+'px');
	yDom.setStyle(this.blacklayer, 'height', docHeight+'px');
	
	//Iframe anpassen
	yDom.setStyle(this.container, 'width', viewportWidth+'px');
	yDom.setStyle(this.container, 'height', viewportHeight+'px');
}