////////////////////////////////////////////////////////////////////// KALENDER-MENÜ

var CalendarSelection = function (fields, identifier) {
	this.dayInput = fields['day'];
	this.monthInput = fields['month'];
	this.yearInput = fields['year'];
	this.identifier = identifier;
	this.calendar = null;
	this.visible = false;
	
	//Button erzeugen
	this.button = document.createElement('img');
	this.button.src = 'design/calendar.gif';
	this.button.title = 'Kalender anzeigen';
	yDom.setStyle(this.button, 'cursor', 'pointer');
	yDom.insertAfter(this.button, this.yearInput);
	yDom.insertAfter(document.createTextNode(' '), this.yearInput);
	
	//Subscribe Click-Event
	yEvent.addListener(this.button, 'click', this.handleClick, null, this);
	
	//Menü-Container erzeugen
	this.container = new YAHOO.widget.Overlay('calendarmenu_'+this.identifier, { context: [this.button, 'bl', 'tl', ['beforeShow', 'windowResize']], visible: false, zIndex:999 } );
	this.container.setBody(' ');
	this.container.body.id = 'calendarcontainer_'+this.identifier;
	this.container.render(document.body);
	this.container.cfg.setProperty('preventcontextoverlap', true);
	this.container.cfg.setProperty('constraintoviewport', true);
};



//Klick auf Button
CalendarSelection.prototype.handleClick = function () {
	if ( this.visible ) {
		this.hideCalendar();
	}
	else {
		if ( this.calendar==null ) {
			this.createCalendar();
		}
		else {
			this.updateCalendar();
			this.calendar.render();
		}
		this.showCalendar();
	}
}



//Kalender erzeugen
CalendarSelection.prototype.createCalendar = function () {
	
	//Kalender erzeugen (Deutsche Lokalisierung)
	this.calendar = new YAHOO.widget.Calendar('buttoncalendar_'+this.identifier, this.container.body.id, {
		LOCALE_WEEKDAYS: "short",
		START_WEEKDAY: 1,
		MULTI_SELECT: false
	});
	
	this.calendar.cfg.setProperty("MULTI_SELECT", false);
	this.calendar.cfg.setProperty("DATE_FIELD_DELIMITER", ".");
	
	this.calendar.cfg.setProperty("MDY_DAY_POSITION", 1);
	this.calendar.cfg.setProperty("MDY_MONTH_POSITION", 2);
	this.calendar.cfg.setProperty("MDY_YEAR_POSITION", 3);
	
	this.calendar.cfg.setProperty("MD_DAY_POSITION", 1);
	this.calendar.cfg.setProperty("MD_MONTH_POSITION", 2);
	
	this.calendar.cfg.setProperty("MONTHS_SHORT",   ["Jan", "Feb", "M\u00E4r", "Apr", "Mai", "Jun", "Jul", "Aug", "Sep", "Okt", "Nov", "Dez"]);
	this.calendar.cfg.setProperty("MONTHS_LONG",    ["Januar", "Februar", "M\u00E4rz", "April", "Mai", "Juni", "Juli", "August", "September", "Oktober", "November", "Dezember"]);
	this.calendar.cfg.setProperty("WEEKDAYS_1CHAR", ["S", "M", "D", "M", "D", "F", "S"]);
	this.calendar.cfg.setProperty("WEEKDAYS_SHORT", ["So", "Mo", "Di", "Mi", "Do", "Fr", "Sa"]);
	this.calendar.cfg.setProperty("WEEKDAYS_MEDIUM",["Son", "Mon", "Die", "Mit", "Don", "Fre", "Sam"]);
	this.calendar.cfg.setProperty("WEEKDAYS_LONG",  ["Sonntag", "Montag", "Dienstag", "Mittwoch", "Donnerstag", "Freitag", "Samstag"]);
	
	this.updateCalendar();
	this.calendar.render();
	
	//Callback für Datum auswählen
	this.calendar.selectEvent.subscribe(this.selectDay.bind(this));
	
	//Focus setzen
	this.container.subscribe('show', this.focusDay.bind(this));
	this.calendar.renderEvent.subscribe(this.focusDay.bind(this), this.calendar, true);
	this.focusDay(); //Init
}



//Ausgewähltes Datum ändern
CalendarSelection.prototype.updateCalendar = function() {
	var day = parseInt(this.dayInput.options[this.dayInput.selectedIndex].value);
	var month = parseInt(this.monthInput.options[this.monthInput.selectedIndex].value);
	var year = parseInt(this.yearInput.value);
	if ( !isNaN(day) && !isNaN(month) && !isNaN(year) ) {
		this.calendar.cfg.setProperty('selected', day+'.'+month+'.'+year);
		this.calendar.cfg.setProperty('PAGEDATE', month+'.'+year);
	}
}



//Tag auswählen
CalendarSelection.prototype.selectDay = function(type, args) {
	if (args) {
		var date = args[0][0];
		for ( var i = 0; i<this.dayInput.options.length; i++ ) {
			if ( this.dayInput.options[i].value==date[2] ) {
				this.dayInput.selectedIndex = i;
				break;
			}
		}
		for ( var i = 0; i<this.monthInput.options.length; i++ ) {
			if ( this.monthInput.options[i].value==date[1] ) {
				this.monthInput.selectedIndex = i;
				break;
			}
		}
		if ( this.yearInput.type=='text' ) {
			this.yearInput.value = date[0];
		}
		else {
			for ( var i = 0; i<this.yearInput.options.length; i++ ) {
				if ( this.yearInput.options[i].value==date[0] ) {
					this.yearInput.selectedIndex = i;
					break;
				}
			}
		}
		
	}
	this.hideCalendar();
}



//Focus auf aktuellen Tag setzen
CalendarSelection.prototype.focusDay = function() {
	var calendarBody  = yDom.get('buttoncalendar_'+this.identifier).tBodies[0];
	var elements = calendarBody.getElementsByTagName('a');
	var anchor;
	
	if ( elements.length>0 ) {
		yDom.batch(elements, function(element) {
			if ( yDom.hasClass(element.parentNode, 'today') ) {
				anchor = element;
			}
		});
		
		if ( !anchor ) {
			anchor = elements[0];
		}
		
		YAHOO.lang.later(0, anchor, function() {
			try {
				anchor.focus();
			}
			catch(e) {}
		});
	}
}



//Tag auswählen
CalendarSelection.prototype.closeOnESC = function(evt) {
	if ( yEvent.getCharCode(evt)===27 ) {
		this.hideCalendar();
		this.button.focus();
	}
}



//Klick auf Dokument => außerhalb? => schließen
CalendarSelection.prototype.handleDocumentMouseDown = function(evt) {
	var target = yEvent.getTarget(evt);
	if (
		target!=this.button && 
		!yDom.isAncestor(this.button, target) && 
		target!=this.container.element && 
		!yDom.isAncestor(this.container.element, target)
	) {
		this.hideCalendar();
	}
}



//Kalender anzeigen
CalendarSelection.prototype.showCalendar = function() {
	this.container.show();
	this.visible = true;
	
	//Close-Events abonnieren
	yEvent.on(this.container.element, 'keydown', this.closeOnESC, null, this);
	yEvent.on(document, 'mousedown', this.handleDocumentMouseDown, null, this);
}



//Kalender schließen
CalendarSelection.prototype.hideCalendar = function() {
	this.container.hide();
	this.visible = false;
	
	//Listener entfernen
	yEvent.removeListener(this.container.element, 'keydown', this.closeOnESC);
	yEvent.removeListener(document, 'mousedown', this.handleDocumentMouseDown);    
}
