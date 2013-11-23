////////////////////////////////////////////////////////////////////// TREE VIEW

//Konstruktor
var TreeView = function(treeContainer, dragable) {
	this.nodeIndex = {};
	this.dnd = new Array();
	this.onNodeMoved = new YAHOO.util.CustomEvent('treeViewNodeMoved');
	this.onNodeToggle = new YAHOO.util.CustomEvent('treeViewNodeToggle');
	
	//Root-Knoten erzeugen
	var tbody = treeContainer.getElementsByTagName('tbody')[0];
	this.rootNode = new RootNode(tbody);
	
	//Baum initialisieren
	var currentParent = new Array();
	currentParent.push(this.rootNode);
	var lastLevel = 0;
	var trs = tbody.getElementsByTagName('tr');
	for ( var i=0; i<trs.length; i++ ) {
		var tr = trs[i];
		var level = this.getNodeLevel(tr);
		if ( level===false ) continue;
		
		//Parents bis zum aktuellen Level entfernen
		if ( level<=lastLevel ) {
			while ( currentParent.length>level+1 ) {
				currentParent.pop();
			}
		}
		
		//Knoten erzeugen
		var parent = currentParent[currentParent.length-1];
		var node = parent.createChild(tr);
		node.onNodeToggle.subscribe(this.handleNodeToggle, node, this);
		this.nodeIndex[tr.id] = node;
		
		//DnD intitialisieren
		if ( dragable ) {
			var dnd = new DragableNode(node, this);
			dnd.nodeMoved.subscribe(this.handleNodeMoved, node, this);
			this.dnd.push(dnd);
		}
		
		//Aktuellen Knoten als nächsten Parent-Knoten merken
		currentParent.push(node);
		lastLevel = level;
	}
	
	//Linien zeichnen
	this.rootNode.updateView([]);
}



//Level auslesen
TreeView.prototype.getNodeLevel = function(tr) {
	var classes = tr.className.split(' ');
	for ( var i=0; i<classes.length; i++ ) {
		if ( classes[i].match(/^l[0-9]+$/) ) {
			var level = parseInt(classes[i].substr(1));
			return level;
		}
	}
	return false;
}



//Knoten öffnen
TreeView.prototype.openNode = function(id) {
	var node = this.getNodeById('node:'+id);
	if ( node ) {
		node.openInit();
	}
}



//Node anhand der ID auslesen
TreeView.prototype.getNodeById = function(id) {
	if ( YAHOO.lang.isObject(this.nodeIndex[id]) ) {
		return this.nodeIndex[id];
	}
	return null;
}



//Knoten wurde verschoben
TreeView.prototype.handleNodeMoved = function(type, args, node) {
	var action = args[0];
	var targetNode = args[1];
	
	//Knoten umhängen
	if ( action==DragableNode.INSERT_BEFORE ) {
		if ( node!=targetNode.parentNode && !node.hasChild(targetNode.parentNode) ) {
			node.parentNode.removeChild(node);
			targetNode.parentNode.addChildBefore(node, targetNode);
			this.onNodeMoved.fire({
				nodeId: node.id,
				newParentId: targetNode.parentNode.id,
				beforeId: targetNode.id,
				afterId: null
			});
		}
	}
	else if ( action==DragableNode.INSERT_IN ) {
		if ( node!=targetNode && !node.hasChild(targetNode) ) {
			node.parentNode.removeChild(node);
			targetNode.addChildBefore(node, null);
			targetNode.open();
			this.onNodeMoved.fire({
				nodeId: node.id,
				newParentId: targetNode.id,
				beforeId: null,
				afterId: null
			});
		}
	}
	else if ( action==DragableNode.INSERT_AFTER ) {
		if ( node!=targetNode.parentNode && !node.hasChild(targetNode.parentNode) ) {
			node.parentNode.removeChild(node);
			targetNode.parentNode.addChildAfter(node, targetNode);
			this.onNodeMoved.fire({
				nodeId: node.id,
				newParentId: targetNode.parentNode.id,
				beforeId: null,
				afterId: targetNode.id
			});
		}
	}
	
	//Ansicht aktualisieren
	this.rootNode.updateView([]);
}



//Knoten wurde getoggled
TreeView.prototype.handleNodeToggle = function(type, args, node) {
	this.onNodeToggle.fire(node.id, args[0]);
}



////////////////////////////////////////////////////////////////////// TREE NODE

//Konstruktor
var TreeNode = function(htmlElement, parentNode) {
	this.id = this.getId(htmlElement);
	this.htmlContainer = htmlElement.parentNode;
	this.htmlElement = htmlElement;
	this.parentNode = parentNode;
	this.children = new Array();
	this.onNodeToggle = new YAHOO.util.CustomEvent('nodeToggle');
	
	//Container für grafische Linien erzeugen
	var tds = yDom.getElementsByClassName('title', 'td', this.htmlElement);
	var titleTd = tds.length ? tds.shift() : this.htmlElement.firstChild;
	this.linesContainer = document.createElement('span');
	this.linesContainer.className = 'treelines';
	titleTd.insertBefore(this.linesContainer, titleTd.firstChild);
}



//Korrespondirendes HTML-Element
TreeNode.prototype.htmlElement = null;

//Eltern-Knoten
TreeNode.prototype.parentNode = null;

//Kindknoten des Knoten
TreeNode.prototype.children = null;

//Zustand des Knotens (standardmäßig eingeklappt)
TreeNode.prototype.collapsed = true;

//Toggle-Button
TreeNode.prototype.toggleButton = null;



//ID auslesen
TreeNode.prototype.getId = function(htmlElement) {
	var id = htmlElement.id.substr(htmlElement.id.indexOf(':')+1);
	return id;
}



//Kindknoten erzeugen
TreeNode.prototype.createChild = function(htmlElement) {
	var childNode = new TreeNode(htmlElement, this);
	this.children.push(childNode);
	return childNode;
}



//Kindknoten vorhanden? (alle Ebenen)
TreeNode.prototype.hasChild = function(node) {
	if ( this.children.indexOf(node)!=-1 ) {
		return true;
	}
	for ( var i=0; i<this.children.length; i++ ) {
		if ( this.children[i].hasChild(node) ) {
			return true;
		}
	}
	return false;
}



//Hat der Knoten Kinder?
TreeNode.prototype.hasChildren = function() {
	return this.children.length ? true : false;
}



//Ist der Knoten direkter Vorgänger dieses Knotens?
TreeNode.prototype.isAntecessor = function(node) {
	return node.isSuccessor(this);
}



//Ist der Knoten direkter Nachfolger dieses Knotens?
TreeNode.prototype.isSuccessor = function(node) {
	if ( !this.parentNode ) {
		return false;
	}
	else {
		var selfIndex = this.parentNode.children.indexOf(this);
		if ( selfIndex<this.parentNode.children.length-1 && this.parentNode.children[selfIndex+1]==node ) {
			return true;
		}
		else {
			return false;
		}
	}
}



//Kind-Knoten entfernen
TreeNode.prototype.removeChild = function(node) {
	this.htmlRemoveChild(node);
	this.children.remove(node);
	node.parentNode = null;
}



//Kind-Knoten nach einem bestimmten Knoten hinzufügen
TreeNode.prototype.addChildBefore = function(node, beforeNode) {
	
	//Finde den Knoten im HTML, vor dem der Knoten eingefügt werden soll
	if ( beforeNode==null ) {
		var referenceNode = this;
		while ( referenceNode.hasChildren() ) {
			referenceNode = referenceNode.children[referenceNode.children.length-1];
		}
		var beforeHTMLNode = referenceNode.htmlElement.nextSibling;
	}
	else {
		var beforeHTMLNode = beforeNode.htmlElement;
	}
	
	//Knoten in die Datenstruktur einfügen
	if ( beforeNode==null ) {
		this.children.push(node);
	}
	else {
		var refIndex = this.children.indexOf(beforeNode);
		this.children.splice(refIndex, 0, node);
	}
	node.parentNode = this;
	
	//Knoten im HTML einfügen
	this.htmlInsertChildBefore(node, beforeHTMLNode);
}



//Kind-Knoten vor einem bestimmten Knoten hinzufügen
TreeNode.prototype.addChildAfter = function(node, afterNode) {
	if ( afterNode==null ) {
		if ( this.hasChildren() ) {
			var refNode = this.children[0];
			this.addChildBefore(node, refNode);
		}
		else {
			this.addChildBefore(node, null);
		} 
	}
	else {
		var refIndex = this.children.indexOf(afterNode);
		if ( refIndex==this.children.length-1 ) {
			this.addChildBefore(node, null);
		}
		else {
			var refNode = this.children[refIndex+1];
			this.addChildBefore(node, refNode);
		}
	}
}



//Linien aktualisieren
TreeNode.prototype.updateView = function(levelInfo) {
	
	//Eigener View
	this.drawLines(levelInfo);
	this.updateFlags();
	
	//View der Unterknoten
	for ( var i=0; i<this.children.length; i++ ) {
		var hasSuccessor = i!=this.children.length-1;
		var newInfo = levelInfo.clone();
		newInfo.push(hasSuccessor);
		this.children[i].updateView(newInfo);
	}
}



//HTML-Knoten des gesamten Subtree entfernen
TreeNode.prototype.htmlRemoveChild = function(node) {
	for ( var i=0; i<node.children.length; i++ ) {
		node.htmlRemoveChild(node.children[i]);
	}
	node.htmlContainer.removeChild(node.htmlElement);
}



//HTML-Knoten des gesamten Subtree einfügen
TreeNode.prototype.htmlInsertChildBefore = function(node, beforeHTMLNode) {
	this.htmlContainer.insertBefore(node.htmlElement, beforeHTMLNode);
	for ( var i=0; i<node.children.length; i++ ) {
		node.htmlInsertChildBefore(node.children[i], beforeHTMLNode);
	}
}



//Linien zeichnen
TreeNode.prototype.drawLines = function(levelInfo) {
	var self = this;
	this.linesContainer.innerHTML = ''; //Alte Linien löschen
	
	//Neue Linien erzeugen
	for ( var level=0; level<levelInfo.length; level++ ) {
		var hasSuccessor = levelInfo[level];
		var span = document.createElement('span');
		
		//Level des Knotens selbst
		if ( level==levelInfo.length-1 ) {
			if ( hasSuccessor ) {
				span.className = 'node';
			}
			else {
				span.className = 'nodeend';
			}
			
			//Toggle-Button
			if ( this.hasChildren() ) {
				this.toggleButton = document.createElement('a');
				this.toggleButton.onclick = function() { self.toggle(); };
				span.appendChild(this.toggleButton);
				if ( this.collapsed ) {
					this.toggleButton.className += ' collapsed';
				}
				else {
					this.toggleButton.className += ' expanded';
				}
			}
		}
		
		//Vorgänger-Level
		else {
			if ( hasSuccessor ) {
				span.className = 'line';
			}
			else {
				span.className = 'empty';
			}
		}
		
		this.linesContainer.appendChild(span);
	}
}



//Flag-Klasse für Kinder aktualisieren
TreeNode.prototype.updateFlags = function() {
	
	//haschildren
	var flagSet = yDom.hasClass(this.htmlElement, 'haschildren');
	if ( flagSet && !this.hasChildren() ) {
		yDom.removeClass(this.htmlElement, 'haschildren');
	}
	else if ( !flagSet && this.hasChildren() ) {
		yDom.addClass(this.htmlElement, 'haschildren');
	}
}



//Level des Knotens auslesen
TreeNode.prototype.getLevel = function() {
	return this.parentNode.getLevel()+1;
}



//Knoten sichtbar/unsichtbar machen
TreeNode.prototype.displayNode = function(state) {
	if ( state ) {
		yDom.removeClass(this.htmlElement, 'hidden');
	}
	else {
		yDom.addClass(this.htmlElement, 'hidden');
	}
}



//Unterknoten sichtbar/unsichtbar machen
TreeNode.prototype.displaySubtree = function(state) {
	
	//Knoten selbst
	this.displayNode(state);
	
	//Unterknoten nur sichtbar machen, wenn dieser Knoten aufgeklappt ist
	if ( state ) {
		if ( !this.collapsed ) {
			for ( var i=0; i<this.children.length; i++ ) {
				this.children[i].displaySubtree(state);
			}
		}
	}
	
	//Alle Unterknoten verstecken
	else {
		for ( var i=0; i<this.children.length; i++ ) {
			this.children[i].displayNode(state);
			this.children[i].displaySubtree(state);
		}
	}
}



//Subtree toggle
TreeNode.prototype.toggle = function() {
	if ( this.collapsed ) {
		this.open();
	}
	else {
		this.close();
	}
}



//Subtree aufklappen (bei Initialisierung)
TreeNode.prototype.openInit = function() {
	if ( !this.isParentCollapsed() ) {
		for ( var i=0; i<this.children.length; i++ ) {
			this.children[i].displaySubtree(true);
		}
	}
	this.collapsed = false;
	yDom.replaceClass(this.toggleButton, 'collapsed', 'expanded');
}



//Subtree aufklappen
TreeNode.prototype.open = function() {
	for ( var i=0; i<this.children.length; i++ ) {
		this.children[i].displaySubtree(true);
	}
	this.collapsed = false;
	yDom.replaceClass(this.toggleButton, 'collapsed', 'expanded');
	this.onNodeToggle.fire(true);
}



//Subtree einklappen
TreeNode.prototype.close = function() {
	for ( var i=0; i<this.children.length; i++ ) {
		this.children[i].displaySubtree(false);
	}
	this.collapsed = true;
	yDom.replaceClass(this.toggleButton, 'expanded', 'collapsed');
	this.onNodeToggle.fire(false);
}



//Ist der Subtree eingeklappt?
TreeNode.prototype.isCollapsed = function() {
	return this.collapsed;
}



//Ist einer der Elternknoten eingeklappt?
TreeNode.prototype.isParentCollapsed = function() {
	var parent = this.parentNode;
	while ( parent!=null ) {
		if ( parent.isCollapsed() ) {
			return true;
		}
		parent = parent.parentNode;
	}
	return false;
}



//Debug-Ansicht auf die Konsole schreiben
TreeNode.prototype.debug = function(level) {
	if ( YAHOO.lang.isUndefined(level) ) {
		level = 0;
	}
	
	var intent = '';
	for ( var i=0; i<level; i++ ) {
		intent += '  ';
	} 
	
	console.log(intent, this.htmlElement);
	for ( var i=0; i<this.children.length; i++ ) {
		this.children[i].debug(level+1);
	}
}



////////////////////////////////////////////////////////////////////// ROOT NODE

//Konstruktor
var RootNode = function(htmlContainer) {
	this.id = 0;
	this.htmlContainer = htmlContainer;
	this.htmlElement = null;
	this.children = new Array();
	this.collapsed = false; //Root ist immer offen
}

//Ableiten
YAHOO.lang.extend(RootNode, TreeNode);



//Linien aktualisieren
RootNode.prototype.drawLines = function(levelInfo) {
	//Nichts zu zeichnen
}



//Level des Knotens auslesen
RootNode.prototype.getLevel = function() {
	return -1; //Root ist -1
}



////////////////////////////////////////////////////////////////////// DRAGDROP ZEILEN

//Konstruktor
var DragableNode = function(node, treeView) {
	DragableNode.superclass.constructor.call(this, node.htmlElement);
	this.node = node;
	this.treeView = treeView;
	this.nodeMoved = new YAHOO.util.CustomEvent('nodeMoveEvent');
};



//Extend
YAHOO.extend(DragableNode, YAHOO.util.DDProxy, {
	
	TYPE: 'DDTreeView',
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
		var targetNode = this.treeView.getNodeById(target.id);
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
		var targetNode = this.treeView.getNodeById(target.id);
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
DragableNode.prototype.contraintSet = false;



//Marker-Zustände
DragableNode.INSERT_IN = 'in';
DragableNode.INSERT_BEFORE = 'before';
DragableNode.INSERT_AFTER = 'after';



//Marker erzeugen
DragableNode.marker = document.createElement('div');
DragableNode.marker.className = 'treeviewmarker';
document.body.appendChild(DragableNode.marker);



//Marker setzen
DragableNode.prototype.setMarker = function(stateInfo) {
	if ( !stateInfo ) {
		this.hideMarker();
	}
	else {
		var className = stateInfo.state==DragableNode.INSERT_IN ? 'movein' : 'movebetween';
		DragableNode.marker.className = 'treeviewmarker '+className;
		DragableNode.marker.style.left = stateInfo.x+'px';
		DragableNode.marker.style.top = stateInfo.y+'px';
		DragableNode.marker.style.width = stateInfo.width+'px';
		DragableNode.marker.style.height = stateInfo.height+'px';
	}
}



//Marker ausblenden
DragableNode.prototype.hideMarker = function() {
	DragableNode.marker.className = 'treeviewmarker';
}



//Marker für ein bestimmtes Target anzeigen
DragableNode.prototype.getValidState = function(e, targetNode) {
	
	//Drop auf Element selbst oder Kindknoten nicht möglich
	if ( this.isInvalidTarget(targetNode) ) {
		this.hideMarker();
		return null;
	}
	
	//Drop-State
	var stateInfo = this.getDropState(targetNode, yEvent.getPageY(e));
	
	//SONDERFALL: Füge nach einem Knoten ein der aufgeklappt ist
	//Eigentlich soll vor dem ersten Kindknoten eingefügt werden
	//Ändern wir also die targetNode entsprechend und rufen die Funktion erneut auf
	if ( stateInfo.state==DragableNode.INSERT_AFTER && targetNode.hasChildren() && !targetNode.isCollapsed() ) {
		var newTargetNode = targetNode.children[0];
		return this.getValidState(e, newTargetNode);
	}
	
	//Prüfen, ob der State überhaupt sinnvoll ist:
	
	//Einfügen vor dem Knoten selbst
	if ( stateInfo && stateInfo.state==DragableNode.INSERT_BEFORE && this.node.isSuccessor(targetNode) ) {
		this.hideMarker();
		return null;
	}
	
	//Einfügen nach dem Knoten selbst
	else if ( stateInfo && stateInfo.state==DragableNode.INSERT_AFTER && this.node.isAntecessor(targetNode) ) {
		this.hideMarker();
		return null;
	}
	
	//Einfügen in den Parent des Knoten
	else if ( stateInfo && stateInfo.state==DragableNode.INSERT_IN && this.node.parentNode==targetNode ) {
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
DragableNode.prototype.isInvalidTarget = function(targetNode) {
	return !targetNode || targetNode==this.node || this.node.hasChild(targetNode);
}



//Drop-Zustand berechnen
DragableNode.prototype.getDropState = function(targetNode, ref) {
	var target = targetNode.htmlElement;
	var targetRegion = yDom.getRegion(target);
	var section = Math.floor(targetRegion.height/3);
	
	//Oben
	if ( /*ref>=targetRegion.top &&*/ ref<=targetRegion.top+section ) {
		var level = targetNode.getLevel();
		return {
				state: DragableNode.INSERT_BEFORE,
				x: targetRegion.left+level*13+(level ? 5 : 0),
				y: targetRegion.top-2,
				width: targetRegion.width-level*13+(level ? 5 : 0),
				height: 0
		};
	}
	
	//Einfügen
	else if ( ref>targetRegion.top+section && ref<targetRegion.bottom-section ) {
		return {
			state: DragableNode.INSERT_IN,
			x: targetRegion.left,
			y: targetRegion.top-1,
			width: targetRegion.width,
			height: targetRegion.height-1
		};
	}
	
	//Unten
	else if ( ref<=targetRegion.bottom /*&& ref>=targetRegion.bottom-section*/ ) {
		var level = targetNode.getLevel();
		return {
			state: DragableNode.INSERT_AFTER,
			x: targetRegion.left+level*13+(level ? 5 : 0),
			y: targetRegion.bottom-2,
			width: targetRegion.width-level*13+(level ? 5 : 0),
			height: 0
		};
	}
	
	return null;
}