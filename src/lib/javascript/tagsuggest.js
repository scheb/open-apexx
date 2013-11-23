////////////////////////////////////////////////////////////////////// SUGGEST TAGS

//Konstruktor
var TagSuggest = function(textField, container, delay) {
	
	//Datenquelle
	this.oACDS = new YAHOO.widget.DS_XHR('ajax.php', ["\n", "\t"]);
	this.oACDS.responseType = YAHOO.widget.DS_XHR.TYPE_FLAT;
	this.oACDS.maxCacheEntries = 5;
	this.oACDS.queryMatchSubset = true;
	
	//Init
	this.autocomplete = new YAHOO.widget.AutoComplete(textField, container, this.oACDS);
	this.autocomplete.delimChar = ',';
	this.autocomplete.queryDelay = delay/1000;
	this.autocomplete.formatResult = this.fnCustomFormatter;
	this.autocomplete.generateRequest = function(query) {
		return '?action=main.suggesttag&query='+query;
	};
}



//Formater-Function
TagSuggest.prototype.fnCustomFormatter = function(oResultItem, sQuery) {
	return oResultItem[0];
};