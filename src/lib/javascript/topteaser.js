/******************************** TOP-TEASER ********************************/

var TeaserImage = function() {
	TeaserImage.superclass.constructor.apply(this, arguments);
	this.timer = setInterval(this.nextTab.bind(this), 7000);
	var tabs = this.get('tabs');
	for ( var i=0; i<tabs.length; i++ ) {
		if ( i==0 ) {
			tabs[i].set('dataLoaded', true);
		}
		tabs[i].set('cacheData', true);
		tabs[i].addListener(tabs[i].get('activationEvent'), this.handleTabClicked, null, this);
	}
}



//Extend Class
YAHOO.lang.extend(TeaserImage, YAHOO.widget.TabView);



//Nächstes Tab wählen
TeaserImage.prototype.nextTab = function(newTab, oldTab) {
	var next = (this.get('activeIndex')+1)%this.get('tabs').length;
	this.selectTab(next);
}



//Tab wurde geklicks
TeaserImage.prototype.handleTabClicked = function(newTab, oldTab) {
	clearInterval(this.timer);
}



//Transition
TeaserImage.prototype.contentTransition = function(newTab, oldTab) {
	
	//Alte Animation stoppen
	if ( oldTab.anim && oldTab.anim.isAnimated() ) {
		oldTab.anim.stop(true);
	}
	if ( newTab.anim && newTab.anim.isAnimated() ) {
		newTab.anim.stop(true);
	}
	
	//Neues Tab einblenden
	newTab.set('contentVisible', true);
	yDom.setStyle(newTab.get('contentEl'), 'opacity', 0);
	yDom.setStyle(newTab.get('contentEl'), 'zIndex', 2);
	newTab.anim = newTab.anim || new YAHOO.util.Anim(newTab.get('contentEl'), { opacity: { to: 1 } }, 0.7, YAHOO.util.Easing.easeOut);
	
	//Altes Tab nach hinten
	yDom.setStyle(oldTab.get('contentEl'), 'zIndex', 1);
	
	//Listener
	var hideOldContent = function() {
		oldTab.set('contentVisible', false);
	};
	newTab.anim.onComplete.subscribe(hideOldContent, this, true);
	
	//Anim starten
	newTab.anim.animate();
}