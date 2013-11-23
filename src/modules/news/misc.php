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



require_once(BASEDIR.getmodulepath('news').'functions.php');



//Kommentar-Popup
function misc_news_comments() {
	global $set,$db,$apx,$user;
	$_REQUEST['id']=(int)$_REQUEST['id'];
	if ( !$_REQUEST['id'] ) die('missing ID!');
	$apx->tmpl->loaddesign('blank');
	news_showcomments($_REQUEST['id']);
}



//Newsfeed ausgeben
function misc_newsfeed() {
	global $set,$db,$apx;
	$apx->tmpl->loaddesign('blank');
	header('Content-type: application/rss+xml');
	
	//Baum holen, wenn nur eine bestimmte Kategorie gezeigt werden soll
	$cattree=news_tree($_REQUEST['catid']);
	
	$data=$db->fetch("SELECT a.id,a.catid,a.title,a.subtitle,a.teaser,a.text,a.starttime,a.top,b.username,b.email,b.pub_hidemail FROM ".PRE."_news AS a LEFT JOIN ".PRE."_user AS b USING (userid) WHERE ( ".time()." BETWEEN starttime AND endtime ".iif(count($cattree),"AND catid IN (".@implode(',',$cattree).")")." ".section_filter()." ) ORDER BY starttime DESC LIMIT 20");
	
	//Kategorien auslesen
	$catinfo=news_catinfo(get_ids($data,'catid'));
	
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			//Link
			$link=mklink(
				'news.php?id='.$res['id'],
				'news,id'.$res['id'].urlformat($res['title']).'.html'
			);
			
			//Text
			if( $set['news']['teaser'] && $res['teaser'] ) $text=$res['teaser'];
			else $text=$res['text'];
			
			$tabledata[$i]['ID']=$res['id'];
			$tabledata[$i]['TITLE']=rss_replace($res['title']);
			$tabledata[$i]['SUBTITLE']=rss_replace($res['subtitle']);
			$tabledata[$i]['TIME']=date('r',$res['starttime']); //Kein TIMEDIFF weil Zeitverschiebung mit angegeben!
			$tabledata[$i]['TEXT']=rss_replace(preg_replace('#{IMAGE\(([0-9]+)\)}#s','',$text));
			$tabledata[$i]['CATTITLE']=rss_replace($catinfo[$res['catid']]['title']);
			$tabledata[$i]['LINK']=HTTP_HOST.$link;
			$tabledata[$i]['USERNAME']=rss_replace($res['username']);
			$tabledata[$i]['EMAIL']=rss_replace(iif(!$res['pub_hidemail'],$res['email']));
			$tabledata[$i]['EMAIL_ENCRYPTED']=rss_replace(iif(!$res['pub_hidemail'],cryptMail($res['email'])));
			$tabledata[$i]['TOP']=$res['top'];
		}
	}
	
	$apx->tmpl->assign('WEBSITENAME',$set['main']['websitename']);
	$apx->tmpl->assign('NEWS',$tabledata);
	$apx->tmpl->parse('rss','news');
}

?>