<?php 

define('APXRUN',true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_start.php');  //////////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->module('faq');
$apx->lang->drop('faq');
headline($apx->lang->get('HEADLINE'),mklink('faq.php','faq.html'));
titlebar($apx->lang->get('HEADLINE'));

$_REQUEST['id']=(int)$_REQUEST['id'];


////////////////////////////////////////////////////////////////////////////////////////////////////////

require_once(BASEDIR.'lib/class.recursivetree.php');
$tree = new RecursiveTree(PRE.'_faq', 'id');

if ( $_REQUEST['id'] ) {
	$db->query("UPDATE ".PRE."_faq SET hits=hits+1 WHERE id='".$_REQUEST['id']."' LIMIT 1");
	$node = $tree->getNode($_REQUEST['id'], array('*'));
	if ( !$node || ( !$user->is_team_member() && $node['starttime']==0 ) ) {
		filenotfound();
	}
	$node['level'] = 0;
	$subData = $tree->getTree(array('*'), $_REQUEST['id'], "starttime!='0'");
	$data = array_merge(array($node), $subData);
}
else {
	$data = $tree->getTree(array('*'), null, "starttime!='0'");
}

$first = null;
if ( count($data) ) {
	foreach ( $data AS $res ) {
		++$i;
		if ( !$first ) {
			$first = $res;
		}
		
		//Link
		$link=mklink(
			'faq.php?id='.$res['id'],
			'faq,'.$res['id'].urlformat($res['question']).'.html'
		);
		
		//Nummer
		$prefixstring='';
		++$prefix[$res['level']];
		for ( $pi=$res['level']+1; isset($prefix[$pi]); $pi++ ) {
			unset($prefix[$pi]);
		}
		$prefixstring=implode('.',$prefix).'.';
		
		//Antwort
		$answer = mediamanager_inline($res['answer']);
		if ( $apx->is_module('glossar') ) $answer = glossar_highlight($answer);
		
		$faqdata[$i]['ID']=$res['id'];
		$faqdata[$i]['NUMBER']=$prefixstring;
		$faqdata[$i]['QUESTION']=$res['question'];
		$faqdata[$i]['ANSWER']=$answer;
		$faqdata[$i]['LINK']=$link;
		$faqdata[$i]['TIME']=$res['starttime'];
		$faqdata[$i]['HITS']=number_format($res['hits'],0,'','.');
		$faqdata[$i]['LEVEL']=$res['level']+($_REQUEST['id'] ? 1 : 0);
	}
}

if ( $_REQUEST['id'] ) $apx->tmpl->assign('DISPLAY',1);
$apx->tmpl->assign('FAQ',$faqdata);
$apx->tmpl->assign_static('META_DESCRIPTION',replace($first['meta_description']));
$apx->tmpl->parse('faq');


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');  /////////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>