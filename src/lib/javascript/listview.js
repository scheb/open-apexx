////////////////////////////////////////////////////////////////////// TREE VIEW

//Konstruktor
var ListView = function(treeContainer) {
	this.nodeIndex = {};
	this.listOrder = new Array();
	this.onNodeMoved = new YAHOO.util.CustomEvent('ListViewNodeMoved');
	
	//Liste initialisieren
	var tbody = treeContainer.getElementsByTagName('tbody')[0];
	var trs = tbody.getElementsByTagName('tr');
	for ( var i=0; i<trs.length; i++ ) {
		var tr = trs[i];
		
		//DnD intitialisieren
		var dnd = new DragableListNode(tr, this);
		dnd.nodeMoved.subscribe(this.handleNodeMoved, dnd, this);
		this.listOrder.push(dnd);
		this.nodeIndex[tr.id] = dnd;
	}
}



//Node anhand der ID auslesen
ListView.prototype.getNodeById = function(id) {
	if ( YAHOO.lang.isObject(this.nodeIndex[id]) ) {
		return this.nodeIndex[id];
	}
	return null;
}



//Ist der Knoten direkter Vorgänger dieses Knotens?
ListView.prototype.isAntecessor = function(node, targetNode) {
	return this.isSuccessor(targetNode, node);
}



//Ist der Knoten direkter Nachfolger dieses Knotens?
ListView.prototype.isSuccessor = function(node, targetNode) {
	var selfIndex = this.listOrder.indexOf(node);
	if ( selfIndex<this.listOrder.length-1 && this.listOrder[selfIndex+1]==targetNode ) {
		return true;
	}
	else {
		return false;
	}
}



//Knoten wurde verschoben
ListView.prototype.handleNodeMoved = function(type, args, node) {
	var action = args[0];
	var targetNode = args[1];
	
	//Knoten umhängen
	if ( node!=targetNode ) {
		if ( action==DragableListNode.BEFORE ) {
			this.insertNodeBefore(node, targetNode);
			this.onNodeMoved.fire({
				nodeId: node.nodeId,
				beforeId: targetNode.nodeId,
				afterId: null
			});
		}
		else if ( action==DragableListNode.AFTER ) {
			this.insertNodeAfter(node, targetNode);
			this.onNodeMoved.fire({
				nodeId: node.nodeId,
				beforeId: null,
				afterId: targetNode.nodeId
			});
		}
	}
}



//Knoten vor einen Knoten verschieben
ListView.prototype.insertNodeBefore = function(node, targetNode) {
	
	//HTML aktualisieren
	node.htmlElement.parentNode.removeChild(node.htmlElement);
	yDom.insertBefore(node.htmlElement, targetNode.htmlElement);
	
	//Liste aktualisieren
	var currentIndex = this.listOrder.indexOf(node);
	this.listOrder.splice(currentIndex, 1);
	var targetIndex = this.listOrder.indexOf(targetNode);
	this.listOrder.splice(targetIndex, 0, node);
}



//Knoten nach einen Knoten verschieben
ListView.prototype.insertNodeAfter = function(node, targetNode) {
	
	//HTML aktualisieren
	node.htmlElement.parentNode.removeChild(node.htmlElement);
	yDom.insertAfter(node.htmlElement, targetNode.htmlElement);
	
	//Liste aktualisieren
	var currentIndex = this.listOrder.indexOf(node);
	this.listOrder.splice(currentIndex, 1);
	var targetIndex = this.listOrder.indexOf(targetNode);
	this.listOrder.splice(targetIndex+1, 0, node);
}



////////////////////////////////////////////////////////////////////// DRAGDROP ZEILEN

//Konstruktor
var DragableListNode = function(htmlElement, listview) {
	DragableListNode.superclass.constructor.call(this, htmlElement);
	this.listview = listview;
	this.nodeId = this.getId(htmlElement);
	this.htmlElement = htmlElement;
	this.nodeMoved = new YAHOO.util.CustomEvent('nodeMoveEvent');
};



//Extend
YAHOO.extend(DragableListNode, YAHOO.util.DDProxy, {
	
	TYPE: 'DDListView',
	isTarget: true,
	
	
	
	//Constraints neu setzen, sofern das noch nicht passiert ist
	onMouseDown: function() {
		if ( !this.constraintSet ) {
			this.resetConstraints();
			this.setXConstraint(0, 0);
			this.constraintSet = true;
		}
	},
	
	
	
	//Proxy-Skin
	startDrag: function() {
		yDom.setStyle(this.getDragEl(), 'border', 'none');
		yDom.setStyle(this.getDragEl(), 'background', 'black');
		yDom.setStyle(this.getDragEl(), 'opacity', 0.1);
	},
	
	
	
	//Drag Over, Marker zeigen
	onDragOver: function(e, id) {
		var oDD = YAHOO.util.DDM.getDDById(id);
		var target = oDD.getEl();
		var targetNode = this.listview.getNodeById(target.id);
		this.getValidState(e, targetNode);
	},
	
	
	
	//Drag Out, Marker verstecken
	onDragOut: function(e, id) {
		this.hideMarker();
	},
	
	
	
	//Drop
	onDragDrop: function(e, id) {
		var oDD = YAHOO.util.DDM.getDDById(id);
		var target = oDD.getEl();
		var targetNode = this.listview.getNodeById(target.id);
		var dropInfo = this.getValidState(e, targetNode);
		if ( dropInfo ) {
			this.nodeMoved.fire(dropInfo.action, dropInfo.target);
		}
	},
	
	
	
	//Marker am Ende immer verstecken
	endDrag: function(e, id) {
		this.hideMarker();
	}
	
});



//Ist der Constraint bereits gesetzt?
DragableListNode.prototype.contraintSet = false;



//Marker-Zustände
DragableListNode.BEFORE = 'before';
DragableListNode.AFTER = 'after';



//Marker erzeugen
DragableListNode.marker = document.createElement('div');
DragableListNode.marker.className = 'listviewmarker';
document.body.appendChild(DragableListNode.marker);



//ID auslesen
DragableListNode.prototype.getId = function(htmlElement) {
	var id = htmlElement.id.substr(htmlElement.id.indexOf(':')+1);
	return id;
}



//Marker setzen
DragableListNode.prototype.setMarker = function(stateInfo) {
	if ( !stateInfo ) {
		this.hideMarker();
	}
	else {
		DragableListNode.marker.style.display = '';
		DragableListNode.marker.style.left = stateInfo.x+'px';
		DragableListNode.marker.style.top = stateInfo.y+'px';
		DragableListNode.marker.style.width = stateInfo.width+'px';
	}
}



//Marker ausblenden
DragableListNode.prototype.hideMarker = function() {
	DragableListNode.marker.style.display = 'none';
}



//Marker für ein bestimmtes Target anzeigen
DragableListNode.prototype.getValidState = function(e, targetNode) {
	
	//Drop auf Element selbst oder Kindknoten nicht möglich
	if ( this.isInvalidTarget(targetNode) ) {
		this.hideMarker();
		return null;
	}
	
	//Drop-State
	var stateInfo = this.getDropState(targetNode, yEvent.getPageY(e));
	
	//Einfügen vor dem Knoten selbst
	if ( stateInfo && stateInfo.state==DragableListNode.BEFORE && this.listview.isSuccessor(this, targetNode) ) {
		this.hideMarker();
		return null;
	}
	
	//Einfügen nach dem Knoten selbst
	else if ( stateInfo && stateInfo.state==DragableListNode.AFTER && this.listview.isAntecessor(this, targetNode) ) {
		this.hideMarker();
		return null;
	}
	
	//Marker setzen
	this.setMarker(stateInfo);
	
	//Infos zurückgeben
	return {
		action: stateInfo.state,
		target: targetNode
	};
}



//Prüfen, ob der Knoten ein mögliches Ziel ist
DragableListNode.prototype.isInvalidTarget = function(targetNode) {
	return !targetNode || targetNode==this.node;
}



//Drop-Zustand berechnen
DragableListNode.prototype.getDropState = function(targetNode, ref) {
	var target = targetNode.htmlElement;
	var targetRegion = yDom.getRegion(target);
	var section = Math.floor(targetRegion.height/3);
	
	//Oben
	if ( /*ref>=targetRegion.top &&*/ ref<=targetRegion.top+section ) {
		return {
				state: DragableListNode.BEFORE,
				x: targetRegion.left,
				y: targetRegion.top-2,
				width: targetRegion.width
		};
	}
	
	//Unten
	else if ( ref<=targetRegion.bottom /*&& ref>=targetRegion.bottom-section*/ ) {
		return {
			state: DragableListNode.AFTER,
			x: targetRegion.left,
			y: targetRegion.bottom-2,
			width: targetRegion.width
		};
	}
	
	return null;
}