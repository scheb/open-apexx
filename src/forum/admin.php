<?php 

define('APXRUN',true);
define('INFORUM',true);
define('BASEREL','../');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('../lib/_start.php');  ///////////////////////////////////////////////////////// SYSTEMSTART ///
require('lib/_start.php');     /////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////


/***********************************************************************************************/
/*************************************** Beitrag bearbeiten ************************************/
/***********************************************************************************************/
if ( $_REQUEST['action']=='editpost' ) {
	$apx->lang->drop('postform');
	$apx->lang->drop('editor');
	
	$_REQUEST['id']=(int)$_REQUEST['id'];
	$_REQUEST['quote']=(int)$_REQUEST['quote'];
	if ( !$_REQUEST['id'] ) die('missing post-ID!');
	
	$postinfo=post_info($_REQUEST['id']);
	if ( !$postinfo['postid'] || $postinfo['del'] ) message($apx->lang->get('MSG_POSTNOTEXIST'));
	$threadinfo=thread_info($postinfo['threadid']);
	if ( !$threadinfo['threadid'] || $threadinfo['del'] ) message($apx->lang->get('MSG_THREADNOTEXIST'));
	$foruminfo=forum_info($threadinfo['forumid']);
	if ( !$foruminfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
	if ( !forum_access_editpost($foruminfo,$threadinfo,$postinfo) ) tmessage('noright',array(),false,false);
	
	//Ist der Beitrag der erste Beitrag im Thema?
	if ( $threadinfo['firstpost']==$postinfo['postid'] ) $firstpost=true;
	else $firstpost=false;
	
	
	//VORSCHAU
	if ( $_POST['preview'] ) {
		$preview=$_POST['text'];
		if ( $_POST['transform_links'] ) $preview=transform_urls($preview);
		$preview=forum_replace($preview,$_POST['allowcodes'],$_POST['allowsmilies']);
		$apx->tmpl->assign('PREVIEW',$preview);
	}
	
	
	//AKTION AUSFÜHREN
	elseif ( $_POST['send'] ) {
		if ( !$_POST['text'] || ( $firstpost && !$_POST['title'] ) || ( !$postinfo['userid'] && !$_POST['username'] ) || ( $firstpost && $_POST['sticky_type']=='own' && !$_POST['sticky_text'] ) ) message('back');
		else {
			
			//Benutzername
			if ( !$postinfo['userid'] ) {
				$addpostfield='username,';
				$addthreadfield='opener,';
				$_POST['opener']=$_POST['username'];
			}
			
			//Links parsen
			if ( $_POST['transform_links'] ) {
				$_POST['text']=transform_urls($_POST['text']);
			}
			
			//Sticky
			if ( forum_access_announce($foruminfo) ) {
				if ( $_POST['sticky_type'] && $_POST['sticky_type']!='no' ) {
					$_POST['sticky']=1;
					if ( $_POST['sticky_type']=='announcement' ) $_POST['sticky_text']=$apx->lang->get('ANNOUNCEMENT');
					if ( $_POST['sticky_type']=='important' ) $_POST['sticky_text']=$apx->lang->get('IMPORTANT');
				}
				else {
					$_POST['sticky']=0;
					$_POST['sticky_text']='';
				}
				$addthreadfield.='sticky,sticky_text,';
			}
			
			//Thema aktualisieren, wenn erstes Posting bearbeitet wird
			if ( $firstpost ) {
				$_POST['icon']=iif($_POST['icon'] && $_POST['icon']!='none',$_POST['icon'],-1);
				$db->dupdate(PRE.'_forum_threads',$addthreadfield.'prefix,title,icon',"WHERE threadid='".$threadinfo['threadid']."' LIMIT 1");
			}
			
			//Letzte Bearbeitung eintragen
			if ( $postinfo['time']+10*60<time() ) {
				$addpostfield.='lastedit_by,lastedit_time,';
				$_POST['lastedit_by']=$user->info['username'];
				$_POST['lastedit_time']=time();
			}
			
			//Posting aktualisieren
			$db->dupdate(PRE.'_forum_posts',$addpostfield.'title,text,allowsmilies,allowcodes,allowsig',"WHERE postid='".$postinfo['postid']."' LIMIT 1");
			
			//Thema/Forum aktualisieren
			thread_update_cache($threadinfo['threadid']);
			forum_update_cache($foruminfo['forumid']);
			
			//Index aktualisieren
			if ( $foruminfo['searchable'] ) {
				update_index($_POST['text'],$threadinfo['threadid'],$postinfo['postid']);
				update_index($_POST['title'],$threadinfo['threadid'],$postinfo['postid'],true);
			}
			
			//Zur vorherigen Seite gehen
			$goto=mkrellink(
				'thread.php?id='.$threadinfo['threadid'].'&amp;postid='.$postinfo['postid'],
				'thread.php?id='.$threadinfo['threadid'].'&amp;postid='.$postinfo['postid']
			);
			
			message($apx->lang->get('MSG_POST_EDIT_OK'),$goto);
		}
	}
	else {
		$_POST['username']=$postinfo['username'];
		$_POST['prefix']=$threadinfo['prefix'];
		$_POST['title']=$postinfo['title'];
		$_POST['text']=$postinfo['text'];
		$_POST['allowcodes']=$postinfo['allowcodes'];
		$_POST['allowsmilies']=$postinfo['allowsmilies'];
		$_POST['allowsig']=$postinfo['allowsig'];
		$_POST['transform_links']=1;
		$_POST['hash']=$postinfo['hash'];
		
		//Sticky und Icons, wenn Startbeitrag
		if ( $firstpost ) {
			
			//Themenicon
			if ( intval($threadinfo['icon'])>=0 ) $_POST['icon']=(int)$threadinfo['icon'];
			else $_POST['icon']='none';
			
			//Sticky
			if ( !$threadinfo['sticky'] ) $_POST['sticky_type']='no';
			elseif ( $threadinfo['sticky_text']==$apx->lang->get('ANNOUNCEMENT') ) {
				$_POST['sticky_type']='announcement';
			}
			elseif ( $threadinfo['sticky_text']==$apx->lang->get('IMPORTANT') ) {
				$_POST['sticky_type']='important';
			}
			else {
				$_POST['sticky_type']='own';
				$_POST['sticky_text']=$threadinfo['sticky_text'];
			}
			
		}
	}
	
	
	//Themenicons
	if ( $firstpost ) {
		$icons=$set['forum']['icons'];
		if ( is_array($icons) ) $icons=array_sort($icons,'ord','ASC');
		if ( count($icons) ) {
			foreach ( $icons AS $key => $res ) {
				++$ii;
				$icondata[$ii]['ID']=$key;
				$icondata[$ii]['IMAGE']=$res['file'];
			}
		}
	}
	
	//Smilies
	if ( count($set['main']['smilies']) ) {
		foreach ( $set['main']['smilies'] AS $res ) {
			++$si;
			$smiledata[$si]['INSERTCODE']=addslashes($res['code']);
			if ( $res['file'][0]!='/' && defined('BASEREL') ) $smiledata[$si]['IMAGE']=BASEREL.$res['file'];
			else $smiledata[$si]['IMAGE']=$res['file'];
			if ( $si==16 ) break;
		}
	}
	
	//Dateitypen
	$filetypes=array();
	$typeinfo=array();
	$data=$db->fetch("SELECT * FROM ".PRE."_forum_filetypes ORDER BY ext ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			$filetypes[]=$res['ext'];
			$typeinfo[$res['ext']]=array($res['size']*1024,$res['icon']);
		}
	}
	
	//Anhänge auslesen
	$attachments='';
	$data=$db->fetch("SELECT * FROM ".PRE."_forum_attachments WHERE ( postid='".$postinfo['postid']."' AND hash='".addslashes($postinfo['hash'])."' ) ORDER BY name ASC");
	if ( count($data) ) {
		require(BASEDIR.'lib/class.mediamanager.php');
		$mm=new mediamanager;
		foreach ( $data AS $res ) {
			$ext=strtolower($mm->getext($res['name']));
			$attachments.='<img src="'.$typeinfo[$ext][1].'" alt="" style="vertical-align:middle;" /> '.$res['name'].' ('.round($res['size']/1024).' KB)';
		}
	}
	
	//Präfixe
	$prefixdata = array();
	$prefixInfo = forum_prefixes($foruminfo['forumid']);
	foreach ( $prefixInfo AS $prefix ) {
		$prefixdata[] = array(
			'ID' => $prefix['prefixid'],
			'TITLE' => compatible_hsc($prefix['title']),
			'SELECTED' => $_POST['prefix']==$prefix['prefixid']
		);
	}
	
	$apx->tmpl->assign('USERID',$postinfo['userid']);
	if ( $postinfo['userid'] ) $apx->tmpl->assign('USERNAME',replace($postinfo['username']));
	else $apx->tmpl->assign('USERNAME',compatible_hsc($_POST['username']));
	$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
	$apx->tmpl->assign('PREFIX',$prefixdata);
	$apx->tmpl->assign('TEXT',compatible_hsc($_POST['text']));
	$apx->tmpl->assign('ICON',iif($_POST['icon']==='none',$_POST['icon'],(int)$_POST['icon']));
	$apx->tmpl->assign('ICONLIST',$icondata);
	$apx->tmpl->assign('SMILEYLIST',$smiledata);
	$apx->tmpl->assign('STICKY_TYPE',compatible_hsc($_POST['sticky_type']));
	$apx->tmpl->assign('STICKY_TEXT',compatible_hsc($_POST['sticky_text']));
	$apx->tmpl->assign('TRANSFORM_LINKS',(int)$_POST['transform_links']);
	$apx->tmpl->assign('ATTACHMENTS',$attachments);
	$apx->tmpl->assign('ATTACHMENT_TYPES',implode(', ',$filetypes));
	$apx->tmpl->assign('ALLOWCODES',(int)$_POST['allowcodes']);
	$apx->tmpl->assign('ALLOWSMILIES',(int)$_POST['allowsmilies']);
	$apx->tmpl->assign('ALLOWSIG',(int)$_POST['allowsig']);
	$apx->tmpl->assign('SET_CODES',$set['forum']['codes']);
	$apx->tmpl->assign('SET_SMILIES',$set['forum']['smilies']);

	$apx->tmpl->assign('FIRST',$firstpost);
	$apx->tmpl->assign('ANNOUNCE',forum_access_announce($foruminfo));
	$apx->tmpl->assign('ATTACH',forum_access_addattachment($foruminfo));
	$apx->tmpl->assign('ID',$postinfo['postid']);
	$apx->tmpl->assign('HASH',$_POST['hash']);
	
	$apx->tmpl->parse('editpost');
	
	////////////////////////////////////////////
	
	$threadpath=array(array(
		'TITLE' => replace($threadinfo['title']),
		'LINK' => mkrellink(
			'thread.php?id='.$threadinfo['threadid'],
			'thread,'.$threadinfo['threadid'].',1'.urlformat($threadinfo['title']).'.html'
		)
	));
	
	$apx->tmpl->assign('PATH',array_merge(forum_path($foruminfo,1),$threadpath));
	$apx->tmpl->assign('PATHEND',$apx->lang->get('HEADLINE_EDITPOST'));
	titlebar($apx->lang->get('HEADLINE_EDITPOST'));
}



/***********************************************************************************************/
/*************************************** Beitrag löschen ***************************************/
/***********************************************************************************************/
elseif ( $_REQUEST['action']=='delpost' ) {
	$apx->lang->drop('admin');
	
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing post-ID!');
	
	$postinfo=post_info($_REQUEST['id']);
	if ( !$postinfo['postid'] ) message($apx->lang->get('MSG_POSTNOTEXIST'));
	$threadinfo=thread_info($postinfo['threadid']);
	if ( !$threadinfo['threadid'] ) message($apx->lang->get('MSG_THREADNOTEXIST'));
	$foruminfo=forum_info($threadinfo['forumid']);
	if ( !$foruminfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
	if ( $threadinfo['del'] && !( $user->info['userid'] && ( $user->is_admin() || in_array($user->info['userid'], $foruminfo['moderator']) ) ) ) message($apx->lang->get('MSG_THREADNOTEXIST'));
	if ( $postinfo['del'] && !( $user->info['userid'] && ( $user->is_admin() || in_array($user->info['userid'], $foruminfo['moderator']) ) ) ) message($apx->lang->get('MSG_POSTNOTEXIST'));
	if ( !forum_access_delpost($foruminfo,$threadinfo,$postinfo) ) tmessage('noright',array(),false,false);
	if ( $threadinfo['firstpost']==$postinfo['postid'] ) die('can not delete first post!');
	
	
	//AKTION AUSFÜHREN
	if ( $_POST['send'] && $_POST['id'] ) {
		
		/* Postingzahlen des Benutzers nicht verringern => Posting wurden vielleicht gar nicht gezählt
		$db->query("UPDATE ".PRE."_user SET forum_posts=forum_posts-1 WHERE userid='".$postinfo['userid']."' LIMIT 1");
		*/
		
		//Beitrag löschen
		if ( $_POST['realdel'] && $user->info['userid'] && ( $user->is_admin() || in_array($user->info['userid'], $foruminfo['moderator']) ) ) {
			
			//Anhänge löschen
			$data = $db->fetch("
				SELECT a.id, a.file
				FROM ".PRE."_forum_attachments AS a
				WHERE a.postid='".$postinfo['postid']."'
			");
			$attIds = get_ids($data, 'id');
			if ( count($data) ) {
				foreach ( $data AS $res ) {
					if ( file_exists(BASEDIR.getpath('uploads').$res['file']) ) {
						@unlink(BASEDIR.getpath('uploads').$res['file']);
					}
				}
				$db->query("DELETE FROM ".PRE."_forum_attachments WHERE id IN (".implode(',', $attIds).")");
			}
			
			//SQL löschen
			$db->query("DELETE FROM ".PRE."_forum_posts WHERE postid='".$postinfo['postid']."' LIMIT 1");
		}
		else {
			$db->query("UPDATE ".PRE."_forum_posts SET del=1 WHERE postid='".$postinfo['postid']."' LIMIT 1");
		}
		
		//Folgende Beiträge zählen
		list($follow)=$db->first("SELECT count(postid) FROM ".PRE."_forum_posts WHERE ( threadid='".$threadinfo['threadid']."' AND time>'".$postinfo['time']."' AND del!=1 )");
		$previous=$threadinfo['posts']-$follow; //Beiträge vor dem gelöschten Beitrag
		
		//Wenn es keine Beiträge vor dem Posting gibt LASTPOST aktualisieren
		//Nur wenn der Beitrag noch nicht gelöscht war
		if ( !$postinfo['del'] ) {
			if ( !$follow ) {
				thread_update_cache($threadinfo['threadid'], -1);
				forum_update_cache($foruminfo['forumid'], -1);
			}
			
			//Ansonsten einfach Beitragszahl -1
			else {
				$db->query("UPDATE ".PRE."_forum_threads SET posts=posts-1 WHERE threadid='".$threadinfo['threadid']."' LIMIT 1");
				$db->query("UPDATE ".PRE."_forums SET posts=posts-1 WHERE forumid='".$foruminfo['forumid']."' LIMIT 1");
			}
		}
		
		//Wörter aus dem Index löschen
		$db->query("DELETE FROM ".PRE."_forum_index WHERE postid='".$postinfo['postid']."'");
		
		//Zur vorherigen Seite gehen
		$goto=mkrellink(
			'thread.php?id='.$threadinfo['threadid'].'&amp;postid='.$postinfo['postid'],
			'thread.php?id='.$threadinfo['threadid'].'&amp;postid='.$postinfo['postid']
		);
		
		message($apx->lang->get('MSG_DELPOST_OK'),$goto);
	}
	else {
		$apx->tmpl->assign('ID', $postinfo['postid']);
		$apx->tmpl->assign('REAL', $postinfo['del']);
		$apx->tmpl->assign('REALDEL_ALLOWED', $user->info['userid'] && ( $user->is_admin() || in_array($user->info['userid'], $foruminfo['moderator'])));
		tmessage('delpost');
	}
}



/***********************************************************************************************/
/********************************* Mehrere Beiträge löschen ************************************/
/***********************************************************************************************/
elseif ( $_REQUEST['action']=='delposts' ) {
	$apx->lang->drop('admin');
	$apx->lang->drop('postform');
	
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing thread-ID!');
	$_REQUEST['p']=(int)$_REQUEST['p'];
	$_POST['forum']=(int)$_POST['forum'];
	
	$threadinfo=thread_info($_REQUEST['id']);
	if ( !$threadinfo['threadid'] || $threadinfo['del'] ) message($apx->lang->get('MSG_THREADNOTEXIST'));
	$foruminfo=forum_info($threadinfo['forumid']);
	if ( !$foruminfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
	if ( !forum_access_admin($foruminfo) ) tmessage('noright',array(),false,false);
	
	//Sicherstellen, dass es sich um IDs handelt
	if ( !is_array($_POST['post']) ) $_POST['post']=array();
	foreach ( $_POST['post'] AS $key => $value ) {
		$_POST['post'][$key]=intval($value);
		if ( !$_POST['post'][$key] ) unset($_POST['post'][$key]);
	}
	
	//ABSENDEN
	if ( $_POST['send'] ) {
		if ( !count($_POST['post']) ) message($apx->lang->get('CORE_BACK'),'back');
		else {
			
			//Beiträge löschen
			if ( $_POST['realdel'] ) {
				$postidsData = $db->fetch("SELECT postid FROM ".PRE."_forum_posts WHERE ( postid IN (".implode(',',$_POST['post']).") AND threadid='".$threadinfo['threadid']."' )");
				$postids = get_ids($postidsData, 'postid');
				$postids[] = -1;
				
				//Anhänge löschen
				$data = $db->fetch("
					SELECT a.id, a.file
					FROM ".PRE."_forum_attachments AS a
					WHERE a.postid IN (".implode(',', $postids).")
				");
				$attIds = get_ids($data, 'id');
				if ( count($data) ) {
					foreach ( $data AS $res ) {
						if ( file_exists(BASEDIR.getpath('uploads').$res['file']) ) {
							@unlink(BASEDIR.getpath('uploads').$res['file']);
						}
					}
					$db->query("DELETE FROM ".PRE."_forum_attachments WHERE id IN (".implode(',', $attIds).")");
				}
				
				//SQL löschen
				$db->query("DELETE FROM ".PRE."_forum_posts WHERE postid IN (".implode(',', $postids).")");
			}
			else {
				$db->query("UPDATE ".PRE."_forum_posts SET del='1' WHERE ( postid IN (".implode(',',$_POST['post']).") AND del=0 AND threadid='".$threadinfo['threadid']."' )");
			}
			$delposts = $db->affected_rows();
			
			//Thema/Forum aktualisieren
			if ( $delposts ) {
				thread_update_cache($threadinfo['threadid'], -$delposts);
				forum_update_cache($foruminfo['forumid'], -$delposts);
			}
			
			//Suchindex anpassen
			$db->query("DELETE FROM ".PRE."_forum_index WHERE postid IN (".implode(',',$_POST['post']).")");
			
			$goto=mkrellink(
				'thread.php?id='.$threadinfo['threadid'],
				'thread,'.$threadinfo['threadid'].',1'.urlformat($threadinfo['title']).'.html'
			);
			
			message($apx->lang->get('MSG_DELPOSTS_OK'),$goto);
			require('lib/_end.php');
			require('../lib/_end.php');
		}
	}
	
	//VOREINSTELLUNGEN
	elseif ( !$_POST['p'] ) {
		$_REQUEST['p']=1;
		$_POST['post']=array();
	}
	
	//Seitenzahlen
	list($count)=$db->first("SELECT count(postid) FROM ".PRE."_forum_posts WHERE ( del=0 AND threadid='".$threadinfo['threadid']."' )");
	$pages=ceil($count/($user->info['forum_ppp']*2));
	if ( $_REQUEST['p']<1 ) $_REQUEST['p']=1;
	if ( $_REQUEST['p']>$pages ) $_REQUEST['p']=$pages;
	
	//Beiträge auflisten
	$inlist=array();
	$data=$db->fetch("SELECT postid,username,text,time FROM ".PRE."_forum_posts WHERE ( del=0 AND threadid='".$threadinfo['threadid']."' ) ORDER BY time DESC LIMIT ".($user->info['forum_ppp']*2*($_REQUEST['p']-1)).','.($user->info['forum_ppp']*2));
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Text
			$text=clear_codes($res['text']);
			$text=replace($text,1);
			
			$postdata[$i]['ID']=$res['postid'];
			$postdata[$i]['USERNAME']=replace($res['username']);
			$postdata[$i]['TEXT']=$text;
			$postdata[$i]['TIME']=$res['time'];
			$postdata[$i]['SELECTED']=iif(in_array($res['postid'],$_POST['post']),1,0);
			$postdata[$i]['FIRST']=$res['postid']==$threadinfo['firstpost'];
			$inlist[]=$res['postid'];
		}
	}
	
	//Alle IDs, die nicht aufgelistet wurden
	$notinlist=array_diff($_POST['post'],$inlist);
	foreach ( $notinlist AS $id ) {
		$seldata[]['ID']=$id;
	}
	
	$apx->tmpl->assign('FORUM',$forumdata);
	$apx->tmpl->assign('POST',$postdata);
	$apx->tmpl->assign('SELPOST',$seldata);
	$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
	$apx->tmpl->assign('ICON',$_POST['icon']);
	$apx->tmpl->assign('ICONLIST',$icondata);
	$apx->tmpl->assign('STICKY_TYPE',compatible_hsc($_POST['sticky_type']));
	$apx->tmpl->assign('STICKY_TEXT',compatible_hsc($_POST['sticky_text']));
	$apx->tmpl->assign('ANNOUNCE',forum_access_announce($foruminfo));
	$apx->tmpl->assign('ID',$threadinfo['threadid']);
	
	$apx->tmpl->assign('P',$_REQUEST['p']);
	$apx->tmpl->assign('PREVIOUS',iif($_REQUEST['p']>1,1,0));
	$apx->tmpl->assign('NEXT',iif($_REQUEST['p']<$pages,1,0));
	
	$apx->tmpl->parse('deleteposts');
	
	////////////////////////////////////////////
	
	$threadpath=array(array(
		'TITLE' => replace($threadinfo['title']),
		'LINK' => mkrellink(
			'thread.php?id='.$threadinfo['threadid'],
			'thread,'.$threadinfo['threadid'].',1'.urlformat($threadinfo['title']).'.html'
		)
	));
	
	$apx->tmpl->assign('PATH',array_merge(forum_path($foruminfo,1),$threadpath));
	$apx->tmpl->assign('PATHEND',$apx->lang->get('DELPOSTS'));
	titlebar($apx->lang->get('DELPOSTS'));
}



/***********************************************************************************************/
/********************************** Beiträge zusammenfassen ************************************/
/***********************************************************************************************/
elseif ( $_REQUEST['action']=='mergeposts' ) {
	$apx->lang->drop('admin');
	$apx->lang->drop('postform');
	
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing thread-ID!');
	$_REQUEST['p']=(int)$_REQUEST['p'];
	$_POST['forum']=(int)$_POST['forum'];
	
	$threadinfo=thread_info($_REQUEST['id']);
	if ( !$threadinfo['threadid'] || $threadinfo['del'] ) message($apx->lang->get('MSG_THREADNOTEXIST'));
	$foruminfo=forum_info($threadinfo['forumid']);
	if ( !$foruminfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
	if ( !forum_access_admin($foruminfo) ) tmessage('noright',array(),false,false);
	
	//Sicherstellen, dass es sich um IDs handelt
	if ( !is_array($_POST['post']) ) $_POST['post']=array();
	foreach ( $_POST['post'] AS $key => $value ) {
		$_POST['post'][$key]=intval($value);
		if ( !$_POST['post'][$key] ) unset($_POST['post'][$key]);
	}
	
	//ABSENDEN
	if ( $_POST['send'] ) {
		if ( !count($_POST['post']) ) message($apx->lang->get('CORE_BACK'),'back');
		else {
			$posts = $db->fetch("SELECT postid, text, hash FROM ".PRE."_forum_posts WHERE threadid='".$threadinfo['threadid']."' AND del=0 AND postid IN (".implode(',',$_POST['post']).") ORDER BY time ASC");
			if ( count($posts)>1 ) {
				$newtext = '';
				foreach ( $posts AS $post ) {
					$newtext .= ($newtext ? "\n\n" : '').$post['text'];
				}
				$newpost = array_pop($posts);
				$newpost['text'] = $newtext;
				$delids = get_ids($posts, 'postid');
				
				//Neuen Post aktualisieren
				$db->query("UPDATE ".PRE."_forum_posts SET text='".addslashes($newpost['text'])."' WHERE postid='".$newpost['postid']."' LIMIT 1");
				
				//Anhänge verschieben
				$db->query("UPDATE ".PRE."_forum_attachments SET postid='".$newpost['postid']."',hash='".addslashes($newpost['hash'])."' WHERE postid IN (".implode(',',$delids).")");
				
				//Alte Posts löschen
				$db->query("DELETE FROM ".PRE."_forum_posts WHERE postid IN (".implode(',',$delids).")");
				$db->query("DELETE FROM ".PRE."_forum_index WHERE postid IN (".implode(',',$delids).")");
				
				//Suchindex aktualisieren
				update_index($newpost['text'],$threadinfo['threadid'],$newpost['postid']);
				
				//Thema/Forum aktualisieren
				thread_update_cache($threadinfo['threadid'], -count($delids));
				forum_update_cache($foruminfo['forumid'], -count($delids));
			}
			
			if ( $newpost ) {
				$goto=mkrellink(
					'thread.php?id='.$threadinfo['threadid'].'&amp;postid='.$newpost['postid'],
					'thread.php?id='.$threadinfo['threadid'].'&amp;postid='.$newpost['postid']
				);
			}
			else {
				$goto=mkrellink(
					'thread.php?id='.$threadinfo['threadid'],
					'thread,'.$threadinfo['threadid'].',1'.urlformat($threadinfo['title']).'.html'
				);
			}
			
			message($apx->lang->get('MSG_MERGEPOSTS_OK'),$goto);
			require('lib/_end.php');
			require('../lib/_end.php');
		}
	}
	
	//VOREINSTELLUNGEN
	elseif ( !$_POST['p'] ) {
		$_REQUEST['p']=1;
		$_POST['post']=array();
	}
	
	//Seitenzahlen
	list($count)=$db->first("SELECT count(postid) FROM ".PRE."_forum_posts WHERE ( del=0 AND threadid='".$threadinfo['threadid']."' )");
	$pages=ceil($count/($user->info['forum_ppp']*2));
	if ( $_REQUEST['p']<1 ) $_REQUEST['p']=1;
	if ( $_REQUEST['p']>$pages ) $_REQUEST['p']=$pages;
	
	//Beiträge auflisten
	$inlist=array();
	$data=$db->fetch("SELECT postid,username,text,time FROM ".PRE."_forum_posts WHERE ( del=0 AND threadid='".$threadinfo['threadid']."' ) ORDER BY time DESC LIMIT ".($user->info['forum_ppp']*2*($_REQUEST['p']-1)).','.($user->info['forum_ppp']*2));
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Text
			$text=clear_codes($res['text']);
			$text=replace($text,1);
			
			$postdata[$i]['ID']=$res['postid'];
			$postdata[$i]['USERNAME']=replace($res['username']);
			$postdata[$i]['TEXT']=$text;
			$postdata[$i]['TIME']=$res['time'];
			$postdata[$i]['SELECTED']=iif(in_array($res['postid'],$_POST['post']),1,0);
			$postdata[$i]['FIRST']=$res['postid']==$threadinfo['firstpost'];
			$inlist[]=$res['postid'];
		}
	}
	
	//Alle IDs, die nicht aufgelistet wurden
	$notinlist=array_diff($_POST['post'],$inlist);
	foreach ( $notinlist AS $id ) {
		$seldata[]['ID']=$id;
	}
	
	$apx->tmpl->assign('FORUM',$forumdata);
	$apx->tmpl->assign('POST',$postdata);
	$apx->tmpl->assign('SELPOST',$seldata);
	$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
	$apx->tmpl->assign('ICON',$_POST['icon']);
	$apx->tmpl->assign('ICONLIST',$icondata);
	$apx->tmpl->assign('STICKY_TYPE',compatible_hsc($_POST['sticky_type']));
	$apx->tmpl->assign('STICKY_TEXT',compatible_hsc($_POST['sticky_text']));
	$apx->tmpl->assign('ANNOUNCE',forum_access_announce($foruminfo));
	$apx->tmpl->assign('ID',$threadinfo['threadid']);
	
	$apx->tmpl->assign('P',$_REQUEST['p']);
	$apx->tmpl->assign('PREVIOUS',iif($_REQUEST['p']>1,1,0));
	$apx->tmpl->assign('NEXT',iif($_REQUEST['p']<$pages,1,0));
	
	$apx->tmpl->parse('mergeposts');
	
	////////////////////////////////////////////
	
	$threadpath=array(array(
		'TITLE' => replace($threadinfo['title']),
		'LINK' => mkrellink(
			'thread.php?id='.$threadinfo['threadid'],
			'thread,'.$threadinfo['threadid'].',1'.urlformat($threadinfo['title']).'.html'
		)
	));
	
	$apx->tmpl->assign('PATH',array_merge(forum_path($foruminfo,1),$threadpath));
	$apx->tmpl->assign('PATHEND',$apx->lang->get('MERGEPOSTS'));
	titlebar($apx->lang->get('MERGEPOSTS'));
}



/***********************************************************************************************/
/********************************* Beitrag wiederherstellen ************************************/
/***********************************************************************************************/
elseif ( $_REQUEST['action']=='recoverpost' ) {
	$apx->lang->drop('admin');
	
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing post-ID!');
	
	$postinfo=post_info($_REQUEST['id']);
	if ( !$postinfo['postid'] ) message($apx->lang->get('MSG_POSTNOTEXIST'));
	$threadinfo=thread_info($postinfo['threadid']);
	if ( !$threadinfo['threadid'] ) message($apx->lang->get('MSG_THREADNOTEXIST'));
	$foruminfo=forum_info($threadinfo['forumid']);
	if ( !$foruminfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
	if ( !forum_access_recoverpost($foruminfo,$threadinfo,$postinfo) ) tmessage('noright',array(),false,false);
	
	
	//AKTION AUSFÜHREN
	if ( $_POST['send'] && $_POST['id'] ) {
		
		//Beitrag wiederherstellen
		$db->query("UPDATE ".PRE."_forum_posts SET del=0 WHERE postid='".$postinfo['postid']."' LIMIT 1");
		
		//Anzahl Beiträge und LASTPOST aktualisieren
		thread_update_cache($threadinfo['threadid'], 1);
		forum_update_cache($foruminfo['forumid'], 1);
		
		//Wörter wieder in den Index eintragen
		if ( $foruminfo['searchable'] ) {
			update_index($postinfo['text'],$threadinfo['threadid'],$postinfo['postid']);
			update_index($postinfo['title'],$threadinfo['threadid'],$postinfo['postid'],true);
		}
		
		//Zur vorherigen Seite gehen
		$goto=mkrellink(
			'thread.php?id='.$threadinfo['threadid'].'&amp;postid='.$postinfo['postid'],
			'thread.php?id='.$threadinfo['threadid'].'&amp;postid='.$postinfo['postid']
		);
		
		message($apx->lang->get('MSG_RECOVERPOST_OK'),$goto);
	}
	else {
		$apx->tmpl->assign('ID', $postinfo['postid']);
		tmessage('recoverpost');
	}
}



/***********************************************************************************************/
/*************************************** Thema löschen *****************************************/
/***********************************************************************************************/
elseif ( $_REQUEST['action']=='delthread' ) {
	$apx->lang->drop('admin');

	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing thread-ID!');
	
	$threadinfo=thread_info($_REQUEST['id']);
	if ( !$threadinfo['threadid'] ) message($apx->lang->get('MSG_THREADNOTEXIST'));
	$foruminfo=forum_info($threadinfo['forumid']);
	if ( !$foruminfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
	if ( $threadinfo['del'] && !( $user->info['userid'] && ( $user->is_admin() || in_array($user->info['userid'], $foruminfo['moderator']) ) ) ) message($apx->lang->get('MSG_THREADNOTEXIST'));
	if ( !forum_access_delthread($foruminfo,$threadinfo) ) tmessage('noright',array(),false,false);
	
	
	//AKTION AUSFÜHREN
	if ( $_POST['send'] && $_POST['id'] ) {
		
		/* Postingzahlen der Benutzer nicht verringern => Postings wurden vielleicht gar nicht gezählt
		$data=$db->fetch("SELECT count(postid) AS posts,userid FROM ".PRE."_forum_posts WHERE ( threadid='".$threadinfo['threadid']."' AND userid!=0 AND del=0 )");
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				$db->query("UPDATE ".PRE."_user SET forum_posts=forum_posts-".$res['posts']." WHERE userid='".$res['userid']."' LIMIT 1");
			}
		}*/
		
		//Thema und Beiträge löschen
		if ( $_POST['realdel'] && $user->info['userid'] && ( $user->is_admin() || in_array($user->info['userid'], $foruminfo['moderator']) ) ) {
			
			//Anhänge löschen
			$data = $db->fetch("
				SELECT a.id, a.file
				FROM ".PRE."_forum_attachments AS a
				LEFT JOIN ".PRE."_forum_posts AS p USING(postid)
				WHERE p.threadid='".$threadinfo['threadid']."'
			");
			$attIds = get_ids($data, 'id');
			if ( count($data) ) {
				foreach ( $data AS $res ) {
					if ( file_exists(BASEDIR.getpath('uploads').$res['file']) ) {
						@unlink(BASEDIR.getpath('uploads').$res['file']);
					}
				}
				$db->query("DELETE FROM ".PRE."_forum_attachments WHERE id IN (".implode(',', $attIds).")");
			}
			
			//SQL löschen
			$db->query("DELETE FROM ".PRE."_forum_threads WHERE threadid='".$threadinfo['threadid']."' LIMIT 1");
			$db->query("DELETE FROM ".PRE."_forum_threads WHERE moved='".$threadinfo['threadid']."' LIMIT 1");
			$db->query("DELETE FROM ".PRE."_forum_posts WHERE threadid='".$threadinfo['threadid']."'");
		}
		else {
			$db->query("UPDATE ".PRE."_forum_threads SET del=1 WHERE threadid='".$threadinfo['threadid']."' LIMIT 1");
			$db->query("DELETE FROM ".PRE."_forum_threads WHERE moved='".$threadinfo['threadid']."' LIMIT 1");
			
			//Beiträge werden nicht explizit als gelöscht markiert
			//So ist Wiederherstellung im Ursprungszustand möglich!
			//$db->query("UPDATE ".PRE."_forum_posts SET del=1 WHERE threadid='".$threadinfo['threadid']."'");
		}
		
		//Themenzahl und Beitragszahl im Forum verringern UND letztes Posting neu festlegen
		//Nur wenn das Thema noch nicht gelöscht wurde
		if ( !$threadinfo['del'] ) {
			forum_update_cache($foruminfo['forumid'], -$threadinfo['posts'], -1);
		}
		
		//Wörter aus dem Index löschen
		$db->query("DELETE FROM ".PRE."_forum_index WHERE threadid='".$threadinfo['threadid']."'");
		
		//Abonnements löschen
		$db->query("DELETE FROM ".PRE."_forum_subscriptions WHERE type='thread' AND source='".$threadinfo['threadid']."'");
		
		//Zur Themenübersicht gehen
		$goto=mkrellink(
			'forum.php?id='.$foruminfo['forumid'],
			'forum,'.$foruminfo['forumid'].',1'.urlformat($foruminfo['title']).'.html'
		);
		
		message($apx->lang->get('MSG_DELTHREAD_OK'),$goto);
	}
	else {
		$apx->tmpl->assign('ID', $threadinfo['threadid']);
		$apx->tmpl->assign('REAL', $threadinfo['del']);
		$apx->tmpl->assign('REALDEL_ALLOWED', $user->info['userid'] && ( $user->is_admin() || in_array($user->info['userid'], $foruminfo['moderator'])));
		tmessage('delthread');
	}
}



/***********************************************************************************************/
/********************************** Thema wiederherstellen *************************************/
/***********************************************************************************************/
elseif ( $_REQUEST['action']=='recoverthread' ) {
	$apx->lang->drop('admin');
	
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing thread-ID!');
	
	$threadinfo=thread_info($_REQUEST['id']);
	if ( !$threadinfo['threadid'] ) message($apx->lang->get('MSG_THREADNOTEXIST'));
	$foruminfo=forum_info($threadinfo['forumid']);
	if ( !$foruminfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
	if ( !forum_access_recoverthread($foruminfo,$threadinfo) ) tmessage('noright',array(),false,false);
	
	
	//AKTION AUSFÜHREN
	if ( $_POST['send'] && $_POST['id'] ) {
		
		//Thema wiederherstellen
		$db->query("UPDATE ".PRE."_forum_threads SET del=0 WHERE threadid='".$threadinfo['threadid']."' LIMIT 1");
		
		//Themenzahl und Beitragszahl im Forum erhöhen UND letztes Posting neu festlegen
		forum_update_cache($foruminfo['forumid'], $threadinfo['posts'], 1);
		
		//Wörter wieder in den Index eintragen
		if ( $foruminfo['searchable'] ) {
			$data = $db->fetch("
				SELECT postid, text, title
				FROM ".PRE."_forum_posts
				WHERE threadid='".$threadinfo['threadid']."' AND del=0
				ORDER BY time ASC
			");
			foreach ( $data AS $postinfo ) {
				update_index($postinfo['text'],$threadinfo['threadid'],$postinfo['postid']);
				update_index($postinfo['title'],$threadinfo['threadid'],$postinfo['postid'],true);
			}
		}
		
		//Zur Themenübersicht gehen
		$goto=mkrellink(
			'forum.php?id='.$foruminfo['forumid'],
			'forum,'.$foruminfo['forumid'].',1'.urlformat($foruminfo['title']).'.html'
		);
		
		message($apx->lang->get('MSG_RECOVERTHREAD_OK'),$goto);
	}
	else {
		$apx->tmpl->assign('ID', $threadinfo['threadid']);
		tmessage('recoverthread');
	}
}



/***********************************************************************************************/
/*************************************** Thema schließen ***************************************/
/***********************************************************************************************/
elseif ( $_REQUEST['action']=='closethread' ) {
	$apx->lang->drop('admin');

	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing thread-ID!');
	
	$threadinfo=thread_info($_REQUEST['id']);
	if ( !$threadinfo['threadid'] || $threadinfo['del'] ) message($apx->lang->get('MSG_THREADNOTEXIST'));
	$foruminfo=forum_info($threadinfo['forumid']);
	if ( !$foruminfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
	if ( !forum_access_admin($foruminfo) ) tmessage('noright',array(),false,false);
	
	$db->query("UPDATE ".PRE."_forum_threads SET open=0 WHERE threadid='".$threadinfo['threadid']."' LIMIT 1");
	
	//Zum Thema gehen
	$goto=mkrellink(
		'thread.php?id='.$threadinfo['threadid'],
		'thread,'.$threadinfo['threadid'].',1'.urlformat($threadinfo['title']).'.html'
	);
	
	message($apx->lang->get('MSG_CLOSETHREAD_OK'),$goto);
}



/***********************************************************************************************/
/*************************************** Thema öffnen ******************************************/
/***********************************************************************************************/
elseif ( $_REQUEST['action']=='openthread' ) {
	$apx->lang->drop('admin');

	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing thread-ID!');
	
	$threadinfo=thread_info($_REQUEST['id']);
	if ( !$threadinfo['threadid'] || $threadinfo['del'] ) message($apx->lang->get('MSG_THREADNOTEXIST'));
	$foruminfo=forum_info($threadinfo['forumid']);
	if ( !$foruminfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
	if ( !forum_access_admin($foruminfo) ) tmessage('noright',array(),false,false);
	
	$db->query("UPDATE ".PRE."_forum_threads SET open=1 WHERE threadid='".$threadinfo['threadid']."' LIMIT 1");
	
	//Zum Thema gehen
	$goto=mkrellink(
		'thread.php?id='.$threadinfo['threadid'],
		'thread,'.$threadinfo['threadid'].',1'.urlformat($threadinfo['title']).'.html'
	);
	
	message($apx->lang->get('MSG_OPENTHREAD_OK'),$goto);
}



/***********************************************************************************************/
/*************************************** Thema verschieben *************************************/
/***********************************************************************************************/
elseif ( $_REQUEST['action']=='movethread' ) {
	$apx->lang->drop('admin');

	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing thread-ID!');
	$_POST['moveto']=(int)$_POST['moveto'];
	
	$threadinfo=thread_info($_REQUEST['id']);
	if ( !$threadinfo['threadid'] || $threadinfo['del'] ) message($apx->lang->get('MSG_THREADNOTEXIST'));
	$foruminfo=forum_info($threadinfo['forumid']);
	if ( !$foruminfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
	if ( !forum_access_admin($foruminfo) ) tmessage('noright',array(),false,false);
	
	//Prüfen, ob Zugang zum Zielforum
	if ( $_POST['moveto'] && $_POST['moveto']!=$foruminfo['forumid'] ) {
		$destinfo=forum_info($_POST['moveto']);
		if ( !$destinfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
		if ( !forum_access_visible($destinfo) ) tmessage('noright',array(),false,false);
		if ( !forum_access_read($destinfo) ) tmessage('noright',array(),false,false);
	}
	
	//AKTION AUSFÜHREN
	if ( $destinfo['forumid'] ) {
		
		//Bestehende Verweise im Zielforum löschen
		$db->query("DELETE FROM ".PRE."_forum_threads WHERE forumid='".$destinfo['forumid']."' AND moved='".$threadinfo['threadid']."'");
		
		//Thema verschieben
		$db->query("UPDATE ".PRE."_forum_threads SET forumid='".$destinfo['forumid']."' WHERE threadid='".$threadinfo['threadid']."' LIMIT 1");
		
		//Altes und neues Forum LASTPOSTER aktualisieren
		forum_update_cache($foruminfo['forumid'], -$threadinfo['posts'], -1);
		forum_update_cache($destinfo['forumid'], $threadinfo['posts'], 1);
		
		//Verweis hinterlassen
		if ( $_POST['reference'] ) {
			$db->query("
				INSERT INTO ".PRE."_forum_threads
				(forumid,prefix,title,opener,opener_userid,opentime,lastposttime,moved,open) VALUES
				('".$foruminfo['forumid']."','".$threadinfo['prefix']."','".addslashes($threadinfo['title'])."','".addslashes($threadinfo['opener'])."','".$threadinfo['opener_userid']."','".$threadinfo['opentime']."','".$threadinfo['lastposttime']."','".$threadinfo['threadid']."','1')
			");
		}
		
		//Zum Zielforum gehen
		$goto=mkrellink(
			'forum.php?id='.$destinfo['forumid'],
			'forum,'.$destinfo['forumid'].',1'.urlformat($destinfo['title']).'.html'
		);
		
		message($apx->lang->get('MSG_MOVETHREAD_OK'),$goto);
	}
	else {
		$data = forum_readout();
		foreach ( $data AS $res ) {
			++$i;
			
			//Nicht sichtbare Foren überspringen
			if ( !forum_access_visible($res) || !forum_access_read($res) ) {
				$jump=$res['level'];
				continue;
			}
			if ( $jump && $res['level']>$jump ) continue;
			else $jump=0;
			
			if ( !$res['iscat'] ) $forumdata[$i]['ID']=$res['forumid'];
			$forumdata[$i]['TITLE']=replace($res['title']);
			$forumdata[$i]['LEVEL']=$res['level'];
			$forumdata[$i]['ISCAT']=$res['iscat'];
		}
		
		tmessage('movethread',array('ID'=>$threadinfo['threadid'],'FORUM'=>$forumdata));
	}
}



/***********************************************************************************************/
/*************************************** Thema teilen ******************************************/
/***********************************************************************************************/
elseif ( $_REQUEST['action']=='splitthread' ) {
	$apx->lang->drop('admin');
	$apx->lang->drop('postform');
	
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing thread-ID!');
	$_REQUEST['p']=(int)$_REQUEST['p'];
	$_POST['forum']=(int)$_POST['forum'];
	
	$threadinfo=thread_info($_REQUEST['id']);
	if ( !$threadinfo['threadid'] || $threadinfo['del'] ) message($apx->lang->get('MSG_THREADNOTEXIST'));
	$foruminfo=forum_info($threadinfo['forumid']);
	if ( !$foruminfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
	if ( !forum_access_admin($foruminfo) ) tmessage('noright',array(),false,false);
	
	//Sicherstellen, dass es sich um IDs handelt
	if ( !is_array($_POST['post']) ) $_POST['post']=array();
	foreach ( $_POST['post'] AS $key => $value ) {
		$_POST['post'][$key]=intval($value);
		if ( !$_POST['post'][$key] ) unset($_POST['post'][$key]);
	}
	
	//ABSENDEN
	if ( $_POST['send'] ) {
		$do = $_POST['do']=='merge' ? 'merge' : 'new';
		
		//Prüfen, ob Zugang zum Zielforum
		if ( $do=='new' && $_POST['forum'] ) {
			$destinfo=forum_info($_POST['forum']);
			if ( !$destinfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
			if ( !forum_access_visible($destinfo) ) tmessage('noright',array(),false,false);
			if ( !forum_access_read($destinfo) ) tmessage('noright',array(),false,false);
		}
		
		//Prüfen, ob das Zielthema existiert
		if ( $do=='merge' && $_POST['targetid'] ) {
			$destinfo=thread_info($_POST['targetid']);
			if ( !$destinfo['threadid'] ) message($apx->lang->get('MSG_THREADNOTEXIST'));
			$destforuminfo = forum_info($destinfo['forumid']);
			if ( !forum_access_visible($destforuminfo) ) tmessage('noright',array(),false,false);
			if ( !forum_access_read($destforuminfo) ) tmessage('noright',array(),false,false);
		}
		
		//Sicherstellen, dass nicht alle Beiträge ausgewählt sind
		if ( count($_POST['post']) ) {
			list($selected)=$db->first("SELECT count(postid) FROM ".PRE."_forum_posts WHERE ( postid IN (".implode(',',$_POST['post']).") AND del=0 AND threadid='".$threadinfo['threadid']."' )");
			list($total)=$db->first("SELECT count(postid) FROM ".PRE."_forum_posts WHERE ( del=0 AND threadid='".$threadinfo['threadid']."' )");
		}
		
		if ( $do=='new' && $total<=$selected ) message($apx->lang->get('MSG_NOTALLPOSTS'),'back');
		elseif ( $do=='new' && ( !$_POST['forum'] || !$_POST['title'] || !$_POST['post'] || !count($_POST['post']) ) ) message($apx->lang->get('CORE_BACK'),'back');
		elseif ( $do=='merge' && !$_POST['targetid'] ) message($apx->lang->get('CORE_BACK'),'back');
		else {
			
			//IN EIN THEMA VERSCHIEBEN
			if ( $do=='merge' ) {
				
				//Beiträge verschieben
				$db->query("UPDATE ".PRE."_forum_posts SET threadid='".$destinfo['threadid']."' WHERE ( postid IN (".implode(',',$_POST['post']).") AND del=0 AND threadid='".$threadinfo['threadid']."' )");
				
				//Altes Thema besitzt keine Beiträge mehr => Altes Thema löschen
				if ( $total<=$selected ) {
					$db->query("DELETE FROM ".PRE."_forum_threads WHERE threadid='".$threadinfo['threadid']."' LIMIT 1");
					
					//Themen aktualisieren
					thread_update_cache($destinfo['threadid'], $selected, true);
					
					//Foreninfo aktualisieren (unterschiedliche Foren)
					if ( $_POST['forum']!=$threadinfo['forum'] ) {
						forum_update_cache($foruminfo['forumid'], -$selected, -1);
						forum_update_cache($destforuminfo['forumid'], $selected);
					}
					
					//Selbes Forum
					else {
						forum_update_cache($foruminfo['forumid'], 0, -1);
					}
				}
				
				//Altes Thema bleibt bestehen
				else {
					
					//Themen aktualisieren
					thread_update_cache($threadinfo['threadid'], -$selected, true);
					thread_update_cache($destinfo['threadid'], $selected, true);
					
					//Foreninfo aktualisieren (unterschiedliche Foren)
					if ( $_POST['forum']!=$threadinfo['forum'] ) {
						forum_update_cache($foruminfo['forumid'], -$selected);
						forum_update_cache($destforuminfo['forumid'], $selected);
					}
				}
				
				//Suchindex anpassen
				$db->query("UPDATE ".PRE."_forum_index SET threadid='".$destinfo['threadid']."' WHERE postid IN (".implode(',',$_POST['post']).")");
				
				$goto=mkrellink(
					'thread.php?id='.$destinfo['threadid'],
					'thread,'.$destinfo['threadid'].',1'.urlformat($destinfo['title']).'.html'
				);
			}
			
			//NEUES THEMA ERSTELLEN
			else {
				
				//Sticky
				if ( forum_access_announce($foruminfo) && $_POST['sticky_type'] && $_POST['sticky_type']!='no' ) {
					$_POST['sticky']=1;
					if ( $_POST['sticky_type']=='announcement' ) $_POST['sticky_text']=$apx->lang->get('ANNOUNCEMENT');
					if ( $_POST['sticky_type']=='important' ) $_POST['sticky_text']=$apx->lang->get('IMPORTANT');
				}
				else {
					$_POST['sticky']=0;
					$_POST['sticky_text']='';
				}
				
				$_POST['forumid']=$destinfo['forumid'];
				$_POST['posts']=$selected;
				$_POST['open']=1;
				$_POST['icon']=iif($_POST['icon'] && $_POST['icon']!='none',$_POST['icon'],-1);
				
				//Thema erstellen
				$db->dinsert(PRE.'_forum_threads','forumid,title,icon,opener,opener_userid,opentime,firstpost,lastpost,lastposter,lastposter_userid,lastposttime,open,sticky,sticky_text,posts');
				$tid=$db->insert_id();
				
				//Beiträge verschieben
				$db->query("UPDATE ".PRE."_forum_posts SET threadid='".$tid."' WHERE ( postid IN (".implode(',',$_POST['post']).") AND del=0 AND threadid='".$threadinfo['threadid']."' )");
				
				//Themen aktualisieren
				thread_update_cache($threadinfo['threadid'], -$selected, true);
				thread_update_cache($tid, $selected, true);
				
				//Foreninfo aktualisieren (gleiches Forum)
				if ( $_POST['forum']==$threadinfo['forum'] ) {
					forum_update_cache($foruminfo['forumid'], 0, 1);
				}
				//Foreninfo aktualisieren (unterschiedliche Foren)
				else {
					forum_update_cache($foruminfo['forumid'], -$selected, 0);
					forum_update_cache($destinfo['forumid'], $selected, 1);
				}
				
				//Suchindex anpassen
				$db->query("UPDATE ".PRE."_forum_index SET threadid='".$tid."' WHERE postid IN (".implode(',',$_POST['post']).")");
				
				$goto=mkrellink(
					'thread.php?id='.$tid,
					'thread,'.$tid.',1'.urlformat($_POST['title']).'.html'
				);
			}
			
			message($apx->lang->get('MSG_SPLITTHREAD_OK'),$goto);
			require('lib/_end.php');
			require('../lib/_end.php');
		}
	}
	
	//VOREINSTELLUNGEN
	elseif ( !$_POST['p'] ) {
		$_REQUEST['p']=1;
		$_POST['icon']='none';
		$_POST['sticky_type']='no';
		$_POST['sticky_text']='';
		$_POST['forum']=$foruminfo['forumid'];
		$_POST['post']=array();
	}
	
	//Themenicons
	$icons=$set['forum']['icons'];
	if ( is_array($icons) ) $icons=array_sort($icons,'ord','ASC');
	if ( count($icons) ) {
		foreach ( $icons AS $key => $res ) {
			++$ii;
			$icondata[$ii]['ID']=$key;
			$icondata[$ii]['IMAGE']=$res['file'];
		}
	}
	
	//Foren auflisten
	$data = forum_readout();
	foreach ( $data AS $res ) {
		++$i;
		
		//Nicht sichtbare Foren überspringen
		if ( !forum_access_visible($res) || !forum_access_read($res) ) {
			$jump=$res['level'];
			continue;
		}
		if ( $jump && $res['level']>$jump ) continue;
		else $jump=0;
		
		if ( !$res['iscat'] ) $forumdata[$i]['ID']=$res['forumid'];
		$forumdata[$i]['TITLE']=replace($res['title']);
		$forumdata[$i]['LEVEL']=$res['level'];
		$forumdata[$i]['ISCAT']=$res['iscat'];
		$forumdata[$i]['SELECTED']=iif($_POST['forum']==$res['forumid'],1,0);
	}
	
	//Seitenzahlen
	list($count)=$db->first("SELECT count(postid) FROM ".PRE."_forum_posts WHERE ( del=0 AND threadid='".$threadinfo['threadid']."' )");
	$pages=ceil($count/($user->info['forum_ppp']*2));
	if ( $_REQUEST['p']<1 ) $_REQUEST['p']=1;
	if ( $_REQUEST['p']>$pages ) $_REQUEST['p']=$pages;
	
	//Beiträge auflisten
	$inlist=array();
	$data=$db->fetch("SELECT postid,username,text,time FROM ".PRE."_forum_posts WHERE ( del=0 AND threadid='".$threadinfo['threadid']."' ) ORDER BY time DESC LIMIT ".($user->info['forum_ppp']*2*($_REQUEST['p']-1)).','.($user->info['forum_ppp']*2));
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Text
			$text=clear_codes($res['text']);
			$text=replace($text,1);
			
			$postdata[$i]['ID']=$res['postid'];
			$postdata[$i]['USERNAME']=replace($res['username']);
			$postdata[$i]['TEXT']=$text;
			$postdata[$i]['TIME']=$res['time'];
			$postdata[$i]['SELECTED']=iif(in_array($res['postid'],$_POST['post']),1,0);
			$inlist[]=$res['postid'];
		}
	}
	
	//Alle IDs, die nicht aufgelistet wurden
	$notinlist=array_diff($_POST['post'],$inlist);
	foreach ( $notinlist AS $id ) {
		$seldata[]['ID']=$id;
	}
	
	$apx->tmpl->assign('FORUM',$forumdata);
	$apx->tmpl->assign('POST',$postdata);
	$apx->tmpl->assign('SELPOST',$seldata);
	$apx->tmpl->assign('TITLE',compatible_hsc($_POST['title']));
	$apx->tmpl->assign('ICON',$_POST['icon']);
	$apx->tmpl->assign('ICONLIST',$icondata);
	$apx->tmpl->assign('STICKY_TYPE',compatible_hsc($_POST['sticky_type']));
	$apx->tmpl->assign('STICKY_TEXT',compatible_hsc($_POST['sticky_text']));
	$apx->tmpl->assign('ANNOUNCE',forum_access_announce($foruminfo));
	$apx->tmpl->assign('ID',$threadinfo['threadid']);
	
	$apx->tmpl->assign('P',$_REQUEST['p']);
	$apx->tmpl->assign('PREVIOUS',iif($_REQUEST['p']>1,1,0));
	$apx->tmpl->assign('NEXT',iif($_REQUEST['p']<$pages,1,0));
	
	$apx->tmpl->parse('split');
	
	////////////////////////////////////////////
	
	$threadpath=array(array(
		'TITLE' => replace($threadinfo['title']),
		'LINK' => mkrellink(
			'thread.php?id='.$threadinfo['threadid'],
			'thread,'.$threadinfo['threadid'].',1'.urlformat($threadinfo['title']).'.html'
		)
	));
	
	$apx->tmpl->assign('PATH',array_merge(forum_path($foruminfo,1),$threadpath));
	$apx->tmpl->assign('PATHEND',$apx->lang->get('SPLITTHREAD'));
	titlebar($apx->lang->get('SPLITTHREAD'));
}



/***********************************************************************************************/
/*************************************** IP Statistik ******************************************/
/***********************************************************************************************/
elseif ( $_REQUEST['action']=='ipstats' ) {
	$apx->lang->drop('ipstats');
	
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing post-ID!');
	
	$postinfo=post_info($_REQUEST['id']);
	if ( !$postinfo['postid'] || $postinfo['del'] ) message($apx->lang->get('MSG_POSTNOTEXIST'));
	$threadinfo=thread_info($postinfo['threadid']);
	if ( !$threadinfo['threadid'] || $threadinfo['del'] ) message($apx->lang->get('MSG_THREADNOTEXIST'));
	$foruminfo=forum_info($threadinfo['forumid']);
	if ( !$foruminfo['forumid'] ) message($apx->lang->get('MSG_FORUMNOTEXIST'));
	if ( !forum_access_admin($foruminfo) ) tmessage('noright',array(),false,false);
	
	//////////////////////////////////////////////////////////////////////////////// SUCHERGEBNISSE ANZEIGEN
	
	//Beiträge von dieser IP
	$data=$db->fetch("SELECT userid,username,count(postid) AS posts FROM ".PRE."_forum_posts WHERE ip='".addslashes($postinfo['ip'])."' GROUP BY username ORDER BY username ASC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$fromdata[$i]['USERID']=$res['userid'];
			$fromdata[$i]['USERNAME']=replace($res['username']);
			$fromdata[$i]['POSTS']=$res['posts'];
		}
	}
	
	//Weitere IPs des Benutzers
	if ( $postinfo['userid'] ) {
		$data=$db->fetch("SELECT ip,count(postid) AS posts FROM ".PRE."_forum_posts WHERE userid='".$postinfo['userid']."' GROUP BY ip ORDER BY posts DESC");
		foreach ( $data AS $res ) {
			++$i;
			$otherdata[$i]['IP']=$res['ip'];
			$otherdata[$i]['POSTS']=$res['posts'];
		}
	}
	
	$apx->tmpl->assign('USERNAME',replace($postinfo['username']));
	$apx->tmpl->assign('USERID',$postinfo['userid']);
	$apx->tmpl->assign('THISIP',$postinfo['ip']);
	$apx->tmpl->assign('FROMIP',$fromdata);
	$apx->tmpl->assign('OTHER',$otherdata);
	
	$apx->tmpl->parse('ipstats');
	
	
	
	////////////////////////////////////////////////////////////////////////////////////////////////////////
	
	$apx->tmpl->assign('PATH',forum_path($foruminfo,1));
	$apx->tmpl->assign('PATHEND',iif($threadinfo['sticky'],$threadinfo['sticky_text'].': ').$threadinfo['title']);
	titlebar($threadinfo['title']);
}



//NO ACTION
else die('action does not exist!');


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');     ///////////////////////////////////////////////////////////////////////////
require('../lib/_end.php');  //////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>