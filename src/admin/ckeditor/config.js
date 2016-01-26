/*
Copyright (c) 2003-2015, CKSource - Frederico Knabben. All rights reserved.
For licensing, see LICENSE.md or http://ckeditor.com/license
*/

CKEDITOR.editorConfig = function( config )
{

	//Resize
	config.resize_enabled = false;

	//Paste
	config.forcePasteAsPlainText = true;
	config.pasteFromWordRemoveFontStyles = true;
	config.pasteFromWordRemoveStyles = true;

	//Sonderzeichen
	config.entities = false;

	//Dateibrowser
	config.filebrowserImageBrowseUrl = 'mediamanager.php?module=mediamanager:1';
	config.filebrowserFlashBrowseUrl = 'mediamanager.php?module=mediamanager:2';

	//Entermodus
	config.enterMode = CKEDITOR.ENTER_P;
	config.shiftEnterMode = CKEDITOR.ENTER_BR;

	//Sprache
	config.language = 'de';
	config.defaultLanguage = 'de';
	config.scayt_autoStartup = true;
	config.scayt_sLang = 'de_DE';

	//Toolbar
	config.toolbar = 'apexx'
	config.toolbar_apexx =
	[
		{ name: 'document', items: [ 'Source', '-', 'NewPage', 'Preview', 'Print' ] },
		{ name: 'clipboard', items: [ 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo' ] },
		{ name: 'editing', items: [ 'Find', 'Replace', '-', 'SelectAll', '-', 'Scayt' ] },
		{ name: 'colors', items: [ 'TextColor', 'BGColor' ] },
		{ name: 'tools', items: [ 'Maximize', 'ShowBlocks' ] },
		{ name: 'links', items: [ 'Link', 'Unlink', 'Anchor' ] },
		'/',
		{ name: 'basicstyles', items: [ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ] },
		{ name: 'paragraph', items: [ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
		{ name: 'insert', items: [ 'Image', 'Flash', 'Table', 'HorizontalRule', 'SpecialChar', 'PageBreak', 'Iframe' ] },
		{ name: 'about', items: [ 'About' ] },
	];

};