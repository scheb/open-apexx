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


define('APXRUN',true);
define('INFORUM',true);
define('BASEREL','../');

////////////////////////////////////////////////////////////////////////////////////////////////////////
require('../lib/_start.php');  ///////////////////////////////////////////////////////// SYSTEMSTART ///
require('lib/_start.php');     /////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////////////////////////////////

$_REQUEST['search']=(int)$_REQUEST['search'];



//////////////////////////////////////////////////////////////////////////////// SUCHERGEBNISSE ANZEIGEN

if ( $_REQUEST['search'] ) {
	$search=$db->first("SELECT * FROM ".PRE."_forum_search WHERE id='".$_REQUEST['search']."' AND hash='".addslashes($_REQUEST['hash'])."' LIMIT 1");
	if ( !$search['id'] ) {
		require('lib/_end.php');
		require('../lib/_end.php');
	}
	
	//Unserialize
	$search['result']=dash_unserialize($search['result']);
	$highlight=unserialize($search['highlight']);
	$ignored=unserialize($search['ignored']);
	if ( !is_array($highlight) ) $highlight=array();
	if ( !is_array($ignored) ) $ignored=array();
	$search['highlight']=urlencode(implode('+',$highlight));
	
	$apx->lang->drop('searchresult');
	
	//BEITRÄGE
	if ( $search['display']=='posts' ) {
		$apx->lang->drop('thread');
		
		//Seitenzahlen
		list($count)=$db->first("SELECT count(postid) FROM ".PRE."_forum_posts WHERE ( del=0 AND postid IN (".implode(',',$search['result']).") )");
		pages(
			'search.php?search='.$search['id'].'&amp;hash='.$_REQUEST['hash'],
			$count,
			$user->info['forum_ppp']
		);
		
		//Sortieren nach
		$_REQUEST['sortby']=$search['sortby_field'].'.'.$search['sortby_dir'];
		$orderdef[0]='open';
		$orderdef['title']=array('title','ASC');
		$orderdef['open']=array('time','DESC');
		
		//Daten auslesen
		$data=$db->fetch("SELECT * FROM ".PRE."_forum_posts WHERE ( del=0 AND postid IN (".implode(',',$search['result']).") ) ".getorder($orderdef).getlimit($user->info['forum_ppp']));
		
		//Zugehörige Themen auslesen
		$threads=get_ids($data,'threadid');
		$threadinfo=array();
		$threaddata=$db->fetch("SELECT threadid,forumid,prefix,title,sticky_text,lastposttime,open,posts,views FROM ".PRE."_forum_threads WHERE threadid IN (".implode(',',$threads).")");
		if ( count($threaddata) ) {
			foreach ( $threaddata AS $res ) {
				$threadinfo[$res['threadid']]=$res;
				$threadinfo[$res['threadid']]['link']=mkrellink(
					'thread.php?id='.$res['threadid'].iif($search['highlight'],'&amp;highlight='.$search['highlight']),
					'thread,'.$res['threadid'].',1'.urlformat($res['title']).'.html'.iif($search['highlight'],'?highlight='.$search['highlight'])
				);
			}
		}
		
		//Zugehörige Foren auslesen
		$forums=get_ids($threaddata,'forumid');
		$foruminfo=array();
		$forumdata=$db->fetch("SELECT forumid,title FROM ".PRE."_forums WHERE forumid IN (".implode(',',$forums).")");
		if ( count($forumdata) ) {
			foreach ( $forumdata AS $res ) {
				$foruminfo[$res['forumid']]=$res;
				$foruminfo[$res['forumid']]['link']=mkrellink(
					'forum.php?id='.$res['forumid'],
					'forum,'.$res['forumid'].',1'.urlformat($res['title']).'.html'
				);
			}
		}
		
		//Beiträge auflisten
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				++$i;
				
				$thisthread=$threadinfo[$res['threadid']];
				$thisforumid=$threadinfo[$res['threadid']]['forumid'];
				
				//Lastvisit bestimmen
				$lastview=max(array(
					$user->info['forum_lastonline'],
					thread_readtime($thisthread),
					forum_readtime($thisforumid)
				));
				
				//Text + Titel
				$title=replace($res['title']);
				$text=$res['text'];
				$text=clear_codes($text);
				$text=replace($text,1);
				
				//Highlight
				if ( $_REQUEST['highlight'] ) {
					$title=text_highlight($title);
					$text=text_highlight($text);
				}
				
				$postdata[$i]['ID']=$res['postid'];
				$postdata[$i]['TITLE']=$title;
				$postdata[$i]['TEXT']=$text;
				$postdata[$i]['TIME']=$res['time'];
				$postdata[$i]['USERNAME']=replace($res['username']);
				$postdata[$i]['USERID']=$res['userid'];
				$postdata[$i]['NEW']=iif($res['time']>$lastview,1,0);
				
				//Thread
				$postdata[$i]['THREAD_ID']=$res['threadid'];
				$postdata[$i]['THREAD_TITLE']=replace($thisthread['title']);
				$postdata[$i]['THREAD_PREFIX']=forum_get_prefix($thisthread['prefix']);
				$postdata[$i]['THREAD_LINK']=$thisthread['link'];
				$postdata[$i]['THREAD_POSTS']=number_format($thisthread['posts'],0,'','.');;
				$postdata[$i]['THREAD_VIEWS']=number_format($thisthread['views'],0,'','.');;
				$postdata[$i]['THREAD_STICKY']=replace($thisthread['sticky_text']);
				$postdata[$i]['THREAD_NEWPOSTS']=iif($thisthread['lastposttime'] && $thisthread['lastposttime']>$lastview,1,0);
				$postdata[$i]['THREAD_CLOSED']=!$thisthread['open'];
				
				//Forum
				$postdata[$i]['FORUM_ID']=$thisforumid;
				$postdata[$i]['FORUM_TITLE']=replace($foruminfo[$thisforumid]['title']);
				$postdata[$i]['FORUM_LINK']=$foruminfo[$thisforumid]['link'];
				
				//Link: zum Beitrag springen
				$link='thread.php?id='.$thisthread['threadid'].'&amp;postid='.$res['postid'].iif($search['highlight'],'&amp;highlight='.$search['highlight']).'#p'.$res['postid'];
				$postdata[$i]['LINK']=$link;
			}
		}
		
		//Hot-Parameter in Sprachplatzhalter
		$langvar = strtr($apx->lang->get('HOTTHREAD'),array('{HOT_POSTS}'=>$set['forum']['hot_posts'],'{HOT_VIEWS}'=>$set['forum']['hot_views']));
		$apx->lang->langpack['HOTTHREAD'] = $langvar;
		
		$apx->tmpl->assign('HOT_POSTS',$set['forum']['hot_posts']);
		$apx->tmpl->assign('HOT_VIEWS',$set['forum']['hot_views']);
		$apx->tmpl->assign('IGNORED',implode(', ',$ignored));
		$apx->tmpl->assign('SEARCHTIME',round($search['time'],2));
		$apx->tmpl->assign('POST',$postdata);
		$apx->tmpl->parse('search_result_posts');
	}
	
	//THEMEN
	else {
		$apx->lang->drop('forum');
		
		//Seitenzahlen
		list($count)=$db->first("SELECT count(threadid) FROM ".PRE."_forum_threads WHERE ( del=0 AND moved=0 AND threadid IN (".implode(',',$search['result']).") )");
		pages(
			'search.php?search='.$search['id'].'&amp;hash='.$_REQUEST['hash'],
			$count,
			$user->info['forum_tpp']
		);
		
		//Sortieren nach
		if ( !isset($_REQUEST['sortby']) ) $_REQUEST['sortby']=$search['sortby_field'].'.'.$search['sortby_dir'];
		$orderdef[0]='lastpost';
		$orderdef['title']=array('title','ASC');
		$orderdef['open']=array('opentime','DESC');
		$orderdef['opener']=array('opener','ASC');
		$orderdef['lastpost']=array('lastposttime','DESC');
		$orderdef['posts']=array('posts','DESC');
		$orderdef['views']=array('views','DESC');
		
		//Daten auslesen
		$data=$db->fetch("SELECT * FROM ".PRE."_forum_threads WHERE ( del=0 AND threadid IN (".implode(',',$search['result']).") ) ".getorder($orderdef).getlimit($user->info['forum_tpp']));
		
		//Zugehörige Foren auslesen
		$forums=get_ids($data,'forumid');
		$foruminfo=array();
		$forumdata=$db->fetch("SELECT forumid,title FROM ".PRE."_forums WHERE forumid IN (".implode(',',$forums).")");
		if ( count($forumdata) ) {
			foreach ( $forumdata AS $res ) {
				$foruminfo[$res['forumid']]=$res;
				$foruminfo[$res['forumid']]['link']=mkrellink(
					'forum.php?id='.$res['forumid'],
					'forum,'.$res['forumid'].',1'.urlformat($res['title']).'.html'
				);
			}
		}
		
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				++$i;
				
				//Lastvisit bestimmen
				$lastview=max(array(
					$user->info['forum_lastonline'],
					thread_readtime($res['threadid']),
					forum_readtime($res['forumid'])
				));
				
				//Link
				$link=mkrellink(
					'thread.php?id='.$res['threadid'].iif($search['highlight'],'&amp;highlight='.$search['highlight']),
					'thread,'.$res['threadid'].',1'.urlformat($res['title']).'.html'.iif($search['highlight'],'?highlight='.$search['highlight'])
				);
				
				//Icon
				if( $res['icon']!=-1 && isset($set['forum']['icons'][(int)$res['icon']]) ) $icon=$set['forum']['icons'][(int)$res['icon']]['file'];
				else $icon='';
				
				$threaddata[$i]['ID']=$res['threadid'];
				$threaddata[$i]['TITLE']=replace($res['title']);
				$threaddata[$i]['PREFIX']=forum_get_prefix($res['prefix']);
				$threaddata[$i]['LINK']=$link;
				$threaddata[$i]['ICON']=$icon;
				$threaddata[$i]['OPENER_USERID']=$res['opener_userid'];
				$threaddata[$i]['OPENER_USERNAME']=replace($res['opener']);
				$threaddata[$i]['OPENTIME']=$res['opentime'];
				$threaddata[$i]['LASTPOST_USERID']=$res['lastposter_userid'];
				$threaddata[$i]['LASTPOST_USERNAME']=replace($res['lastposter']);
				$threaddata[$i]['LASTPOST_TIME']=$res['lastposttime'];
				$threaddata[$i]['LASTPOST_LINK']='thread.php?id='.$res['threadid'].'&amp;goto=lastpost';
				$threaddata[$i]['LINK_UNREAD']='thread.php?id='.$res['threadid'].'&amp;goto=firstunread';
				$threaddata[$i]['STICKY']=replace($res['sticky_text']);
				$threaddata[$i]['POSTS']=$res['posts']-1;
				$threaddata[$i]['VIEWS']=$res['views'];
				$threaddata[$i]['NEWPOSTS']=iif($res['lastposttime'] && $res['lastposttime']>$lastview,1,0);
				$threaddata[$i]['CLOSED']=!$res['open'];
				
				//Forum
				$threaddata[$i]['FORUM_ID']=$res['forumid'];
				$threaddata[$i]['FORUM_TITLE']=replace($foruminfo[$res['forumid']]['title']);
				$threaddata[$i]['FORUM_LINK']=$foruminfo[$res['forumid']]['link'];
				
				//Bewertungen
				if ( $apx->is_module('ratings') && $set['forum']['ratings'] ) {
					require_once(BASEDIR.getmodulepath('ratings').'class.ratings.php');
					if ( !isset($rate) ) $rate=new ratings('forum',$res['threadid']);
					else $rate->mid=$res['threadid'];
					$threaddata[$i]['RATING']=$rate->display();
					$threaddata[$i]['RATING_VOTES']=$rate->count();
					$threaddata[$i]['DISPLAY_RATING']=1;
				}
			}
		}
		
		//Sortieren nach...
		ordervars(
			$orderdef,
			'search.php?search='.$search['id'].'&amp;hash='.$_REQUEST['hash']
		);
		
		//Hot-Parameter in Sprachplatzhalter
		$langvar = strtr($apx->lang->get('HOTTHREAD'),array('{HOT_POSTS}'=>$set['forum']['hot_posts'],'{HOT_VIEWS}'=>$set['forum']['hot_views']));
		$apx->lang->langpack['HOTTHREAD'] = $langvar;
		
		$apx->tmpl->assign('HOT_POSTS',$set['forum']['hot_posts']);
		$apx->tmpl->assign('HOT_VIEWS',$set['forum']['hot_views']);
		$apx->tmpl->assign('IGNORED',implode(', ',$ignored));
		$apx->tmpl->assign('SEARCHTIME',round($search['time'],2));
		$apx->tmpl->assign('THREAD',$threaddata);
		$apx->tmpl->parse('search_result_threads');
	}
	
	/////////////////////////////////////
	
	$apx->tmpl->assign('PATHEND',$apx->lang->get('HEADLINE_SEARCHRESULT'));
	titlebar($apx->lang->get('HEADLINE_SEARCHRESULT'));
	
	require('lib/_end.php');
	require('../lib/_end.php');
}



//////////////////////////////////////////////////////////////////////////////////////// NEUE BEITRÄGE

$apx->lang->drop('search');

if ( $_REQUEST['newposts'] ) {
	$searchstart=microtime();
	
	$inforum=array();
	$data = forum_readout();
	foreach ( $data AS $res ) {
		
		//Nicht sichtbare Foren überspringen
		if ( !forum_access_visible($res) || !forum_access_read($res) || !correct_forum_password($res) ) continue;
		
		//Keine Kategorien durchsuchen
		if ( $res['iscat'] ) continue;
		 
		$inforum[]=$res['forumid'];
	}
	
	//Keine Foren zum Durchsuchen
	if ( !count($inforum) ) {
		message($apx->lang->get('MSG_EMPTYSEARCH'),'javascript:history.back();');
	}
	
	//Themen nach Suchkriterien filtern
	$data=$db->fetch("SELECT threadid FROM ".PRE."_forum_threads WHERE ( forumid IN (".implode(',',$inforum).") AND lastposttime>'".$user->info['forum_lastonline']."' AND del=0 AND moved=0 ) ORDER BY threadid ASC");
	$result=get_ids($data,'threadid');
	$display='threads';
	
	//Keine Treffer
	if ( !count($result) ) {
		message($apx->lang->get('MSG_EMPTYSEARCH'),'javascript:history.back();');
	}
	
	//Suchergebnis eintragen und weiterleiten
	else {
		
		list($usec,$sec)=explode(' ',microtime()); 
		$b2=((float)$usec+(float)$sec);
		list($usec,$sec)=explode(' ',$searchstart); 
		$b1=((float)$usec+(float)$sec);
		$searchtime=round($b2-$b1,5);
		
		$highlightme='';
		$resultstring=dash_serialize($result);
		$hash=md5(uniqid(time()));
		$ign=serialize(array());
		
		$db->query("INSERT INTO ".PRE."_forum_search (userid,result,display,highlight,ignored,order_field,order_dir,time,hash) VALUES ('".$user->info['userid']."','".addslashes($resultstring)."','".addslashes($display)."','".addslashes($highlightme)."','".addslashes($ign)."','".addslashes($_POST['sortby'])."','".addslashes($_POST['sortby_dir'])."','".$searchtime."','".addslashes($hash)."')");
		$searchid=$db->insert_id();
		$link = mkrellink(
			'search.php?search='.$searchid.'&hash='.$hash,
			'search.php?search='.$searchid.'&hash='.$hash
		);
		header("HTTP/1.1 301 Moved Permanently");
		header('location: '.$link);
		exit;
	}
	
	require('lib/_end.php');
	require('../lib/_end.php');
}

//////////////////////////////////////////////////////////////////////////////////////// SUCHE AUSFÜHREN

$apx->lang->drop('search');

if ( $_REQUEST['send'] ) {
	
	//Direkte Forensuche
	if ( $_POST['keywords_req'] && !$_POST['keywords_one'] && !$_POST['keywords_not'] ) {
		$_POST['searchtitle']=1;
		$_POST['searchtext']=1;
	}
	
	//Forum zu Thread-ID auslesen
	if ( $_POST['threadid'] ) {
		$threadinfo=thread_info($_REQUEST['id']);
		if ( $threadinfo['del'] ) $threadinfo=array();
		$_POST['forumid'] = array($threadinfo['forumid']);
	}
	
	//Standardeinstellungen
	if ( !isset($_POST['keywords_req']) ) $_POST['keywords_req']='';
	if ( !isset($_POST['keywords_one']) ) $_POST['keywords_one']='';
	if ( !isset($_POST['keywords_not']) ) $_POST['keywords_not']='';
	//if ( !isset($_POST['searchtitle']) ) $_POST['searchtitle']=1;
	//if ( !isset($_POST['searchtext']) ) $_POST['searchtext']=1;
	if ( !isset($_POST['author']) ) $_POST['author']='';
	//if ( !isset($_POST['findthreads']) ) $_POST['findthreads']=1;
	//if ( !isset($_POST['findposts']) ) $_POST['findposts']=1;
	if ( !isset($_POST['answers']) ) $_POST['answers']=0;
	if ( !isset($_POST['forumid']) ) $_POST['forumid']=array('all');
	if ( !isset($_POST['searchsubforums']) ) $_POST['searchsubforums']=1;
	if ( !isset($_POST['period']) ) $_POST['period']='all';
	if ( !isset($_POST['period_dir']) ) $_POST['period_dir']='younger';
	if ( !isset($_POST['sortby']) ) $_POST['sortby']='lastpost';
	if ( !isset($_POST['sortby_dir']) ) $_POST['sortby_dir']='DESC';
	if ( !isset($_POST['display']) ) $_POST['display']='threads';
	
	//Beiträge eines Besuchers suchen
	if ( isset($_GET['author']) ) {
		$_POST['author']=$_GET['author'];
		$_POST['display']='posts';
		$_POST['exact']=1;
		$_POST['findthreads']=1;
		$_POST['findposts']=1;
	}
	
	//Suche beginnt
	if ( 
		( !$_POST['keywords_req'] && !$_POST['keywords_one'] && !$_POST['keywords_not'] && !$_POST['author'] )
		|| ( ( $_POST['keywords_req'] || $_POST['keywords_one'] || $_POST['keywords_not'] ) && !$_POST['searchtitle'] && !$_POST['searchtext'] )
		|| ( $_POST['author'] && !$_POST['findthreads'] && !$_POST['findposts'] )
	) message($apx->lang->get('CORE_BACK'),'javascript:history.back();');
	else {
		
		$searchstart=microtime();		
		$wherepost='';
		$wherethread='';
		$keywords=$ignored=array();
		
		//Suchstring erzeugen und prüfen
		if ( $_POST['keywords_req'] || $_POST['keywords_one'] || $_POST['keywords_not'] ) {
			list($words_req,$ignored1)=searchstring_to_array($_POST['keywords_req']);
			list($words_one,$ignored2)=searchstring_to_array($_POST['keywords_one']);
			list($words_not,$ignored3)=searchstring_to_array($_POST['keywords_not']);
			
			$keywords=array_unique(array_merge($words_req,$words_one,$words_not));
			$ignored=array_unique(array_merge($ignored1,$ignored2,$ignored3));
			
			if ( !count($keywords) ) {
				$message=$apx->lang->get('MSG_WRONGKEYWORDS');
				if ( count($ignored) ) {
					sort($ignored);
					$message.='<br />'.$apx->lang->get('IGNORED').': '.implode(', ',$ignored);
				}
				message($message,'javascript:history.back();');
				require('lib/_end.php');
				require('../lib/_end.php');
			}
		}
		
		//Foren auslesen
		if ( !is_array($_POST['forumid']) ) $_POST['forumid']=array('all');
		$searchsubtill=9999999;
		$inforum=array();
		$data = forum_readout();
		foreach ( $data AS $res ) {
			
			//Keine weiteren Unterforen vorhanden
			if ( $res['level']<=$searchsubtill ) $searchsubtill=9999999;
			
			//Nicht sichtbare Foren überspringen
			if ( !forum_access_visible($res) || !forum_access_read($res) ) continue;
			
			//Forum nicht ausgewählt
			if ( $_POST['forumid'][0]!='all' && !in_array($res['forumid'],$_POST['forumid']) && $res['level']<=$searchsubtill ) continue;
			
			//Unterforen ebenfalls durchsuchen
			if ( $_POST['searchsubforums'] && $searchsubtill>$res['level'] ) $searchsubtill=$res['level'];
			
			//Keine Kategorien durchsuchen
			if ( $res['iscat'] ) continue;
			 
			$inforum[]=$res['forumid'];
		}
		
		//Keine Foren zum Durchsuchen vorhanden
		if ( !count($inforum) ) {
			message($apx->lang->get('CORE_BACK'),'javascript:history.back();');
			require('lib/_end.php');
			require('../lib/_end.php');
		}
		
		//Suchbegriffe
		if ( count($keywords) ) {
			
			$searchindex=' word IN ('.array_to_searchsql($keywords).') ';
			
			if ( $_POST['searchtitle'] && !$_POST['searchtext'] ) $searchindex=" ( istitle=1 AND ( ".$searchindex." ) ) ";
			elseif ( !$_POST['searchtitle'] && $_POST['searchtext'] ) $searchindex=" ( istitle=0 AND ( ".$searchindex." ) ) ";
			else $searchindex=" ( ".$searchindex." ) ";
			
			//Alle Ergebnisse auslesen
			$data=$db->fetch("SELECT threadid,postid,word FROM ".PRE."_forum_index WHERE ".$searchindex);
			
			//Keine Ergebnisse?
			if ( !count($data) ) {
				message($apx->lang->get('MSG_EMPTYSEARCH'),'javascript:history.back();');
				require('lib/_end.php');
				require('../lib/_end.php');
			}
			
			$word2content=array();
			foreach ( $data AS $res ) {
				$word2content[$res['word']]['threads'][]=$res['threadid'];
				$word2content[$res['word']]['posts'][]=$res['postid'];
			}
			foreach ( $word2content AS $word => $result ) {
				$word2content[$word]['threads']=array_unique($result['threads']);
				$word2content[$word]['posts']=array_unique($result['posts']);
			}
			
			//ERGEBNISSE: RequiredWords
			$reqi=0;
			$result_req=array('threads'=>array(),'posts'=>array());
			if ( count($words_req) ) {
				foreach ( $words_req AS $word ) {
					++$reqi;
					
					//Keine Ergebnisse zu einem Wort => Schnittmenge wird auch leer sein!
					if ( !isset($word2content[$word]) ) {
						message($apx->lang->get('MSG_EMPTYSEARCH'),'javascript:history.back();');
						require('lib/_end.php');
						require('../lib/_end.php');
					}
					
					//Erstes Teilergebnis so übernehmen wie es ist
					// => Nichts da, um eine Schnittmenge zu bestimmen
					if ( $reqi==1 ) {
						$result_req['threads']=$word2content[$word]['threads'];
						$result_req['posts']=$word2content[$word]['posts'];
					}
					
					//Schnittmenge bestimmen (Alle Beiträge/Themen, die alle Wörter besitzen)
					else {
						if ( isset($word2content[$word]['threads']) ) {
							$result_req['threads']=array_intersect($result_req['threads'],$word2content[$word]['threads']);
						}
						if ( isset($word2content[$word]['posts']) ) {
							$result_req['posts']=array_intersect($result_req['posts'],$word2content[$word]['posts']);
						}
					}
				}
			}
			
			//ERGEBNISSE: OneWord
			$result_one=array('threads'=>array(),'posts'=>array());
			if ( count($words_one) ) {
				
				//Summe bestimmen (Alle Beiträge/Themen, die eines der Wörter besitzen)
				foreach ( $words_one AS $word ) {
					if ( isset($word2content[$word]['threads']) ) {
						$result_one['threads']=array_merge($result_one['threads'], $word2content[$word]['threads']);
					}
					if ( isset($word2content[$word]['posts']) ) {
						$result_one['posts']=array_merge($result_one['posts'], $word2content[$word]['posts']);
					}
				}
				
				$result_one['threads']=array_unique($result_one['threads']);
				$result_one['posts']=array_unique($result_one['posts']);
				
				//Kein Ergebnis
				if ( !count($result_one) ) {
					message($apx->lang->get('MSG_EMPTYSEARCH'),'javascript:history.back();');
					require('lib/_end.php');
					require('../lib/_end.php');
				}
			}
			
			//ERGEBNISSE: NotWords
			$result_not=array('threads'=>array(),'posts'=>array());
			if ( count($words_not) ) {
				
				//Summe aller verbotenen Beiträge/Themen
				foreach ( $words_not AS $word ) {
					if ( isset($word2content[$word]['threads']) ) {
						$result_not['threads']=array_merge($result_not['threads'],$word2content[$word]['threads']);
					}
					if ( isset($word2content[$word]['posts']) ) {
						$result_not['posts']=array_merge($result_not['posts'],$word2content[$word]['posts']);
					}
				}
				
				$result_not['threads']=array_unique($result_not['threads']);
				$result_not['posts']=array_unique($result_not['posts']);
			}
			
			//Wenn sowohl nach REQ als auch ONE gesucht wurde
			if ( count($words_req) && count($words_one) ) {
				$tempres['threads']=array_intersect($result_req['threads'],$result_one['threads']);
				$tempres['posts']=array_intersect($result_req['posts'],$result_one['posts']);
			}
			
			//Nur REQ
			elseif ( count($words_req) ) {
				$tempres['threads']=$result_req['threads'];
				$tempres['posts']=$result_req['posts'];
			}
			
			//Nur ONE
			else {
				$tempres['threads']=$result_one['threads'];
				$tempres['posts']=$result_one['posts'];
			}
			
			//Themen und Beiträge herausfiltern, die nicht gewünscht sind
			$keyword_threads=array_unique(array_diff($tempres['threads'],$result_not['threads']));
			$keyword_posts=array_unique(array_diff($tempres['posts'],$result_not['posts']));
			
			//Wort-Highlighting
			$highlight=array_merge($words_req,$words_one);
		}
		
		
/////////////////////////////////////////////////////////////////////////////////////////////////
/////////////////////////////////////////////////////////////////////////// ALS BEITRÄGE ANZEIGEN
/////////////////////////////////////////////////////////////////////////////////////////////////
		if ( $_POST['display']=='posts' ) {
			
			//Nur Themen aus den gewählten Foren
			$wherethread.=" forumid IN (".implode(',',$inforum).") ";
			
			//Nur Themen vom Autor suchen
			if ( $_REQUEST['author'] && $_POST['findthreads'] && !$_POST['findposts'] ) {
				if ( $_POST['exact'] ) $wherethread.=iif($wherethread,' AND ')." opener='".addslashes($_REQUEST['author'])."' ";
				else $wherethread.=iif($wherethread,' AND ')." opener LIKE '%".addslashes_like($_REQUEST['author'])."%' ";
			}
			
			//Antworten: Filter erstellen
			if ( $_POST['answers'] ) {
				$wherethread.=iif($wherethread,' AND ')." posts>='".(intval($_POST['answers'])+1)."' ";
			}
			
			//Nach Präfixen suchen
			if ( is_array($_POST['prefix']) && count($_POST['prefix']) ) {
				$_POST['prefix'] = array_map('intval', $_POST['prefix']);
				$wherethread.=iif($wherethread,' AND ')." prefix IN (".implode(',', $_POST['prefix']).") ";
			}
			
			//Relevante Themen auslesen
			$data=$db->fetch("SELECT threadid FROM ".PRE."_forum_threads WHERE ( ".$wherethread." AND del=0 AND moved=0 ) ORDER BY threadid ASC");
			$inthread=get_ids($data,'threadid');
			if ( isset($keyword_threads) ) $inthread=array_intersect($inthread,$keyword_threads);
			if ( !count($inthread) ) $inthread=array(-1);
			$wherepost.=iif($wherepost,' AND ')." threadid IN (".implode(',',$inthread).") ";
			
			//////////////////////////////
			
			//Suchbegriffe: Filter erstellen
			if ( $_POST['keywords'] ) {
				if ( !count($keyword_posts) ) $keyword_posts=array(-1);
				$wherepost.=iif($wherepost,' AND ')." postid IN (".implode(',',$keyword_posts).") ";
			}
			
			//Nur Beiträge vom Autor suchen
			if ( $_REQUEST['author'] && $_POST['findposts'] ) {
				if ( $_POST['exact'] ) $wherepost.=iif($wherepost,' AND ')." username='".addslashes($_REQUEST['author'])."' ";
				else $wherepost.=iif($wherepost,' AND ')." username LIKE '%".addslashes($_REQUEST['author'])."%' ";
			}
			
			//Zeitraum
			if ( $_POST['period'] ) {
				$op=iif($_POST['period_dir']=='older','<=','>=');
				if ( $_POST['period']=='lastvisit' ) $wherepost.=iif($wherepost,' AND ')." time".$op."'".$user->info['forum_lastvisit']."'";
				elseif ( $_POST['period']=='yesterday' ) $wherepost.=iif($wherepost,' AND ')." time".$op."'".(time()-24*3600)."'";
				elseif ( $_POST['period']=='week1' ) $wherepost.=iif($wherepost,' AND ')." time".$op."'".(time()-7*24*3600)."'";
				elseif ( $_POST['period']=='week2' ) $wherepost.=iif($wherepost,' AND ')." time".$op."'".(time()-14*24*3600)."'";
				elseif ( $_POST['period']=='month1' ) $wherepost.=iif($wherepost,' AND ')." time".$op."'".(time()-30*24*3600)."'";
				elseif ( $_POST['period']=='month3' ) $wherepost.=iif($wherepost,' AND ')." time".$op."'".(time()-90*24*3600)."'";
				elseif ( $_POST['period']=='month6' ) $wherepost.=iif($wherepost,' AND ')." time".$op."'".(time()-180*24*3600)."'";
				elseif ( $_POST['period']=='year' ) $wherepost.=iif($wherepost,' AND ')." time".$op."'".(time()-365*24*3600)."'";
			}
			
			//Beiträge nach Suchkriterien filtern
			$data=$db->fetch("SELECT postid FROM ".PRE."_forum_posts WHERE ( ".$wherepost." AND del=0 ) ORDER BY postid ASC");
			$result=get_ids($data,'postid');
			$display='posts';
		}
		
		
/////////////////////////////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////// ALS THEMEN ANZEIGEN
/////////////////////////////////////////////////////////////////////////////////////////////////
		else {
			
			//Nur Beiträge vom Autor suchen
			if ( $_REQUEST['author'] && $_POST['findposts'] ) {
				if ( $_POST['exact'] ) $wherepost.=iif($wherepost,' AND ')." username='".addslashes($_REQUEST['author'])."' ";
				else $wherepost.=iif($wherepost,' AND ')." username LIKE '%".addslashes($_REQUEST['author'])."%' ";
			}
			
			//Zeitraum
			if ( $_POST['period'] ) {
				$op=iif($_POST['period_dir']=='older','<=','>=');
				if ( $_POST['period']=='lastvisit' ) $wherepost.=iif($wherepost,' AND ')." time".$op."'".$user->info['forum_lastvisit']."'";
				elseif ( $_POST['period']=='yesterday' ) $wherepost.=iif($wherepost,' AND ')." time".$op."'".(time()-24*3600)."'";
				elseif ( $_POST['period']=='week1' ) $wherepost.=iif($wherepost,' AND ')." time".$op."'".(time()-7*24*3600)."'";
				elseif ( $_POST['period']=='week2' ) $wherepost.=iif($wherepost,' AND ')." time".$op."'".(time()-14*24*3600)."'";
				elseif ( $_POST['period']=='month1' ) $wherepost.=iif($wherepost,' AND ')." time".$op."'".(time()-30*24*3600)."'";
				elseif ( $_POST['period']=='month3' ) $wherepost.=iif($wherepost,' AND ')." time".$op."'".(time()-90*24*3600)."'";
				elseif ( $_POST['period']=='month6' ) $wherepost.=iif($wherepost,' AND ')." time".$op."'".(time()-180*24*3600)."'";
				elseif ( $_POST['period']=='year' ) $wherepost.=iif($wherepost,' AND ')." time".$op."'".(time()-365*24*3600)."'";
			}
			
			//Beiträge nach Kriterien filtern
			if ( $wherepost ) {
				$data=$db->fetch("SELECT threadid FROM ".PRE."_forum_posts WHERE ( ".$wherepost." AND del=0 ) ORDER BY postid ASC");
				$inthread=get_ids($data,'threadid');
				if ( isset($keyword_threads) ) $inthread=array_intersect($inthread,$keyword_threads);
				if ( !is_array($inthread) || !count($inthread) ) $inthread=array(-1);
				$wherethread.=iif($wherethread,' AND ')." threadid IN (".implode(',',$inthread).") ";
			}
			
			//Beiträge müssen nicht gefiltert werden => Keyword-Ergebnis übernehmen
			elseif ( isset($keyword_threads) ) {
				$inthread=$keyword_threads;
				if ( !is_array($inthread) || !count($inthread) ) $inthread=array(-1);
				$wherethread.=iif($wherethread,' AND ')." threadid IN (".implode(',',$inthread).") ";
			}
			
			//////////////////////////////
			
			//Nur Themen aus den gewählten Foren
			$wherethread.=iif($wherethread,' AND ')." forumid IN (".implode(',',$inforum).") ";
			
			//Nur Themen vom Autor suchen
			if ( $_REQUEST['author'] && $_POST['findthreads'] && !$_POST['findposts'] ) {
				if ( $_POST['exact'] ) $wherethread.=iif($wherethread,' AND ')." opener='".addslashes($_REQUEST['author'])."' ";
				else $wherethread.=iif($wherethread,' AND ')." opener LIKE '%".addslashes($_REQUEST['author'])."%' ";
			}
			
			//Antworten: Filter erstellen
			if ( $_POST['answers'] ) {
				$wherethread.=iif($wherethread,' AND ')." posts>='".(intval($_POST['answers'])+1)."' ";
			}
			
			//Nach Präfixen suchen
			if ( is_array($_POST['prefix']) && count($_POST['prefix']) ) {
				$_POST['prefix'] = array_map('intval', $_POST['prefix']);
				$wherethread.=iif($wherethread,' AND ')." prefix IN (".implode(',', $_POST['prefix']).") ";
			}
			
			//Themen nach Suchkriterien filtern
			$data=$db->fetch("SELECT threadid FROM ".PRE."_forum_threads WHERE ( ".$wherethread." AND del=0 AND moved=0 ) ORDER BY threadid ASC");
			$result=get_ids($data,'threadid');
			$display='threads';
		}
		
		
/////////////////////////////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////////////////////////// SUCHE ABSCHLIESSEN
/////////////////////////////////////////////////////////////////////////////////////////////////
		//Keine Treffer
		if ( !count($result) ) {
			message($apx->lang->get('MSG_EMPTYSEARCH'),'javascript:history.back();');
		}
		
		//Suchergebnis eintragen und weiterleiten
		else {
			
			list($usec,$sec)=explode(' ',microtime()); 
			$b2=((float)$usec+(float)$sec);
			list($usec,$sec)=explode(' ',$searchstart); 
			$b1=((float)$usec+(float)$sec);
			$searchtime=round($b2-$b1,5);
			
			if ( is_countable($highlight) && count($highlight) ) $highlightme=serialize($highlight);
			else $highlightme='';
			$resultstring=dash_serialize($result);
			$hash=md5(uniqid(time()));
			$ign=serialize($ignored);
			
			$db->query("INSERT INTO ".PRE."_forum_search (userid,result,display,highlight,ignored,order_field,order_dir,time,hash) VALUES ('".$user->info['userid']."','".addslashes($resultstring)."','".addslashes($display)."','".addslashes($highlightme)."','".addslashes($ign)."','".addslashes($_POST['sortby'])."','".addslashes($_POST['sortby_dir'])."','".$searchtime."','".addslashes($hash)."')");
			$searchid=$db->insert_id();
			message($apx->lang->get('MSG_OK'),'search.php?search='.$searchid.'&amp;hash='.$hash);
		}
	}
}

/////////////////////////////////////////////////////////////////////////////////////////////// FORMULAR

$pforum = array();

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
	
	$forumdata[$i]['ID']=$res['forumid'];
	$forumdata[$i]['TITLE']=replace($res['title']);
	$forumdata[$i]['LEVEL']=$res['level'];
	$forumdata[$i]['ISCAT']=$res['iscat'];
	
	$prefixes = forum_prefixes($res['forumid']);
	if ( $prefixes ) {
		$prefixdata = array();
		foreach ( $prefixes AS $pre ) {
			$prefixdata[] = array(
				'ID'=> $pre['prefixid'],
				'TITLE' => replace($pre['title'])
			);
		}
		$pforum[] = array(
			'TITLE' => replace($res['title']),
			'PREFIX' => $prefixdata
		);
	}
}

$apx->tmpl->assign('PFORUM',$pforum);
$apx->tmpl->assign('FORUM',$forumdata);
$apx->tmpl->parse('search');



////////////////////////////////////////////////////////////////////////////////////////////////////////

$apx->tmpl->assign('PATHEND',$apx->lang->get('HEADLINE_SEARCH'));
titlebar($apx->lang->get('HEADLINE_SEARCH'));


////////////////////////////////////////////////////////////////////////////////////////////////////////
require('lib/_end.php');     ///////////////////////////////////////////////////////////////////////////
require('../lib/_end.php');  //////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

?>
