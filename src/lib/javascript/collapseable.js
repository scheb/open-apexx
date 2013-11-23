////////////////////////////////////////////////////////////////////// AUFKLAPPBARER INHALT

//Konstruktor
var CollapseableContent = function(collapse, trigger, initStatus, animTime) {
	
	//Collapse-Objekt auslesen
	if (typeof collapse=='string') {
		this.collapseObj = yDom.get(collapse);
	}
	else if (collapse.tagName) {
		this.collapseObj = collapse;
	}
	
	//Trigger
	this.trigger = trigger;
	
	//Status OPEN/CLOSED
	if ( initStatus==CollapseableContent.OPEN || initStatus==CollapseableContent.CLOSED ) {
		this.status = initStatus;
	}
	else {
		this.status = CollapseableContent.CLOSED;
	}
	
	this.animation = null;
	this.animating = false;
	this.animationTime = animTime ? animTime/1000 : 500/1000;
}



//Konstanten
CollapseableContent.OPEN = 0;
CollapseableContent.CLOSED = 1;



//Auf und zu klappen
CollapseableContent.prototype.toggle = function() {
	
	//Zu schnelles Klicken verhindern
	if ( this.animating ) return;
	
	//Schließen
	if ( this.status==CollapseableContent.OPEN ) {
		
		//Flags setzen
		this.animating = true;
		this.status = CollapseableContent.CLOSED;
		
		//Eigenschaften aktualisieren
		yDom.setStyle(this.collapseObj, 'overflow', 'hidden');
		yDom.setStyle(this.collapseObj, 'display', 'block');
		
		//Höhe berechnen
		var padding = 0;
		padding += parseInt(yDom.getStyle(this.collapseObj, 'padding-bottom').replace(/px/, ''));
		padding += parseInt(yDom.getStyle(this.collapseObj, 'padding-top').replace(/px/, ''));
		var height = this.collapseObj.scrollHeight-padding;
		
		//Animation starten
		this.animation = new YAHOO.util.Anim(
			this.collapseObj,
			{ height: { from: height, to: 0 } },
			this.animationTime,
			YAHOO.util.Easing.easeOut
		);
		
		//Callback-Funktion
		var onComplete = function() {
			this.animating = false;
			this.trigger.onClosed();
			yDom.setStyle(this.collapseObj, 'display', 'none');
			yDom.setStyle(this.collapseObj, 'overflow', '');
			yDom.setStyle(this.collapseObj, 'height', '');
		};
		
		//Event nach Ende des Events
		this.animation.onComplete.subscribe(onComplete.bind(this));
		
		//Event auf Trigger aufrufen
		this.trigger.onAnimation();
		
		//Und los
		this.animation.animate();
	}
	
	//Öffnen
	else {
		
		//Flags setzen
		this.animating = true;
		this.status = CollapseableContent.OPEN;
		
		//Eigenschaften aktualisieren
		yDom.setStyle(this.collapseObj, 'height', '0px');
		yDom.setStyle(this.collapseObj, 'overflow', 'hidden');
		yDom.setStyle(this.collapseObj, 'display', 'block');
		
		//Höhe berechnen
		var padding = 0;
		padding += parseInt(yDom.getStyle(this.collapseObj, 'padding-bottom').replace(/px/, ''));
		padding += parseInt(yDom.getStyle(this.collapseObj, 'padding-top').replace(/px/, ''));
		var height = this.collapseObj.scrollHeight-padding;
		
		//Animation starten
		this.animation = new YAHOO.util.Anim(
			this.collapseObj,
			{ height: { from: 0, to: height } },
			this.animationTime,
			YAHOO.util.Easing.easeOut
		);
		
		//Callback-Funktion
		var onComplete = function() {
			this.animating = false;
			this.trigger.onOpened();
			yDom.setStyle(this.collapseObj, 'display', '');
			yDom.setStyle(this.collapseObj, 'overflow', '');
			yDom.setStyle(this.collapseObj, 'height', '');
		};
		
		//Event nach Ende des Events
		this.animation.onComplete.subscribe(onComplete.bind(this));
		
		//Event auf Trigger aufrufen
		this.trigger.onAnimation();
		
		//Und los
		this.animation.animate();
	}
}



//Ist das Menü offen
CollapseableContent.prototype.isOpen = function() {
	return (this.status==CollapseableContent.OPEN);
}




////////////////////////////////////////////////////////////////////// TRIGGER (ABSTRACT)

var CollapseTrigger = function() {}
CollapseTrigger.prototype.onOpened = function() { }
CollapseTrigger.prototype.onAnimation = function() { }
CollapseTrigger.prototype.onClosed = function() { }



////////////////////////////////////////////////////////////////////// TRIGGER (BILD)

//Konstruktor
var CollapseTriggerImage = function(image, expandImage, collapseImage, activeImage) {
	CollapseTriggerImage.superclass.constructor.call();
	
	//Image-Objekt auslesen
	if (typeof image=='string') {
		this.toggleImg = yDom.get(image);
	}
	else if (image.tagName) {
		this.toggleImg = image;
	}
	
	//Config
	this.expandImage = expandImage;
	this.collapseImage = collapseImage;
	this.activeImage = collapseImage;
	
	//Preload Images
	var imageObj = new Image();
	if ( this.imgExpand!=null ) imageObj.src = this.imgExpand;
	if ( this.imgCollapse!=null ) imageObj.src = this.imgCollapse;
	if ( this.imgActive!=null ) imageObj.src = this.imgActive;
}



//Extend AbstractCollapseTrigger
YAHOO.lang.extend(CollapseTriggerImage, CollapseTrigger);



//onOpened
CollapseTriggerImage.prototype.onOpened = function() {
	if ( this.collapseImage!=null ) {
		this.toggleImg.src = this.collapseImage;
	}
}



//onAnimation
CollapseTriggerImage.prototype.onAnimation = function() {
	if ( this.activeImage!=null ) {
		this.toggleImg.src = this.activeImage;
	}
}



//onClosed
CollapseTriggerImage.prototype.onClosed = function() {
	if ( this.expandImage!=null ) {
		this.toggleImg.src = this.expandImage;
	}
}



////////////////////////////////////////////////////////////////////// TRIGGER (CLASSNAME)

//Konstruktor
var CollapseTriggerClass = function(domObject, expandedClass, collapsedClass, activeClass) {
	CollapseTriggerImage.superclass.constructor.call();
	
	//Image-Objekt auslesen
	if (typeof domObject=='string') {
		this.toggleObj = yDom.get(domObject);
	}
	else if (domObject.tagName) {
		this.toggleObj = domObject;
	}
	
	//Config
	this.expandedClass = expandedClass;
	this.collapsedClass = collapsedClass;
	this.activeClass = activeClass;
}



//Extend AbstractCollapseTrigger
YAHOO.lang.extend(CollapseTriggerClass, CollapseTrigger);



//onOpened
CollapseTriggerClass.prototype.onOpened = function() {
	if ( YAHOO.lang.isString(this.expandedClass) ) {
		yDom.removeClass(this.toggleObj, this.collapsedClass);
		yDom.addClass(this.toggleObj, this.expandedClass);
	}
}



//onAnimation
CollapseTriggerClass.prototype.onAnimation = function() {
	if ( YAHOO.lang.isString(this.activeClass) ) {
		yDom.addClass(this.toggleObj, this.activeClass);
	}
}



//onClosed
CollapseTriggerClass.prototype.onClosed = function() {
	if ( YAHOO.lang.isString(this.collapsedClass) ) {
		yDom.removeClass(this.toggleObj, this.expandedClass);
		yDom.addClass(this.toggleObj, this.collapsedClass);
	}
}