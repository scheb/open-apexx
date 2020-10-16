<?php 

# Comment Class
# =============

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



//Kommentar melden
function misc_comments_report() {
	global $apx,$db,$set,$user;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ContentID!');
	$apx->lang->drop('report', 'comments');
	$apx->tmpl->loaddesign('blank');
	headline($apx->lang->get('HEADLINE_REPORT'),str_replace('&','&amp;',$_SERVER['REQUEST_URI']));
	titlebar($apx->lang->get('HEADLINE_REPORT'));
	
	//Absenden
	if ( $_POST['send'] ) {
		
		//Kommentar auslesen
		list($commenttext) = $db->first("SELECT text FROM ".PRE."_comments WHERE id='".$_REQUEST['id']."' LIMIT 1");
		
		if ( !$_POST['text'] ) message('back');
		elseif ( !$commenttext ) message('invalid comment-ID!');
		else {
			
			//eMail senden
			if ( $set['comments']['reportmail'] ) {
				$input['URL']=$_POST['url'];
				$input['REASON']=$_POST['text'];
				$input['TEXT']=$commenttext;
				sendmail($set['comments']['reportmail'],'REPORT',$input);
			}
			
			message($apx->lang->get('MSG_OK'));
		}
	}
	else {
		$apx->tmpl->assign('POSTTO',HTTPDIR.'misc.php?action=comments_report');
		$apx->tmpl->assign('URL',compatible_hsc($_REQUEST['url']));
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->parse('report', 'comments');
	}
}


?>