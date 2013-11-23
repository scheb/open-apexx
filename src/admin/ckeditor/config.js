/*
Copyright (c) 2003-2009, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.html or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{
	//Resize disabled
	config.resize_enabled = false;
	
	//Paste
	config.forcePasteAsPlainText = true;
	config.pasteFromWordIgnoreFontFace = true;
	config.pasteFromWordKeepsStructure = false;
	config.pasteFromWordRemoveStyle = false;
	
	//Sonderzeichen
	config.entities_latin = false;
	
	//Dateibrowser
	config.filebrowserImageBrowseUrl = 'mediamanager.php?module=mediamanager:1';
	config.filebrowserFlashBrowseUrl = 'mediamanager.php?module=mediamanager:2';
	
	//Enter-Modus
	config.enterMode = CKEDITOR.ENTER_P;
	config.shiftEnterMode = CKEDITOR.ENTER_BR;
	//config.ignoreEmptyParagraph = false;
	
	//Spell-Checker
	config.scayt_autoStartup = false;
	config.scayt_sLang = 'de_DE';
	
	//Toolbar
	config.toolbar = 'apexx';
	config.toolbar_apexx =
	[
	    ['Source','-','NewPage','Preview','-','Templates','-','SpellChecker'],
	    ['Cut','Copy','Paste','PasteText','PasteFromWord'],
	    ['Undo','Redo','-','Find','Replace','-','SelectAll','RemoveFormat'],
	    '/',
	    ['NumberedList','BulletedList','-','Outdent','Indent','Blockquote'],
	    ['JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock'],
	    ['Link','Unlink','Anchor'],
	    ['Image','Flash','Table','HorizontalRule','SpecialChar'],
	    ['Maximize', 'ShowBlocks'],
	    '/',
	    ['Format','Font','FontSize'],
	    ['Bold','Italic','Underline','Strike','-','Subscript','Superscript'],
	    ['TextColor','BGColor'],
	    ['About']
	];
};
