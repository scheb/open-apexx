////////////////////////////////////////////////////////////////////// DND NAVI

var SortableNavi = function(navigation) {
	this.navigation = navigation;
	new YAHOO.util.DDTarget(navigation.id);
	var lis = yDom.getElementsByClassName('navi_header', 'li', navigation);
	for ( var i=0; i<lis.length; i++ ) {
		var item = new SortableNaviItem(lis[i].id);
		item.onOrderChange.subscribe(this.onOrderChange, this, true);
	}
}



//Anordnung der Navi wurde ge�ndert
SortableNavi.prototype.onOrderChange = function(type, args) {
	var callback = {
		success: function() { },
		failure: function() { }
	};

	var postData = 'action=main.savenaviorder';
	var lis = yDom.getElementsByClassName('navi_header', 'li', this.navigation);
	for ( var i=0; i<lis.length; i++ ) {
		var id = lis[i].id.substr(3);
		postData += '&order[]='+id;
	}

	YAHOO.util.Connect.asyncRequest('POST', 'ajax.php', callback, postData);
}



////////////////////////////////////////////////////////////////////// DND NAVI ITEM

var SortableNaviItem = function(id, sGroup, config) {
	SortableNaviItem.superclass.constructor.call(this, id, sGroup, config);
	this.id = id;
	this.goingUp = false;
	this.setXConstraint(0, 0);
	var el = this.getEl();
	var contentEl = el.getElementsByTagName('ul')[0];
	var link = el.getElementsByTagName('a')[0];
	link.onclick = this.toggle.bind(this);

	//Handle setzen
	this.setHandleElId(el.id+'_move');

	//Proxy-Style
	var proxy = this.getDragEl();
	yDom.setStyle(proxy, "opacity", 0.5);
	yDom.setStyle(proxy, "border", "none");

	//Collapseable erzeugen
	var isOpen = yDom.hasClass(yDom.get(id), 'expanded');
	trigger = new CollapseTriggerClass(id, 'expanded', 'collapsed', null);
	this.collapseable = new CollapseableContent(contentEl, trigger, isOpen ? CollapseableContent.OPEN : CollapseableContent.CLOSED, 70);

	//Event erzeugen
	this.onOrderChange = new YAHOO.util.CustomEvent('naviOrderChange');
};



//Extend
YAHOO.extend(SortableNaviItem, YAHOO.util.DDProxy, {

	startDrag: function(x, y) {
		var dragEl = this.getDragEl();
		var clickEl = this.getEl();
		yDom.setStyle(clickEl, 'visibility', 'hidden');
		dragEl.innerHTML = clickEl.innerHTML.replace(/onclick="[^"]"/, '');
		dragEl.className = clickEl.className;
		yDom.setStyle(dragEl, "border", "none");
	},


	endDrag: function(e) {
		var srcEl = this.getEl();
		var proxy = this.getDragEl();

		yDom.setStyle(proxy, "visibility", "hidden");
		yDom.setStyle(srcEl, "visibility", "");

		this.onOrderChange.fire();
	},


	onDragDrop: function(e, id) {

		// If there is one drop interaction, the li was dropped either on the list,
		// or it was dropped on the current location of the source element.
		if ( YAHOO.util.DDM.interactionInfo.drop.length===1 ) {

			// The position of the cursor at the time of the drop (YAHOO.util.Point)
			var pt = YAHOO.util.DDM.interactionInfo.point;

			// The region occupied by the source element at the time of the drop
			var region = YAHOO.util.DDM.interactionInfo.sourceRegion;

			// Check to see if we are over the source element's location.  We will
			// append to the bottom of the list once we are sure it was a drop in
			// the negative space (the area of the list without any list items)
			if ( !region.intersect(pt) ) {
				var destEl = yDom.get(id);
				var destDD = YAHOO.util.DDM.getDDById(id);
				destEl.appendChild(this.getEl());
				destDD.isEmpty = false;
				YAHOO.util.DDM.refreshCache();
			}

		}
	},


	onDrag: function(e) {

		// Keep track of the direction of the drag for use during onDragOver
		var y = yEvent.getPageY(e);

		if (y < this.lastY) {
			this.goingUp = true;
		}
		else if (y > this.lastY) {
			this.goingUp = false;
		}

		this.lastY = y;
	},


	onDragOver: function(e, id) {
		var srcEl = this.getEl();
		var destEl = yDom.get(id);
		var parent = srcEl.parentNode;

		// We are only concerned with list items, we ignore the dragover
		// notifications for the list.
		if ( destEl.nodeName.toLowerCase()=='li' ) {
			if ( this.goingUp ) {
				parent.insertBefore(srcEl, destEl); // insert above
			}
			else {
				parent.insertBefore(srcEl, destEl.nextSibling); // insert below
			}

			YAHOO.util.DDM.refreshCache();
		}
	}
});


//Navigationspunkt toggle
SortableNaviItem.prototype.toggle = function() {
	this.collapseable.toggle();
	this.saveMenuState(this.id, this.collapseable.isOpen());
}



//Navigationspunkt �ffnen
SortableNaviItem.prototype.open = function() {
	this.collapseable.open();
}



//Navigationspunkt schlie�en
SortableNaviItem.prototype.close = function() {
	this.collapseable.close();
}



//Aufklapp-Status speichern
SortableNaviItem.prototype.saveMenuState = function(theid, open) {
	if ( open ) {
		document.cookie="apx_open["+theid+"]=1; path=/; expires=Thu, 1 Jan 2020 00:00:00 GMT;";
	}
	else {
		document.cookie="apx_open["+theid+"]=0; path=/; expires=Thu, 1 Jan 1970 00:00:00 GMT;";
	}
}
