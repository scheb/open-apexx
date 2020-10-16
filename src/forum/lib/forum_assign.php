<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');

/////////////////////////////////////////////////////////////////////////////// RECHTE

$collapse=explode('|',$_COOKIE['apx_forum_togglelist']);
$readthreads=threads_get_read(); //Alle gelesenen Themen


//Alle Variablen für ein Forum erzeugen
function createForumVars($res, $readoutMods = true) {
	global $apx, $db, $set, $user;
	global $collapse, $readthreads, $foruminfo;
	
	//Link
	$link=mkrellink(
		'forum.php?id='.$res['forumid'],
		'forum,'.$res['forumid'].',1'.urlformat($res['title']).'.html'
	);
		
	//Moderatoren
	$moddata=array();
	if ( $readoutMods ) {
		//$mods=dash_unserialize($res['moderator']);
		$mods = $res['moderator'];
		if ( count($mods) ) {
			$userdata=$db->fetch("SELECT userid,username FROM ".PRE."_user WHERE userid IN (".implode(',',$mods).") ORDER BY username ASC");
			if ( count($userdata) ) {
				foreach ( $userdata AS $modres ) {
					++$mi;
					$moddata[$mi]['USERID']=$modres['userid'];
					$moddata[$mi]['USERNAME']=replace($modres['username']);
				}
			}
		}
	}
	
	$forumdata['ID']=$res['forumid'];
	$forumdata['ISCAT']=$res['iscat'];
	$forumdata['LEVEL']=$res['level'];
	$forumdata['TITLE']=$res['title'];
	$forumdata['DESCRIPTION']=$res['description'];
	$forumdata['LINKTO']=$res['link'];
	$forumdata['LINK']=$link;
	$forumdata['THREADS']='-';
	$forumdata['POSTS']='-';
	$forumdata['CLOSED']=iif($res['open'],0,1);
	$forumdata['MODERATOR']=$moddata;
	$forumdata['COLLAPSE']=in_array($res['forumid'],$collapse);
	
	//Neues Thema erstellen, wenn Schreibrechte
	if ( forum_access_open($res) ) {
		$forumdata['LINK_NEWTHREAD']='newthread.php?id='.$res['forumid'];
	}
	
	//Nur anzeigen, wenn Leserechte
	if ( forum_access_read($res) && correct_forum_password($res) ) {
		$thread=false;
		
		//Lastvisit bestimmen
		$lastview=max(array(
			$user->info['forum_lastonline'],
			forum_readtime($res['forumid'])
		));
		
		$forumdata['NEWPOSTS']=iif($res['lastposttime'] && $res['lastposttime']>$lastview,1,0);
		$forumdata['THREADS']=number_format($res['threads'],0,'','.');
		$forumdata['POSTS']=number_format($res['posts'],0,'','.');
		
		//Letzter Beitrag
		//if ( $res['lastposttime'] && $thread==false ) $thread=$db->first("SELECT threadid,title,icon FROM ".PRE."_forum_threads WHERE ( del=0 AND moved=0 AND forumid='".$res['forumid']."' ) ORDER BY lastposttime DESC LIMIT 1");
		if( $res['lastthread_icon']!=-1 && isset($set['forum']['icons'][$res['lastthread_icon']]) ) $icon=$set['forum']['icons'][$res['lastthread_icon']]['file'];
		else $icon='';
		$forumdata['LASTPOST_THREADID']=$res['lastthread'];
		$forumdata['LASTPOST_THREADTITLE']=replace($res['lastthread_title']);
		$forumdata['LASTPOST_THREADPREFIX']=forum_get_prefix($res['lastthread_prefix']);
		$forumdata['LASTPOST_USERNAME']=replace($res['lastposter']);
		$forumdata['LASTPOST_USERID']=$res['lastposter_userid'];
		$forumdata['LASTPOST_TIME']=$res['lastposttime'];
		$forumdata['LASTPOST_LINK']='thread.php?id='.$res['lastthread'].'&amp;goto=lastpost';
		$forumdata['LASTPOST_ICON']=$icon;
	}
	
	return $forumdata;
}


//Forum-Liste abarbeiten
$forumRec = array();
if ( count($data) ) {
	
	//Erzeuge eine rekursive Datenstruktur
	$superForum = array();
	foreach ( $data AS $key => $res ) {
		$right_visible=dash_unserialize($res['right_visible']);
		$right_read=dash_unserialize($res['right_read']);
		
		//Foren, die der Benutzer nicht lesen darf überspringen
		if ( !forum_access_visible($res) ) {
			continue;
		}
		
		//Level 1 Foren in die Liste schreiben
		if ( $res['level']==1 ) {
			$forumRec[] = &$data[$key];
		}
		
		//Letztes Forum pro Level merken
		$superForum[$res['level']] = &$data[$key];
		
		//Beim übergeordneten Forum hinzufügen
		if ( isset($superForum[$res['level']-1]) ) {
			$superForum[$res['level']-1]['subforums'][] = &$data[$key];
		}
	}
	
	
	
	//Posts und Threadcount, sowieso Lastpost nach oben propagieren
	function propagateInfo(&$forumList) {
		//Felder, die für Lastpost kopiert werden müssen
		$lastpostcopy = array(
			'lastposttime',
			'lastpost',
			'lastposter',
			'lastposter_userid',
			'lastposttime',
			'lastthread',
			'lastthread_title',
			'lastthread_icon',
			'lastthread_prefix'
		);
		
		$info = array();
		foreach ( $forumList AS $key => $res ) {
			$subinfo = array();
			
			//Info von Subforen holen
			if ( isset($res['subforums']) ) {
				$subinfo = propagateInfo($res['subforums']);
			}
			
			//Info einfügen
			if ( isset($subinfo['posts']) ) {
				$res['posts'] += $subinfo['posts'];
				$forumList[$key]['posts'] += $subinfo['posts'];
			}
			if ( isset($subinfo['threads']) ) {
				$res['threads'] += $subinfo['threads'];
				$forumList[$key]['threads'] += $subinfo['threads'];
			}
			if ( isset($subinfo['lastpost']) && $subinfo['lastpost']['lastposttime']>$res['lastposttime'] ) {
				foreach ( $subinfo['lastpost'] AS $key2 => $value ) {
					$res[$key2] = $value;
					$forumList[$key][$key2] = $value;
				}
			}
			
			//Forum darf gelesen werden => Informationen für übergeordnetes Forum relevant
			if ( forum_access_read($res) ) {
				
				//Posts und Threads summieren
				$info['posts'] += $res['posts'];
				$info['threads'] += $res['threads'];
				
				//Lastpost ermitteln
				if ( !isset($info['lastposttime']) || $res['lastposttime']>$info['lastposttime'] ) {
					foreach ( $lastpostcopy AS $lcopy ) {
						$info['lastpost'][$lcopy] = $res[$lcopy];
						$info['lastposttime'] = $res['lastposttime'];
					}
				}
			}
			
		}
		return $info;
	}
	
	propagateInfo($forumRec);
	
	
	/*
	$l1Id = 0;
	$l2Id = 0;
	
	//Foren auflisten
	foreach ( $data AS $res ) {
		if ( $res['level']==1 ) {
			$l1Id = $res['forumid'];
			$l2Id = 0;
		}
		elseif ( $res['level']==2 ) {
			$l2Id = $res['forumid'];
		}
		
		$right_visible=dash_unserialize($res['right_visible']);
		$right_read=dash_unserialize($res['right_read']);
		
		//Nicht sichtbare Foren und deren Unterforen überspringen
		if ( !forum_access_visible($res) ) {
			$jump=$res['level'];
			continue;
		}
		if ( $jump && $res['level']>$jump ) continue;
		else $jump=0;
		
		//Variablen für Level 1 erzeugen
		if ( $res['level']==1 ) {
			$mainforums[$res['forumid']] = $res;
			$subforums[$res['forumid']] = array();
		}
		
		//Variable für Level 2 erzeugen
		elseif ( $res['level']==2 ) {
			$subforums[$l1Id][$res['forumid']] = $res;
		}
		
		
		//Lastpost und Beiträge/Themen vererben, wenn das Forum lesbar ist
		if ( $res['level']>1 && forum_access_read($res) ) {
			
			//Beiträge/Themen in Oberforen hinzuzählen
			$mainforums[$l1Id]['threads'] += $res['threads'];
			$mainforums[$l1Id]['posts'] += $res['posts'];
			if ( $res['level']>2 ) {
				$subforums[$l1Id][$l2Id]['threads'] += $res['threads'];
				$subforums[$l1Id][$l2Id]['posts'] += $res['posts'];
			}
			
			//Neuesten Beitrag finden
			if ( $res['lastposttime']>$mainforums[$l1Id]['lastposttime'] ) {
				foreach ( $lastpostcopy AS $lcopy ) {
					$mainforums[$l1Id][$lcopy] = $res[$lcopy];
				}
			}
			if ( $res['level']>2 && $res['lastposttime']>$subforums[$l1Id][$l2Id]['lastposttime'] ) {
				foreach ( $lastpostcopy AS $lcopy ) {
					$subforums[$l1Id][$l2Id][$lcopy] = $res[$lcopy];
				}
			}
			
		}
	}
	
	//Variablen erzeugen
	$i = 0;
	$j = 0;
	foreach ( $mainforums AS $res ) {
		++$i;
		$forumdata[$i] = createForumVars($res);
		$forumdata[$i]['SUB'] = array();
		foreach ( $subforums[$res['forumid']] AS $subres ) {
			++$j;
			$forumdata[$i]['SUB'][$j] = createForumVars($subres);
		}
	}*/
	
	$i = 0;
	$j = 0;
	$k = 0;
	foreach ( $forumRec AS $level1 ) {
		++$i;
		$forumdata[$i] = createForumVars($level1);
		$forumdata[$i]['SUB'] = array();
		if ( $level1['subforums'] ) {
			foreach ( $level1['subforums'] AS $level2 ) {
				++$j;
				$forumdata[$i]['SUB'][$j] = createForumVars($level2);
				if ( $level2['subforums'] ) {
					foreach ( $level2['subforums'] AS $level3 ) {
						++$k;
						$forumdata[$i]['SUB'][$j]['SUB2'][$k] = createForumVars($level3, false);
					}
				}
			}
		}
	}
}

$apx->tmpl->assign('FORUM',$forumdata);

?>