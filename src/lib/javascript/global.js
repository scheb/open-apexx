/***************************************************************\
|                                                               |
|                   apexx CMS & Portalsystem                    |
|                 ============================                  |
|           (c) Copyright 2005-2009, Christian Scheb            |
|                  http://www.stylemotion.de                    |
|                                                               |
|---------------------------------------------------------------|
| THIS SOFTWARE IS NOT FREE! MAKE SURE YOU OWN A VALID LICENSE! |
| DO NOT REMOVE ANY COPYRIGHTS WITHOUT PERMISSION!              |
| SOFTWARE BELONGS TO ITS AUTHORS!                              |
\***************************************************************/



/**
 * Binds a function to a specific scope in which it will be executed
 * @param obj  The scope-object
 * @return A function-wrapper that will be call in the scope of obj
 */
Function.prototype.bind = function(obj) {
	var method = this,
		temp = function() {
			return method.apply(obj, arguments);
		};
	return temp;
}



//YUI Basic
var yDom = YAHOO.util.Dom;
var yEvent = YAHOO.util.Event;



function getobject(objid) {
	return yDom.get(objid);
}


//in_array
function in_array(my_value,my_array){
	caseSensitive = in_array.arguments.length<3?0:in_array.arguments[2];
	
	for(i=0;i<my_array.length;i++){
		if((caseSensitive==0?my_array[i]:my_array[i].toUpperCase())==(caseSensitive==0?my_value:my_value.toUpperCase())){
			return true;
		}
	}
	
	return false;
}



//***************** EXTEND ARRAY ******************

//IndexOf
if( !YAHOO.lang.isFunction(Array.indexOf) ) {
	Array.prototype.indexOf = function(element) {
		for ( var i = 0; i < this.length; i++ ) {
			if( element==this[i] ) return i;
		}
		return -1;
	}
}



//Clone
if( !YAHOO.lang.isFunction(Array.clone) ) {
	Array.prototype.clone = function() {
		return this.slice.call(this, 0);
	}
}



//Contains
Array.prototype.contains = function (element) {
	for ( var i = 0; i < this.length; i++ ) {
		if ( this[i]==element ) {
			return true;
		}
	}
	return false;
};



//Remove
Array.prototype.remove = function(element){
	for ( var i = 0; i < this.length; i++ ) {
		if( element==this[i] ) this.splice(i, 1);
	}
}



//Swap
Array.prototype.swap = function(a, b) {
	var tmp = this[a];
	this[a] = this[b];
	this[b] = tmp;
}



//***************** NODE EXTEND ******************

function swapNodes(a, b) {
	var aparent = a.parentNode;
	var asibling = a.nextSibling===b ? a : a.nextSibling;
	b.parentNode.insertBefore(a, b);
	aparent.insertBefore(b, asibling);
}



//***************** TRIM FUNKTIONEN ******************

function lTrim( value ) {	
	var re = /\s*((\S+\s*)*)/;
	return value.replace(re, "$1");
}

function rTrim( value ) {
	var re = /((\s*\S+)*)\s*/;
	return value.replace(re, "$1");
}

function trim( value ) {
	return lTrim(rTrim(value));
}


//***************** EMAIL ENCRYPT ******************

function uncryptMail(s) {
	var n=0;
	var r="";
	for(var i=0; i < s.length; i++) {
		n=s.charCodeAt(i);
		if (n>=8364) {n = 128;}
		r += String.fromCharCode(n+3);
	}
	return r;
}

function linkUncryptedMail(s)	{
	location.href = uncryptMail(s);
}