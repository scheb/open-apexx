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


# NEWSLETTER
# ===========

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class action {

//***************************** Newsletter zeigen *****************************
function show() {
	global $set,$db,$apx,$html;
	
	quicklink_multi('newsletter.add');
	if ( $apx->is_module('news') ) quicklink_multi('newsletter.addnews');
	quicklink_out();
	
	list($rec)=$db->first("
		SELECT count(DISTINCT eid)
		FROM ".PRE."_newsletter_emails AS ne
		JOIN ".PRE."_newsletter_emails_cat AS nec ON ne.id=nec.eid
		WHERE active=1
	");
	echo '<p class="hint">'.$apx->lang->get('CURRENTREC').': '.number_format($rec,0,'','.').'</p>';
	
	$orderdef[0]='addtime';
	$orderdef['subject']=array('subject','ASC','COL_SUBJECT');
	$orderdef['addtime']=array('addtime','DESC','SORT_ADDTIME');
	$orderdef['sendtime']=array('sendtime','DESC','COL_SENDTIME');
	
	$col[]=array('&nbsp;',1,'');
	$col[]=array('COL_SUBJECT',50,'class="title"');
	$col[]=array('COL_CATEGORY',30,'align="center"');
	$col[]=array('COL_SENDTIME',20,'align="center"');
	
	list($count)=$db->first("SELECT count(id) FROM ".PRE."_newsletter");
	pages('action.php?action=newsletter.show&amp;sortby='.$_REQUEST['sortby'],$count);
	
	$data=$db->fetch("SELECT id,catid,subject,done,sendtime FROM ".PRE."_newsletter ".getorder($orderdef).getlimit());
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			if ( $res['done'] ) $tabledata[$i]['COL1']='<img src="design/check.gif" alt="'.$apx->lang->get('ISSEND').'" title="'.$apx->lang->get('ISSEND').'" />';
			else $tabledata[$i]['COL1']='&nbsp;';
			
			$tabledata[$i]['COL2']=replace($res['subject']);
			$tabledata[$i]['COL3']=replace($set['newsletter']['categories'][$res['catid']]);
			$tabledata[$i]['COL4']=iif($res['sendtime'],apxdate($res['sendtime']),'-');
			
			//Optionen
			if ( $apx->user->has_right('newsletter.edit') ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'newsletter.edit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('newsletter.del') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'newsletter.del', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('newsletter.send') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('emailsend.gif', 'newsletter.send', 'id='.$res['id'], $apx->lang->get('SEND'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('newsletter.preview') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('emailpreview.gif', 'newsletter.preview', 'id='.$res['id'], $apx->lang->get('PREVIEW'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
	orderstr($orderdef,'action.php?action=newsletter.show');
	
	save_index($_SERVER['REQUEST_URI']);
}



//***************************** Newsletter erstellen *****************************
function add() {
	global $set,$db,$apx;

	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['subject'] || !$_POST['text'] || !isset($_POST['catid']) || !$_POST['text_html'] ) infoNotComplete();
		else {
			$_POST['addtime']=time();
			
			$db->dinsert(PRE.'_newsletter','catid,subject,text,text_html,addsig,addtime');
			$insertid=$db->insert_id();
			logit('NEWSLETTER_ADD',$insertid);
			printJSRedirect('action.php?action=newsletter.show');
		}
	}
	else {
		$_POST['addsig'] = 1;
		
		//Kategorien
		$catinfo=$set['newsletter']['categories'];
		asort($catinfo);
		foreach ( $catinfo AS $id => $name ) {
			$catlist.='<option value="'.$id.'"'.iif(intval($_POST['catid'])==$id,'selected="selected"').'>'.$name.'</option>';
		}
		
		$apx->tmpl->assign('ACTION','add');
		$apx->tmpl->assign('SUBJECT',compatible_hsc($_POST['subject']));
		$apx->tmpl->assign('CATLIST',$catlist);
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('TEXT_HTML',compatible_hsc($_POST['text_html']));
		$apx->tmpl->assign('ADDSIG',(int)$_POST['addsig']);
		
		$apx->tmpl->parse('add_edit');
	}
}



//***************************** Newsletter aus News erstellen *****************************
function addnews() {
	global $set,$db,$apx;
	if ( !$apx->is_module('news') ) die('module news is not active!');
	
	//Zeitraum generieren
	if ( !$_POST['period'] && !$_POST['finish'] && !$_POST['send'] ) {
		$_POST['day_count']=(int)$_POST['day_count'];
		$_POST['week_count']=(int)$_POST['week_count'];
		$_POST['month_count']=(int)$_POST['month_count'];
		if ( $_POST['type']=='day' && $_POST['day_count'] ) $_POST['period']=$_POST['day_count']*24*3600;
		elseif ( $_POST['type']=='week' && $_POST['week_count'] ) $_POST['period']=$_POST['week_count']*7*24*3600;
		elseif ( $_POST['type']=='month' && $_POST['month_count'] ) $_POST['period']=$_POST['month_count']*30*24*3600;
	}
	
	//Newsletter erstellen
	if ( $_POST['finish'] ) {
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) infoInvalidToken();
			elseif ( !$_POST['subject'] || !$_POST['text'] ) infoNotComplete();
			else {
				$_POST['addtime']=time();
				
				$db->dinsert(PRE.'_newsletter','catid,subject,text,text_html,addtime');
				$insertid=$db->insert_id();
				logit('NEWSLETTER_ADDNEWS',$insertid);
				printJSRedirect('action.php?action=newsletter.show');
			}
		}
		else {
			if ( is_array($_POST['news']) && count($_POST['news']) ) {
				foreach ( $_POST['news'] AS $id => $trash ) {
					$id=(int)$id;
					if ( !$id ) continue;
					$ids[]=$id;
				}
				
				//Sonderzeichen-Symbole entfernen
				$trans=get_html_translation_table(HTML_ENTITIES);
				$trans=array_flip($trans);
				
				$data=$db->fetch("SELECT id,secid,title,subtitle,teaser,text FROM ".PRE."_news WHERE id IN (".implode(',',$ids).")");
				if ( count($data) ) {
					foreach ( $data AS $res ) {
						++$ii;
						
						//Image-Codes entfernen
						$res['teaser']=preg_replace('#{IMAGE\( *[0-9]+ *\)}#s','',$res['teaser']);
						$res['text']=preg_replace('#{IMAGE\( *[0-9]+ *\)}#s','',$res['text']);
						
						$tmp=unserialize_section($res['secid']);
						$newslink = HTTP_HOST.mklink(
							'news.php?id='.$res['id'],
							'news,id'.$res['id'].urlformat($res['title']).'.html',
							iif($set['main']['forcesection'],iif(unserialize_section($res['secid'])==array('all'),$apx->section_default,array_shift($tmp)),0)
						);
						
						$text.=strip_tags($res['title'])."\n".iif($res['teaser'],strip_tags(strtr($res['teaser'],$trans))."\n").strip_tags(strtr($res['text'],$trans))."\n\n\n";
						$text_html.=iif($ii>1,'<br><br><br>').'<b><a href="'.$newslink.'">'.$res['title'].'</a></b><br>'.iif($res['teaser'],$res['teaser'].'<br>').$res['text'];
					}
				}
			}
			
			$_POST['text']=trim($text);
			$_POST['text_html']=trim($text_html);
		}
		
		//Kategorien
		$catinfo=$set['newsletter']['categories'];
		asort($catinfo);
		foreach ( $catinfo AS $id => $name ) {
			$catlist.='<option value="'.$id.'"'.iif(intval($_POST['catid'])==$id,'selected="selected"').'>'.$name.'</option>';
		}
		
		$apx->tmpl->assign('ACTION','add');
		$apx->tmpl->assign('SUBJECT',compatible_hsc($_POST['subject']));
		$apx->tmpl->assign('CATLIST',$catlist);
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('TEXT_HTML',compatible_hsc($_POST['text_html']));
		
		$apx->tmpl->parse('add_edit');
	}
	
	//News auflisten
	elseif ( $_POST['period'] ) {
		
		//Sektions-Liste
		if ( !is_array($_POST['secid']) || $_POST['secid'][0]=='all' ) $_POST['secid']=array('all');
		
		//Filter nach Sektionen
		$secfilter = '';
		if ( $_POST['secid'][0]!='all' ) {
			foreach ( $_POST['secid'] AS $secid ) {
				$secfilter .= " secid LIKE '%|".intval($secid)."|%' OR ";
			}
			$secfilter = " AND ( ".$secfilter." secid='all' )";
		}
		
		$_POST['period']=(int)$_POST['period'];
		list($count)=$db->first("SELECT count(id) FROM ".PRE."_news WHERE starttime>='".(time()-$_POST['period'])."' ".$secfilter);
		$maxpage=ceil($count/10);
		
		$_REQUEST['p']=(int)$_REQUEST['p'];
		if ( $_POST['previous'] ) --$_REQUEST['p'];
		if ( $_POST['next'] ) ++$_REQUEST['p'];
		if ( !$_REQUEST['p'] ) $_REQUEST['p']=1;
		if ( $_REQUEST['p']<1 || $_REQUEST['p']>$maxpage ) $_REQUEST['p']=1;
		
		if ( !is_array($_POST['news']) ) $_POST['news']=array();
		$data=$db->fetch("SELECT id,title,subtitle,teaser,text FROM ".PRE."_news WHERE starttime>='".(time()-$_POST['period'])."' ".$secfilter." LIMIT ".(($_REQUEST['p']-1)*10).",10");
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				++$i;
				$tabledata[$i]['ID']=$res['id'];
				$tabledata[$i]['TITLE']=strip_tags($res['title']);
				if ( $res['teaser'] ) $tabledata[$i]['TEXT']=shorttext(strip_tags($res['teaser']),200);
				else $tabledata[$i]['TEXT']=shorttext(strip_tags($res['text']),200);
				$tabledata[$i]['CHECKED']=iif(in_array($res['id'],$_POST['news']),1,0);
				unset($_POST['news'][$res['id']]);
			}
		}
		
		//Ausgewählte News
		foreach ( $_POST['news'] AS $id => $trash ) {
			$checkednews.='<input type="hidden" name="news['.$id.']" value="'.$id.'" />';
		}
		
		//Sektionen
		$seclist = array();
		foreach ( $_POST['secid'] AS $secid ) {
			$seclist[] = array('SECID' => $secid);
		}
		
		$apx->tmpl->assign('SECTION',$seclist);
		$apx->tmpl->assign('NEWS',$tabledata);
		$apx->tmpl->assign('MAXPAGE',$maxpage);
		$apx->tmpl->assign('PERIOD',$_POST['period']);
		$apx->tmpl->assign('P',$_REQUEST['p']);
		$apx->tmpl->assign('CHECKEDNEWS',$checkednews);
		
		$apx->tmpl->parse('addnews_choose');
	}
	
	//Zeitraum bestimmen
	else {
		if ( !$_POST['day_count'] ) $_POST['day_count']=7;
		if ( !$_POST['week_count'] ) $_POST['week_count']=2;
		if ( !$_POST['month_count'] ) $_POST['month_count']=1;
		if ( !$_POST['type'] ) $_POST['type']='day';
		
		$apx->tmpl->assign('SECID',array('all'));
		$apx->tmpl->assign('TYPE',$_POST['type']);
		$apx->tmpl->assign('DAY_COUNT',$_POST['day_count']);
		$apx->tmpl->assign('WEEK_COUNT',$_POST['week_count']);
		$apx->tmpl->assign('MONTH_COUNT',$_POST['month_count']);
		$apx->tmpl->parse('addnews_period');
	}
}



//***************************** Newsletter bearbeiten *****************************
function edit() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');

	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['subject'] || !isset($_POST['catid']) || !$_POST['text'] || !$_POST['text_html'] ) infoNotComplete();
		else {
			$db->dupdate(PRE.'_newsletter','catid,subject,text,addsig,text_html',"WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('NEWSLETTER_EDIT',$_REQUEST['id']);
			printJSRedirect(get_index('newsletter.show'));
		}
	}
	else {
		list(
			$_POST['subject'],
			$_POST['catid'],
			$_POST['text'],
			$_POST['text_html'],
			$_POST['addsig']
		)=$db->first("SELECT subject,catid,text,text_html,addsig FROM ".PRE."_newsletter WHERE id='".$_REQUEST['id']."' LIMIT 1");
		
		//Kategorien
		$catinfo=$set['newsletter']['categories'];
		asort($catinfo);
		foreach ( $catinfo AS $id => $name ) {
			$catlist.='<option value="'.$id.'"'.iif(intval($_POST['catid'])==$id,'selected="selected"').'>'.$name.'</option>';
		}
		
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		$apx->tmpl->assign('ACTION','edit');
		$apx->tmpl->assign('SUBJECT',compatible_hsc($_POST['subject']));
		$apx->tmpl->assign('CATLIST',$catlist);
		$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
		$apx->tmpl->assign('TEXT_HTML',compatible_hsc($_POST['text_html']));
		$apx->tmpl->assign('ADDSIG',(int)$_POST['addsig']);
		
		$apx->tmpl->parse('add_edit');
	}
}



//***************************** Newsletter löschen *****************************
function del() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');

	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$db->query("DELETE FROM ".PRE."_newsletter WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('NEWSLETTER_DEL','ID #'.$_REQUEST['id']);
			printJSReload();
		}
	}
	else {
		list($title) = $db->first("SELECT subject FROM ".PRE."_newsletter WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('deltitle',array('ID'=>$_REQUEST['id']),'/');
	}
}



//***************************** Newsletter senden *****************************
function send() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	$countPerCall = 50;
	@set_time_limit(600);
	
	//Sicherheitsabfrage
	if ( !$_REQUEST['doit'] ) {
		list($title) = $db->first("SELECT subject FROM ".PRE."_newsletter WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_SEND', array('TITLE' => compatible_hsc($title))));
		tmessageOverlay('send',array('ID'=>$_REQUEST['id']));
		return;
	}
	
	//FORWARDER
	if ( !isset($_REQUEST['done']) ) {
		tmessageOverlay('sending',array('FORWARDER'=>'action.php?action=newsletter.send&amp;id='.$_REQUEST['id'].'&amp;doit=1&amp;done=0'));
		return;
	}
	
	//VARS
	$done=(int)$_REQUEST['done'];
	$_REQUEST['id']=(int)$_REQUEST['id'];
	
	//Newsletter-Info auslesen
	$newsletter=$db->first("SELECT id,catid,subject,text,text_html,addsig FROM ".PRE."_newsletter WHERE id='".$_REQUEST['id']."' LIMIT 1");
	if ( !$newsletter['id'] ) die('no valid newsletter!');
	
	//SEND NEWSLETTER
	$data = $db->fetch("
		SELECT ne.email, nec.html
		FROM ".PRE."_newsletter_emails AS ne
		JOIN ".PRE."_newsletter_emails_cat AS nec ON ne.id=nec.eid
		WHERE nec.catid='".$newsletter['catid']."' AND nec.active=1
		ORDER BY ne.id ASC
		LIMIT ".$done.",".$countPerCall."
	");
	if ( count($data) ) {
		
		//HTML-Newsletter formatieren
		$minput['{WEBSITE}']=replace($set['main']['websitename']);
		$minput['{SUBJECT}']=replace($newsletter['subject']);
		$minput['{TEXT}']=$newsletter['text_html'];
		if ( $newsletter['addsig'] ) $minput['{SIGNATURE}']=$set['newsletter']['sig_html'];
		else $minput['{SIGNATURE}']='';
		$html_template=stripslashes(implode('',file(BASEDIR.getpath('tmpl_modules_public',array('MODULE'=>'newsletter','THEME'=>'default')).'html_newsletter.html')));
		$html_template=preg_replace('#{\*(.*)\*}#siU','',$html_template); //Kommentare entfernen
		$newsletter['text_html']=strtr($html_template,$minput);
		
		//Text-Newsletter formatieren
		if ( $newsletter['addsig'] ) $textletter = $newsletter['text']."\n\n".$set['newsletter']['sig_text'];
		else $textletter = $newsletter['text'];
		
		//Senden
		foreach ( $data AS $res ) {
			++$i;
			$this->sendNewsletter($res, $newsletter);
		}
		
		////// FORWARDER
		
		//Vorgang beendet
		if ( $i<$countPerCall ) {
			$db->query("UPDATE ".PRE."_newsletter SET done=1,sendtime='".time()."' WHERE id='".$_REQUEST['id']."' LIMIT 1");
			logit('NEWSLETTER_SEND','ID #'.$_REQUEST['id']);
			tmessageOverlay('send_ok');
			return;
		}
		
		//Weiter gehts...
		else {
			tmessageOverlay('sending',array('FORWARDER'=>'action.php?action=newsletter.send&amp;id='.$_REQUEST['id'].'&amp;doit=1&amp;done='.($done+$countPerCall)));
			return;
		}
	}
	
	//Keine Empfänger, das wars...
	else {
		$db->query("UPDATE ".PRE."_newsletter SET done=1,sendtime='".time()."' WHERE id='".$_REQUEST['id']."' LIMIT 1");
		logit('NEWSLETTER_SEND','ID #'.$_REQUEST['id']);
		tmessageOverlay('send_ok');
		return;
	}
}



//Newsletter verschicken
function sendNewsletter($res, $newsletter) {
	global $set,$db,$apx;
	if ( $res['html'] ) {
		mail(
			$res['email'],
			$newsletter['subject'],
			$newsletter['text_html'],
			"MIME-Version: 1.0\nContent-type: text/html; charset=".$set['main']['charset']."\nFrom: ".$set['main']['mailbotname']."<".$set['main']['mailbot'].">"
		);
	}
	else {
		mail(
			$res['email'],
			$newsletter['subject'],
			$newsletter['text'],
			"From: ".$set['main']['mailbotname']."<".$set['main']['mailbot'].">"
		);
	}
}



//***************************** Vorschau senden *****************************
function preview() {
	global $set,$db,$apx;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	
	if ( isset($_POST['sendto']) && checkmail($_POST['sendto']) ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$newsletter=$db->first("SELECT id,subject,text,text_html,addsig FROM ".PRE."_newsletter WHERE id='".$_REQUEST['id']."' LIMIT 1");
			if ( !$newsletter['id'] ) die('no valid newsletter!');
			
			//HTML-Newsletter formatieren
			$minput['{WEBSITE}']=replace($set['main']['websitename']);
			$minput['{SUBJECT}']=replace($newsletter['subject']);
			$minput['{TEXT}']=$newsletter['text_html'];
			if ( $newsletter['addsig'] ) $minput['{SIGNATURE}']=$set['newsletter']['sig_html'];
			else $minput['{SIGNATURE}']='';
			$html_template=stripslashes(implode('',file(BASEDIR.getpath('tmpl_modules_public',array('MODULE'=>'newsletter','THEME'=>'default')).'html_newsletter.html')));
			$html_template=preg_replace('#{\*(.*)\*}#siU','',$html_template); //Kommentare entfernen
			$newsletter['text_html']=strtr($html_template,$minput);
			
			mail(
				$_POST['sendto'],
				$newsletter['subject'],
				$newsletter['text_html'],
				"MIME-Version: 1.0\nContent-type: text/html; charset=".$set['charset']."\nFrom: ".$set['main']['mailbotname']."<".$set['main']['mailbot'].">"
			);
			
			if ( $newsletter['addsig'] ) $textletter = $newsletter['text']."\n\n".$set['newsletter']['sig_text'];
			else $textletter = $newsletter['text'];
			mail(
				$_POST['sendto'],
				$newsletter['subject'],
				$textletter,
				"From: ".$set['main']['mailbotname']."<".$set['main']['mailbot'].">"
			);
			
			tmessageOverlay('preview_ok');
		}
	}
	else {
		tmessageOverlay('preview',array('ID'=>$_REQUEST['id']));
	}
}



//***************************** Adressen zeigen *****************************
function eshow() {
	global $set,$db,$apx,$html;
	
	//Suche durchführen
	if ( $_REQUEST['item'] ) {
		$where=" AND email LIKE '%".addslashes_like($_REQUEST['item'])."%'";
		
		$data=$db->fetch("SELECT id FROM ".PRE."_newsletter_emails WHERE 1 ".$where);
		$ids = get_ids($data, 'id');
		$ids[] = -1;
		$searchid = saveSearchResult('admin_newsletter_email', $ids, $_REQUEST['item']);
		header("HTTP/1.1 301 Moved Permanently");
		header('Location: action.php?action=newsletter.eshow&what='.$_REQUEST['what'].'&searchid='.$searchid);
		return;
	}
	
	
	//Suchergebnis?
	$resultFilter = '';
	if ( $_REQUEST['searchid'] ) {
		$searchRes = getSearchResult('admin_newsletter_email', $_REQUEST['searchid']);
		if ( $searchRes ) {
			list($resultIds, $resultMeta) = $searchRes;
			$_REQUEST['item'] = $resultMeta;
			$resultFilter = " AND id IN (".implode(', ', $resultIds).")";
		}
		else {
			$_REQUEST['searchid'] = '';
		}
	}
	
	
	quicklink('newsletter.eadd');
	
	$layerdef[]=array('LAYER_ALL','action.php?action=newsletter.eshow',!$_REQUEST['what']);
	$layerdef[]=array('LAYER_INACTIVE','action.php?action=newsletter.eshow&amp;what=inactive',$_REQUEST['what']=='inactive');
	
	//Layer Header ausgeben
	$html->layer_header($layerdef);
	
	$apx->tmpl->assign('WHAT',$_REQUEST['what']);
	$apx->tmpl->assign('ITEM',compatible_hsc($_REQUEST['item']));
	$apx->tmpl->parse('esearch');
	
	//Inaktive Adressen
	if ( $_REQUEST['what']=='inactive' ) {
		list($count)=$db->first("
			SELECT count(DISTINCT ne.id)
			FROM ".PRE."_newsletter_emails AS ne
			JOIN ".PRE."_newsletter_emails_cat AS nec ON ne.id=nec.eid
			WHERE nec.active=0 ".$resultFilter
		);
		pages('action.php?action=newsletter.eshow&amp;what=inactive&amp;sortby='.$_REQUEST['sortby'],$count);
		$data=$db->fetch("
			SELECT DISTINCT ne.id, ne.email
			FROM ".PRE."_newsletter_emails AS ne
			JOIN ".PRE."_newsletter_emails_cat AS nec ON ne.id=nec.eid
			WHERE nec.active=0 ".$resultFilter."
			ORDER BY ne.email
			ASC ".getlimit($set['epp'])
		);
		$this->eshow_print($data);
		save_index($_SERVER['REQUEST_URI']);
	}
	
	//Alle Adressen
	else {
		list($count)=$db->first("
			SELECT count(id)
			FROM ".PRE."_newsletter_emails
			WHERE 1 ".$resultFilter
		);
		pages('action.php?action=newsletter.eshow&amp;sortby='.$_REQUEST['sortby'],$count);
		$data=$db->fetch("
			SELECT id,email
			FROM ".PRE."_newsletter_emails
			WHERE 1 ".$resultFilter."
			ORDER BY email ASC
			".getlimit($set['epp'])
		);
		$this->eshow_print($data);
		save_index($_SERVER['REQUEST_URI']);
	}
	
	//Layer-Footer ausgeben
	$html->layer_footer();
}


//Einträge auflisten
function eshow_print($data) {
	global $set,$db,$apx,$html;
	
	$col[]=array('',1,'');
	$col[]=array('COL_EMAIL',70,'class="title"');
	$col[]=array('COL_CATEGORIES',30,'style="text-align:center;"');
	
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			list($totalCount) = $db->first("SELECT count(catid) FROM ".PRE."_newsletter_emails_cat WHERE eid='".$res['id']."'");
			list($inactiveCount) = $db->first("SELECT count(catid) FROM ".PRE."_newsletter_emails_cat WHERE eid='".$res['id']."' AND active=0");
			
			if ( $totalCount==$inactiveCount ) $tabledata[$i]['COL1']='<img src="design/reddot.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			elseif ( $inactiveCount ) $tabledata[$i]['COL1']='<img src="design/greendotwait.gif" alt="'.$apx->lang->get('CORE_INACTIVE').'" title="'.$apx->lang->get('CORE_INACTIVE').'" />';
			else $tabledata[$i]['COL1']='<img src="design/greendot.gif" alt="'.$apx->lang->get('CORE_ACTIVE').'" title="'.$apx->lang->get('CORE_ACTIVE').'" />';
			
			$tabledata[$i]['ID']=$res['id'];
			$tabledata[$i]['COL2']=replace($res['email']);
			$tabledata[$i]['COL3']=$totalCount.($inactiveCount ? ' ('.$inactiveCount.' '.$apx->lang->get('NOT_ACTIVE').')' : '');
			
			//Optionen
			if ( $apx->user->has_right('newsletter.eedit') ) $tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'newsletter.eedit', 'id='.$res['id'], $apx->lang->get('CORE_EDIT'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $apx->user->has_right('newsletter.edel') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'newsletter.edel', 'id='.$res['id'], $apx->lang->get('CORE_DEL'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
			
			if ( $inactiveCount && $apx->user->has_right('newsletter.eenable') ) $tabledata[$i]['OPTIONS'].=optionHTMLOverlay('enable.gif', 'newsletter.eenable', 'id='.$res['id'], $apx->lang->get('CORE_ENABLE'));
			else $tabledata[$i]['OPTIONS'].='<img src="design/ispace.gif" alt="" />';
		}
	}
	
	$multiactions = array();
	if ( $apx->user->has_right('newsletter.edel') ) $multiactions[] = array($apx->lang->get('CORE_DEL'), 'action.php?action=newsletter.edel', false);
	if ( $apx->user->has_right('newsletter.eenable') ) $multiactions[] = array($apx->lang->get('CORE_ENABLE'), 'action.php?action=newsletter.eenable', false);
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col, $multiactions);
}



//***************************** Adresse hinzufügen *****************************
function eadd() {
	global $set,$db,$apx;
	
	if ( $_POST['catid'][0]=='all' || !isset($_POST['catid']) ) $_POST['catid']=array('all');
	
	if ( $_POST['send']==1 ) {
		list($check) = $db->first("SELECT id FROM ".PRE."_newsletter_emails WHERE email LIKE '".addslashes_like($_POST['email'])."'");
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['email'] || !$_POST['catid'] ) infoNotComplete();
		elseif ( !checkmail($_POST['email']) ) info($apx->lang->get('INFO_WRONGSYNTAX'));
		elseif ( $check ) info($apx->lang->get('INFO_EXISTS'));
		else {
			
			//Kategorien
			if ( $_POST['catid'][0]=='all' ) {
				$catids = array_keys($set['newsletter']['categories']);
			}
			else {
				$catids = array_unique(array_map('intval', $_POST['catid']));
			}
			
			$db->query("
				INSERT INTO ".PRE."_newsletter_emails
				(email) VALUES
				('".addslashes($_POST['email'])."')
			");
			$nid = $db->insert_id();
			foreach ( $catids AS $catid ) {
				$db->query("
					INSERT INTO ".PRE."_newsletter_emails_cat
					(eid, catid, active, html) VALUES
					('".$nid."', '".$catid."', '1', '".($_POST['html'] ? true : false)."')
				");
			}
			
			logit('NEWSLETTER_EADD','ID #'.$nid);
			printJSRedirect('action.php?action=newsletter.eshow');
		}
	}
	else {
		
		//Kategorien
		$catinfo=$set['newsletter']['categories'];
		asort($catinfo);
		$catlist='<option value="all"'.iif($_POST['catid'][0]=='all','selected="selected"').' style="font-weight:bold;">'.$apx->lang->get('ALL').'</option>';
		foreach ( $catinfo AS $id => $name ) {
			$catlist.='<option value="'.$id.'"'.iif(in_array($id,$_POST['catid']),'selected="selected"').'>'.replace($name).'</option>';
		}
		
		$apx->tmpl->assign('CATLIST',$catlist);
		$apx->tmpl->assign('EMAIL',compatible_hsc($_POST['email']));
		$apx->tmpl->assign('HTML',(int)$_POST['html']);
		$apx->tmpl->assign('ACTION','add');
		
		$apx->tmpl->parse('eadd_eedit');
	}
}



//***************************** Adresse bearbeiten *****************************
function eedit() {
	global $set,$db,$apx;
	$_REQUEST['id'] = (int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	
	if ( $_POST['catid'][0]=='all' || !isset($_POST['catid']) ) $_POST['catid']=array('all');
	
	if ( $_POST['send']==1 ) {
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['catid'] ) infoNotComplete();
		else {
			
			//Kategorien
			if ( $_POST['catid'][0]=='all' ) {
				$catids = array_keys($set['newsletter']['categories']);
			}
			else {
				$catids = array_unique(array_map('intval', $_POST['catid']));
			}
			
			$db->query("DELETE FROM ".PRE."_newsletter_emails_cat WHERE eid='".$_REQUEST['id']."'");
			foreach ( $catids AS $catid ) {
				$db->query("
					INSERT INTO ".PRE."_newsletter_emails_cat
					(eid, catid, active, html) VALUES
					('".$_REQUEST['id']."', '".$catid."', '1', '".($_POST['html'] ? true : false)."')
				");
			}
			
			logit('NEWSLETTER_EEDIT','ID #'.$_REQUEST['id']);
			printJSRedirect(get_index('newsletter.eshow'));
		}
	}
	else {
		$res = $db->first("SELECT email FROM ".PRE."_newsletter_emails WHERE id='".$_REQUEST['id']."' LIMIT 1");
		$_POST['email'] = $res['email'];
		$_POST['html'] = 0;
		
		$queryData = $db->fetch("SELECT catid, html FROM ".PRE."_newsletter_emails_cat WHERE eid='".$_REQUEST['id']."' AND active=1");
		$catids = get_ids($queryData, 'catid');
		foreach ( $queryData AS $res ) {
			if ( $res['html'] ) {
				$_POST['html'] = 1;
				break;
			}
		}
		
		//Kategorien
		$catinfo=$set['newsletter']['categories'];
		asort($catinfo);
		$catlist='<option value="all" style="font-weight:bold;">'.$apx->lang->get('ALL').'</option>';
		foreach ( $catinfo AS $id => $name ) {
			$catlist.='<option value="'.$id.'"'.iif(in_array($id,$catids),'selected="selected"').'>'.replace($name).'</option>';
		}
		
		$apx->tmpl->assign('CATLIST',$catlist);
		$apx->tmpl->assign('EMAIL',compatible_hsc($_POST['email']));
		$apx->tmpl->assign('HTML',(int)$_POST['html']);
		$apx->tmpl->assign('ACTION','edit');
		$apx->tmpl->assign('ID',$_REQUEST['id']);
		
		$apx->tmpl->parse('eadd_eedit');
	}
}



//***************************** Adresse löschen *****************************
function edel() {
	global $set,$db,$apx;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$cache = array_map('intval', $_REQUEST['multiid']);
			if ( !count($cache) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('newsletter.eshow'));
				return;
			}
			
			if ( count($cache) ) {	
				$db->query("DELETE FROM ".PRE."_newsletter_emails WHERE id IN (".implode(",",$cache).")");
				$db->query("DELETE FROM ".PRE."_newsletter_emails_cat WHERE eid IN (".implode(",",$cache).")");
				foreach ( $cache AS $id ) logit('NEWSLETTER_EDEL',"ID #".$id);
			}
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('newsletter.eshow'));
		}
	}
	
	//Einzeln
	else {
		$_REQUEST['id']=(int)$_REQUEST['id'];
		if ( !$_REQUEST['id'] ) die('missing ID!');
		
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) printInvalidInfo();
			else {
				$db->query("DELETE FROM ".PRE."_newsletter_emails WHERE id='".$_REQUEST['id']."' LIMIT 1");
				$db->query("DELETE FROM ".PRE."_newsletter_emails_cat WHERE eid='".$_REQUEST['id']."'");
				logit('NEWSLETTER_EDEL','ID #'.$_REQUEST['id']);
				printJSRedirect(get_index('newsletter.eshow'));
			}
		}
		else {
			list($title) = $db->first("SELECT email FROM ".PRE."_newsletter_emails WHERE id='".$_REQUEST['id']."' LIMIT 1");
			$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
			tmessageOverlay('deltitle',array('ID'=>$_REQUEST['id']),'/');
		}
	}
}



//***************************** Adresse aktivieren *****************************
function eenable() {
	global $set,$db,$apx;
	
	//Mehrere
	if ( is_array($_REQUEST['multiid']) ) {
		if ( !checkToken() ) printInvalidToken();
		else {
			$cache = array_map('intval', $_REQUEST['multiid']);
			if ( !count($cache) ) {
				header("HTTP/1.1 301 Moved Permanently");
				header('Location: '.get_index('newsletter.eshow'));
				return;
			}
			
			if ( count($cache) ) {
				$db->query("UPDATE ".PRE."_newsletter_emails_cat SET active=1 WHERE eid IN (".implode(",",$cache).")");
				foreach ( $cache AS $id ) logit('NEWSLETTER_EENABLE',"ID #".$id);
			}
			
			header("HTTP/1.1 301 Moved Permanently");
			header('Location: '.get_index('newsletter.eshow'));
		}
	}
	
	//Einzeln
	else {
		$_REQUEST['id']=(int)$_REQUEST['id'];
		if ( !$_REQUEST['id'] ) die('missing ID!');
		
		if ( $_POST['send']==1 ) {
			if ( !checkToken() ) printInvalidInfo();
			else {
				$db->query("UPDATE ".PRE."_newsletter_emails_cat SET active=1 WHERE eid='".$_REQUEST['id']."'");
				logit('NEWSLETTER_EENABLE','ID #'.$_REQUEST['id']);
				printJSRedirect(get_index('newsletter.eshow'));
			}
		}
		else {
			list($title) = $db->first("SELECT email FROM ".PRE."_newsletter_emails WHERE id='".$_REQUEST['id']."' LIMIT 1");
			$apx->tmpl->assign('MESSAGE', $apx->lang->get('MSG_TEXT', array('TITLE' => compatible_hsc($title))));
			tmessageOverlay('eenable',array('ID'=>$_REQUEST['id']));
		}
	}
}



//***************************** Adressen importieren *****************************
function eimport() {
	global $set,$db,$apx;
	
	if ( $_POST['catid'][0]=='all' || !isset($_POST['catid']) ) $_POST['catid']=array('all');
	
	if ( $_POST['send']==1 ) {
		
		$emails = explode("\n", $_POST['email']);
		$emails = array_map('trim', $emails);
		$wrongmails = array();
		foreach ( $emails AS $email ) {
			if ( !strlen($email) ) continue;
			if ( $email && !checkmail($email) ) {
				$wrongmails[] = $email;
			}
		}
		
		if ( !checkToken() ) infoInvalidToken();
		elseif ( !$_POST['catid'] ) infoNotComplete();
		elseif ( $wrongmails ) info($apx->lang->get('INFO_WRONGSYNTAX', array('EMAILS' => implode(', ', $wrongmails))));
		else {
			
			//Kategorien
			if ( $_POST['catid'][0]=='all' ) {
				$catids = array_keys($set['newsletter']['categories']);
			}
			else {
				$catids = array_unique(array_map('intval', $_POST['catid']));
			}
			
			foreach ( $emails AS $email ) {
				if ( !strlen($email) ) continue;
				list($aboId) = $db->first("SELECT id FROM ".PRE."_newsletter_emails WHERE email LIKE '".addslashes_like($email)."' LIMIT 1");
				
				//Email bereits vorhanden
				if ( $aboId ) {
					foreach ( $catids AS $catid ) {
						$db->query("
							INSERT IGNORE INTO ".PRE."_newsletter_emails_cat
							(eid, catid, active, html) VALUES
							('".$aboId."', '".$catid."', '1', '".($_POST['html'] ? true : false)."')
						");
						if ( $db->affected_rows()==0 ) {
							$db->query("
								UPDATE ".PRE."_newsletter_emails_cat
								SET active=1, html='".($_POST['html'] ? 1 : 0)."', incode=''
								WHERE eid='".$aboId."' AND catid='".$catid."'
								LIMIT 1
							");
						}
					}
				}
				
				//Neue Email
				else {
					$db->query("INSERT INTO ".PRE."_newsletter_emails (email) VALUES ('".addslashes($email)."')");
					$nid = $db->insert_id();
					foreach ( $catids AS $catid ) {
						$db->query("
							INSERT INTO ".PRE."_newsletter_emails_cat
							(eid, catid, active, html) VALUES
							('".$nid."', '".$catid."', '1', '".($_POST['html'] ? true : false)."')
						");
					}
				}
				
				logit('NEWSLETTER_EADD','ID #'.$nid);
			}
			
			logit('NEWSLETTER_EIMPORT');
			printJSRedirect(get_index('newsletter.eshow'));
		}
	}
	else {
		
		//Kategorien
		$catinfo=$set['newsletter']['categories'];
		asort($catinfo);
		$catlist='<option value="all"'.iif($_POST['catid'][0]=='all','selected="selected"').' style="font-weight:bold;">'.$apx->lang->get('ALL').'</option>';
		foreach ( $catinfo AS $id => $name ) {
			$catlist.='<option value="'.$id.'"'.iif(in_array($id,$_POST['catid']),'selected="selected"').'>'.replace($name).'</option>';
		}
		
		$apx->tmpl->assign('CATLIST',$catlist);
		$apx->tmpl->assign('EMAIL','');
		$apx->tmpl->assign('HTML',0);
		
		$apx->tmpl->parse('eimport');
	}
}



////////////////////////////////////////////////////////////////////////////////////////// -> KATEGORIEN

//***************************** Kategorien verwalten *****************************
function catshow() {
	global $set,$db,$apx,$html;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	$data=$set['newsletter']['categories'];
	
	//Kategorie löschen
	if ( $_REQUEST['do']=='del' && isset($data[$_REQUEST['id']]) ) {
		if ( isset($_POST['id']) ) {
			if ( !checkToken() ) printInvalidToken();
			else {
				unset($data[$_REQUEST['id']]);
				$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($data))."' WHERE module='newsletter' AND varname='categories' LIMIT 1");
				logit('NEWSLETTER_CATDEL',$_REQUEST['id']);
				printJSRedirect('action.php?action=newsletter.catshow');
			}
		}
		else {
			tmessageOverlay('catdel',array('ID'=>$_REQUEST['id']));
		}
		return;
	}
	
	
	//Kategorie bearbeiten
	elseif ( $_REQUEST['do']=='edit' && isset($data[$_REQUEST['id']]) ) {
		if ( $_POST['send'] ) {
			if ( !checkToken() ) infoInvalidToken();
			elseif ( !$_POST['title'] ) infoNotComplete();
			else {
				$data[$_REQUEST['id']]=$_POST['title'];
				$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($data))."' WHERE module='newsletter' AND varname='categories' LIMIT 1");
				logit('NEWSLETTER_CATEDIT',$_REQUEST['id']);
				printJSRedirect('action.php?action=newsletter.catshow');
			}
		}
		else {
			$_POST['title']=$data[$_REQUEST['id']];
			
			$apx->tmpl->assign('TITLE',$_POST['title']);
			$apx->tmpl->assign('ACTION','edit');
			$apx->tmpl->assign('ID',$_REQUEST['id']);
			$apx->tmpl->parse('catadd_catedit');
		}
	}
	
	//Kategorie erstellen
	elseif ( $_REQUEST['do']=='add' ) {
		if ( $_POST['send'] ) {
			if ( !checkToken() ) infoInvalidToken();
			elseif ( !$_POST['title'] ) infoNotComplete();
			else {
				$data[]=$_POST['title'];
				$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($data))."' WHERE module='newsletter' AND varname='categories' LIMIT 1");
				logit('NEWSLETTER_CATADD',array_key_max($data));
				printJSRedirect('action.php?action=newsletter.catshow');
			}
		}
		return;
	}
	
	else {
		$apx->tmpl->assign('ACTION','add');
		$apx->tmpl->parse('catadd_catedit');
	}
	
	
	////////// AUFLISTUNG
	
	$col[]=array('COL_TITLE',100,'class="title"');
	
	asort($data);
	if ( count($data) ) {
		foreach ( $data AS $id => $name ) {
			++$i;
			
			$tabledata[$i]['COL1']=replace($name);
			
			$tabledata[$i]['OPTIONS'].=optionHTML('edit.gif', 'newsletter.catshow', 'do=edit&id='.$id, $apx->lang->get('CORE_EDIT'));
			$tabledata[$i]['OPTIONS'].=optionHTMLOverlay('del.gif', 'newsletter.catshow', 'do=del&id='.$id, $apx->lang->get('CORE_DEL'));
		}
	}
	
	$apx->tmpl->assign('TABLE',$tabledata);
	$html->table($col);
}


} //END CLASS

?>
