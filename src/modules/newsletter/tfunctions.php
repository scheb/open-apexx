<?php 

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



//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



//Newsletter-Form ausgeben
function newsletter_form() {
	global $set,$db,$apx,$user;
	$tmpl=new tengine;
	
	$apx->lang->drop('form','newsletter');
	
	//Kategorien
	$catinfo=$set['newsletter']['categories'];
	if ( !is_array($set['newsletter']['categories']) ) $set['newsletter']['categories']=array();
	asort($catinfo);
	
	foreach ( $catinfo AS $id => $name ) {
		++$i;
		$catdata[$i]['ID']=$id;
		$catdata[$i]['TITLE']=$name;
	}
	
	$tmpl->assign('CATEGORY',$catdata);
	$tmpl->parse('functions/form','newsletter');	
}

?>