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


# STATS CLASS
# ===========

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');


class action {

function action() {
	echo '<link rel="stylesheet" href="../modules/stats/images/style.css" type="text/css" />';
}


function getweekstamp($time) {
	//Wenn Kalenderwoche >= 52 und wir uns im Januar befinden
	//-> Kalenderwoche gehört zum vorherigen Jahr!
	if ( intval(date('W',$time-TIMEDIFF))>=52 && intval(date('n',$time-TIMEDIFF))==1 ) {
		return (date('Y',$time-TIMEDIFF)-1).sprintf('%02d',date('W',$time-TIMEDIFF));
	}
	
	return date('Y',$time-TIMEDIFF).sprintf('%02d',date('W',$time-TIMEDIFF));
}



//***************************** Besucherzahlen *****************************
function visitors() {
	global $set,$db,$apx,$html;
	$hournow=(int)date('H',time()-TIMEDIFF);
	$minnow=(int)date('i',time()-TIMEDIFF);
	
	//PRECACHING Besucherkurve
	$res=$db->first("SELECT sum(uniques) AS uniques,sum(uniques_0h),sum(uniques_1h),sum(uniques_2h),sum(uniques_3h),sum(uniques_4h),sum(uniques_5h),sum(uniques_6h),sum(uniques_7h),sum(uniques_8h),sum(uniques_9h),sum(uniques_10h),sum(uniques_11h),sum(uniques_12h),sum(uniques_13h),sum(uniques_14h),sum(uniques_15h),sum(uniques_16h),sum(uniques_17h),sum(uniques_18h),sum(uniques_19h),sum(uniques_20h),sum(uniques_21h),sum(uniques_22h),sum(uniques_23h) FROM ".PRE."_stats WHERE ( daystamp BETWEEN '".date('Ymd',time()-31*24*3600-TIMEDIFF)."' AND '".date('Ymd',time()-1*24*3600-TIMEDIFF)."' ) LIMIT 30",1);
	
	//Summe errechnen
	$sum=$res['uniques'];
	if ( !$sum ) $sum=1;
	
	//$data verarbeiten
	foreach ( $res AS $key => $count ) {
		if ( substr($key,0,4)!='sum(' ) continue;
		$key=intval(substr($key,12,-2));
		$newdata[$key]=$count/$sum*100;
	}
	
	//Kurze Besucherübersicht + Prognose
	list($count_record,$record_time)=$db->first("SELECT sum(uniques) AS count,time FROM ".PRE."_stats GROUP BY daystamp ORDER BY count DESC LIMIT 1");
	list($count_total)=$db->first("SELECT sum(uniques) AS count FROM ".PRE."_stats");
	list($count_today)=$db->first("SELECT sum(uniques) AS count FROM ".PRE."_stats WHERE daystamp='".date('Ymd',time()-TIMEDIFF)."'");
	$count_total+=$set['stats']['startcount'];
	
	//Bruchteil der aktuellen Stunde
	$sumpercent=($newdata[$hournow]/100)*$minnow/60;
	
	//Vergangene Stunden
	if ( $hournow>0 ) {
		for ( $i=0; $i<=($hournow-1); $i++ ) {
			$sumpercent+=$newdata[$i]/100;
		}
	}
	
	if ( $count_today && $sumpercent ) $progn=round($count_today/$sumpercent);
	
	$apx->tmpl->assign('RECORD',number_format($count_record,0,'','.'));
	$apx->tmpl->assign('RECORD_DATE',apxdate($record_time));
	$apx->tmpl->assign('TOTAL',number_format($count_total,0,'','.'));
	$apx->tmpl->assign('TODAY',number_format($count_today,0,'','.'));
	$apx->tmpl->assign('PROGNOSIS',number_format($progn,0,'','.'));
	
	//LAYER DEFINIEREN
	$layerdef[]=array('LAYER_TABLE','action.php?action=stats.visitors',!$_REQUEST['what']);
	$layerdef[]=array('LAYER_GRAPH','action.php?action=stats.visitors&amp;what=graph',$_REQUEST['what']=='graph');
	
	$html->layer_header($layerdef);
	
	if ( $_REQUEST['what']=='graph' ) $this->visitors_graph($newdata);
	else $this->visitors_table($progn);
	
	$html->layer_footer($layerdef);
}



//TABELLEN
function visitors_table($progn) {
	global $set,$db,$apx;
	
	//Die letzten 30 Tage
	$data=$db->fetch("SELECT sum(uniques) AS count,daystamp,time FROM ".PRE."_stats WHERE daystamp>='".date('Ymd',time()-29*24*3600-TIMEDIFF)."' GROUP BY daystamp ORDER BY daystamp DESC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			if ( $res['count']>$max ) $max=$res['count'];
		}
		
		if ( $progn>$max ) $max=$progn;
		if ( !$max ) $max=1;
		
		foreach ( $data AS $res ) {
			++$i;
			$width=round((($res['count']/$max)*100));
			$lastdata[$i]['DATE']=strip_tags(apxdate($res['time']));
			$lastdata[$i]['COUNT']=number_format($res['count'],0,'','.');
			$lastdata[$i]['WIDTH']=$width.'%';
			
			if ( $res['daystamp']==date('Ymd',time()-TIMEDIFF) ) {
				$prognwidth=round($progn/$max*100);
				if ( $prognwidth ) $lastdata[$i]['PROGNOSIS']=($prognwidth-$width).'%';
			}
		}
	}
	
	$data=$db->fetch("SELECT sum(uniques) AS count,sum(hits) AS hitscount,count(uniques) AS days,LEFT(daystamp,6) AS monthstamp,time FROM ".PRE."_stats WHERE daystamp<'".date('Ymd',time()-TIMEDIFF)."' GROUP BY monthstamp ORDER BY monthstamp DESC LIMIT 6");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			/*if ( date('m',time()-TIMEDIFF)==date('m',$res['time']-TIMEDIFF) ) $days=(date('d',time()-TIMEDIFF)-1);
			else $days=date('t',$res['time']-TIMEDIFF);*/
			$days=$res['days'];
			
			$monthdata[$i]['MONTH']=getcalmonth(intval(substr($res['monthstamp'],4))).' '.substr($res['monthstamp'],0,4);
			$monthdata[$i]['VISITORS']=number_format($res['count'],0,'','.');
			$monthdata[$i]['HITS']=number_format($res['hitscount'],0,'','.');
			$monthdata[$i]['AVG_VISITORS']=number_format(round($res['count']/$days),0,'','.');
			$monthdata[$i]['AVG_HITS']=number_format(round($res['hitscount']/$days),0,'','.');
		}
	}
	
	$apx->tmpl->assign('LAST',$lastdata);
	$apx->tmpl->assign('MONTH',$monthdata);
	
	$apx->tmpl->parse('visitors_table');
}



//DIAGRAMME
function visitors_graph($newdata) {
	global $set,$db,$apx;
	
	$data=$db->fetch("SELECT uniques AS count,hits,daystamp,time FROM ".PRE."_stats WHERE daystamp>='".date('Ymd',time()-49*24*3600-TIMEDIFF)."' GROUP BY daystamp ORDER BY daystamp ASC");
	if ( count($data) ) {
		
		//Maximum holen
		foreach ( $data AS $res ) {
			if ( $res['count']>$max ) $max=$res['count'];
			if ( $res['hits']>$max ) $max=$res['hits'];
		}
		
		//Base generieren
		if ( strlen($max)>1 ) {
			for ( $i=1; $i<=10; $i++ ) {
			$scale_base=pow(10,(strlen($max)-1))*$i;
				if ( $scale_base>=$max ) break;
			}
		}
		else $scale_base=$max;
		
		$scale1=round($scale_base/4);
		$scale2=round($scale_base/4*2);
		$scale3=round($scale_base/4*3);
		$scale4=$scale_base;
		
		//Statistik generieren
		foreach ( $data AS $res ) {
			++$i;
			$last50data[$i]['DATE']=strip_tags(apxdate($res['time']));
			$last50data[$i]['COUNT']=number_format($res['count'],0,'','.');
			$last50data[$i]['HEIGHT']=round((($res['count']/$scale_base)*298));
			$last50data[$i]['HITS_COUNT']=number_format($res['hits'],0,'','.');
			$last50data[$i]['HITS_HEIGHT']=round((($res['hits']/$scale_base)*298))-$last50data[$i]['HEIGHT'];
		}
	}
	
	$apx->tmpl->assign('LAST50_SCALE1',$scale1);
	$apx->tmpl->assign('LAST50_SCALE2',$scale2);
	$apx->tmpl->assign('LAST50_SCALE3',$scale3);
	$apx->tmpl->assign('LAST50_SCALE4',$scale4);
	$apx->tmpl->assign('LAST50',$last50data);
	
	unset($scale_base,$scale1,$scale2,$scale3,$scale4,$max);
	
	
	//Die letzten 30 Wochen
	$weekstamp=$this->getweekstamp(time()-29*7*24*3600);
	$data=$db->fetch("SELECT sum(uniques) AS count,weekstamp,time FROM ".PRE."_stats WHERE weekstamp>='".$weekstamp."'  GROUP BY weekstamp ORDER BY weekstamp ASC");
	if ( count($data) ) {
		
		//Maximum holen
		foreach ( $data AS $res ) {
			if ( $res['count']>$max ) $max=$res['count'];
		}
		
		//Base generieren
		if ( strlen($max)>1 ) {
			for ( $i=1; $i<=10; $i++ ) {
			$scale_base=pow(10,(strlen($max)-1))*$i;
				if ( $scale_base>=$max ) break;
			}
		}
		else $scale_base=$max;
		
		$scale1=round($scale_base/4);
		$scale2=round($scale_base/4*2);
		$scale3=round($scale_base/4*3);
		$scale4=$scale_base;
		
		//Statistik generieren
		foreach ( $data AS $res ) {
			++$i;
			$week=$this->getweekstamp($res['time']);
			$weekdata[$i]['YEAR']=(int)substr($week,0,4);
			$weekdata[$i]['WEEK']=(int)substr($week,4,2);
			$weekdata[$i]['COUNT']=number_format($res['count'],0,'','.');
			$weekdata[$i]['HEIGHT']=round((($res['count']/$scale_base)*298));
		}
	}
	
	$apx->tmpl->assign('WEEK_SCALE1',$scale1);
	$apx->tmpl->assign('WEEK_SCALE2',$scale2);
	$apx->tmpl->assign('WEEK_SCALE3',$scale3);
	$apx->tmpl->assign('WEEK_SCALE4',$scale4);
	$apx->tmpl->assign('WEEK',$weekdata);
	
	unset($scale_base,$scale1,$scale2,$scale3,$scale4,$max);
	
	
	//Besucher je Wochentag
	$weekstamp=$this->getweekstamp(time()-29*7*24*3600);
	$data=$db->fetch("SELECT sum(uniques) AS count,weekday,time FROM ".PRE."_stats WHERE weekstamp>='".$weekstamp."'  GROUP BY weekday ORDER BY weekday ASC");
	if ( count($data) ) {
		
		//Maximum holen
		$wdmax = 0;
		$wdsum = 0;
		foreach ( $data AS $res ) {
			$wdsum += $res['count'];
			if ( $res['count']>$wdmax ) $wdmax=$res['count'];
		}
		if ( !$wdsum ) $wdsum = 1;
		$max = $wdmax/$wdsum*100;
		
		//Base generieren
		$maxint=ceil($max);
		if ( strlen($maxint)>1 ) {
			for ( $i=1; $i<=10; $i++ ) {
			$scale_base=pow(10,(strlen($maxint)-1))*$i;
				if ( $scale_base>=$maxint ) break;
			}
		}
		else $scale_base=$maxint;
		if ( !$scale_base ) $scale_base=100;
		
		$scale1=round($scale_base/4);
		$scale2=round($scale_base/4*2);
		$scale3=round($scale_base/4*3);
		$scale4=$scale_base;
		
		$weekdays = array(
			0 => 'Mon',
			1 => 'Tue',
			2 => 'Wed',
			3 => 'Thu',
			4 => 'Fri',
			5 => 'Sat',
			6 => 'Sun'
		);
		
		//Statistik generieren
		foreach ( $data AS $res ) {
			++$i;
			$percent = $res['count']/$wdsum*100;
			$weekday = $weekdays[$res['weekday']];
			$weekdaydata[$i]['DAY']=getweekday($weekday);
			$weekdaydata[$i]['COUNT']=number_format($res['count'],0,'','.');
			$weekdaydata[$i]['HEIGHT']=round((($percent/$scale_base)*298));
			$weekdaydata[$i]['PERCENT']=round($percent,1);
		}
	}
	
	$apx->tmpl->assign('WEEKDAY_SCALE1',$scale1);
	$apx->tmpl->assign('WEEKDAY_SCALE2',$scale2);
	$apx->tmpl->assign('WEEKDAY_SCALE3',$scale3);
	$apx->tmpl->assign('WEEKDAY_SCALE4',$scale4);
	$apx->tmpl->assign('WEEKDAY',$weekdaydata);
	
	unset($scale_base,$scale1,$scale2,$scale3,$scale4,$max);
	
	
	//Besucher nach Stunden
	if ( isset($newdata) ) {
		
		//Maximum holen
		foreach ( $newdata AS $percent ) {
			if ( $percent>$max ) $max=$percent;
		}
		
		//Base generieren
		$maxint=ceil($max);
		if ( strlen($maxint)>1 ) {
			for ( $i=1; $i<=10; $i++ ) {
			$scale_base=pow(10,(strlen($maxint)-1))*$i;
				if ( $scale_base>=$maxint ) break;
			}
		}
		else $scale_base=$maxint;
		if ( !$scale_base ) $scale_base=100; 
		
		$scale1=round($scale_base/4);
		$scale2=round($scale_base/4*2);
		$scale3=round($scale_base/4*3);
		$scale4=$scale_base;
		
		//Statistik generieren
		for ( $i=0; $i<=23; $i++ ) {
			$hourdata[$i]['PERCENT']=round($newdata[$i],1);
			$hourdata[$i]['HEIGHT']=round((($newdata[$i]/$scale_base)*298));
			$hourdata[$i]['HOUR']=sprintf('%02d',$i).':XX';
			$hourdata[$i]['BASE']=$i;
		}
	}
	
	$apx->tmpl->assign('HOUR_SCALE1',$scale1);
	$apx->tmpl->assign('HOUR_SCALE2',$scale2);
	$apx->tmpl->assign('HOUR_SCALE3',$scale3);
	$apx->tmpl->assign('HOUR_SCALE4',$scale4);
	$apx->tmpl->assign('HOUR',$hourdata);
	
	$apx->tmpl->parse('visitors_graph');
}



//***************************** Browser *****************************
function agents() {
	global $set,$db,$apx;

	$icons=array(
		'Firefox' => 'browser_firefox.gif',
		'Mozilla' => 'browser_mozilla.gif',
		'Chrome' => 'browser_chrome.gif',
		'MSIE' => 'browser_msie.gif',
		'MSIE 6.0' => 'browser_msie.gif',
		'MSIE 7.0' => 'browser_msie.gif',
		'MSIE 8.0' => 'browser_msie.gif',
		'MSIE 9.0' => 'browser_msie.gif',
		'MSIE 10.0' => 'browser_msie.gif',
		'MSIE 11.0' => 'browser_msie.gif',
		'Edge' => 'browser_msie.gif',
		'Opera' => 'browser_opera.gif',
		'Konqueror' => 'browser_konqueror.gif',
		'Netscape' => 'browser_netscape.gif',
		'Lynx' => 'browser_lynx.gif',
		'Safari' => 'browser_safari.gif',
		'SEARCHENGINE' => 'searchengine.gif',
		'UNKNOWN' => 'unknown.gif'
	);
	
	list($count)=$db->first("SELECT sum(hits) FROM ".PRE."_stats_userenv WHERE ( daystamp>='".date('Ymd',time()-3*24*3600-TIMEDIFF)."' AND type='browser' )");
	$data=$db->fetch("SELECT value AS browser,sum(hits) AS count FROM ".PRE."_stats_userenv WHERE ( daystamp>='".date('Ymd',time()-3*24*3600-TIMEDIFF)."' AND type='browser' ) GROUP BY value ORDER BY count DESC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			if ( $res['browser']=='SEARCHENGINE' || $res['browser']=='UNKNOWN' ) $browsername=$apx->lang->get($res['browser']);
			else $browsername=$res['browser'];
			
			$tabledata[$i]['ICON']='<img src="../'.getmodulepath('stats').'images/'.$icons[$res['browser']].'" alt="'.replace($res['browser']).'" style="vertical-align:middle;" /> ';
			$tabledata[$i]['NAME']=$browsername;
			$tabledata[$i]['COUNT']=number_format($res['count'],0,'','.');
			$tabledata[$i]['PERCENT']=round($res['count']/$count*100,1);
			$tabledata[$i]['WIDTH']=round($res['count']/$count*100).'%';
		}
	}
	
	$apx->tmpl->assign('BROWSER',$tabledata);
	$apx->tmpl->parse('browser');
}



//***************************** Betriebssysteme *****************************
function os() {
	global $set,$db,$apx;

	$icons=array(
	    'Windows 10' => 'os_windows.gif',
	    'Windows 8.1' => 'os_windows.gif',
	    'Windows 8' => 'os_windows.gif',
		'Windows 7' => 'os_windows.gif',
		'Windows Vista' => 'os_windows.gif',
		'Windows XP' => 'os_windows.gif',
		'Windows 2003 Server' => 'os_windows.gif',
		'Windows 2000' => 'os_windows.gif',
		'Windows ME' => 'os_windows.gif',
		'Windows NT 4.0' => 'os_windows.gif',
		'Windows 98/95' => 'os_windows.gif',
		'Linux' => 'os_linux.gif',
		'Mac OS' => 'os_macos.gif',
		'FreeBSD' => 'os_freebsd.gif',
		'Sun OS' => 'os_sunos.gif',
		'IRIX' => 'os_irix.gif',
		'BeOS' => 'os_beos.gif',
		'OS/2' => 'os_os2.gif',
		'AIX' => 'os_aix.gif',
		'iOS' => 'os_macos.gif',
		'Android' => 'os_linux.gif',
		'SEARCHENGINE' => 'searchengine.gif',
		'UNKNOWN' => 'unknown.gif'
	);
	
	list($count)=$db->first("SELECT sum(hits) FROM ".PRE."_stats_userenv WHERE ( daystamp>='".date('Ymd',time()-3*24*3600-TIMEDIFF)."' AND type='os' )");
	$data=$db->fetch("SELECT value AS os,sum(hits) AS count FROM ".PRE."_stats_userenv WHERE ( daystamp>='".date('Ymd',time()-3*24*3600-TIMEDIFF)."' AND type='os' ) GROUP BY value ORDER BY count DESC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			if ( $res['os']=='SEARCHENGINE' || $res['os']=='UNKNOWN' ) $osname=$apx->lang->get($res['os']);
			else $osname=$res['os'];
			
			$tabledata[$i]['ICON']='<img src="../'.getmodulepath('stats').'images/'.$icons[$res['os']].'" alt="'.replace($res['os']).'" style="vertical-align:middle;" /> ';
			$tabledata[$i]['NAME']=$osname;
			$tabledata[$i]['COUNT']=number_format($res['count'],0,'','.');
			$tabledata[$i]['PERCENT']=round($res['count']/$count*100,1);
			$tabledata[$i]['WIDTH']=round($res['count']/$count*100).'%';
		}
	}
	
	$apx->tmpl->assign('OS',$tabledata);
	$apx->tmpl->parse('os');
}



//***************************** Countries *****************************
function countries() {
	global $set,$db,$apx;
	
	list($count)=$db->first("SELECT sum(hits) FROM ".PRE."_stats_userenv WHERE ( daystamp>='".date('Ymd',time()-3*24*3600-TIMEDIFF)."' AND type='country' )");
	$data=$db->fetch("SELECT value AS country,sum(hits) AS count FROM ".PRE."_stats_userenv WHERE ( daystamp>='".date('Ymd',time()-3*24*3600-TIMEDIFF)."' AND type='country' ) GROUP BY value ORDER BY count DESC");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			
			if ( $res['country']=='UNKNOWN' ) $cname=$apx->lang->get($res['country']);
			else $cname=$res['country'];
			
			$tabledata[$i]['NAME']=$cname;
			$tabledata[$i]['COUNT']=number_format($res['count'],0,'','.');
			$tabledata[$i]['PERCENT']=round($res['count']/$count*100,1);
			$tabledata[$i]['WIDTH']=round($res['count']/$count*100).'%';
		}
	}
	
	$apx->tmpl->assign('COUNTRY',$tabledata);
	$apx->tmpl->parse('countries');
}



//***************************** Referer *****************************
function referer() {
	global $set,$db,$apx,$html;
	
	//Nur dieser Host
	if ( $_REQUEST['host'] ) {	
	$data=$db->fetch("SELECT url,sum(hits) AS hits FROM ".PRE."_stats_referer WHERE ( daystamp>='".date('Ymd',time()-7*24*3600)."' AND host='".addslashes($_REQUEST['host'])."' ) GROUP BY url ORDER BY hits DESC");
		if ( count($data) ) {
			foreach ( $data AS $res ) {
				++$i;
				$mostdata[$i]['HITS']=number_format($res['hits'],0,'','.');
				$mostdata[$i]['URL']=iif(strlen($res['url'])>90,substr($res['url'],0,60).' ... '.substr($res['url'],(strlen($res['url'])-25)),$res['url']);
				$mostdata[$i]['LINK']='../misc.php?action=redirect&amp;url='.urlencode($res['url']);
			}
		}
		
		$apx->tmpl->assign('HOST','http://'.replace($_REQUEST['host']));
		$apx->tmpl->assign('MOST',$mostdata);
		$apx->tmpl->parse('referer_hosts');
		return;
	}
	
	//Layer, wenn Sonderrechte
	if ( $apx->user->has_spright('stats.referer') ) {
		
		//Host-Filter löschen
		if ( isset($_REQUEST['delfilter']) ) {
			if ( $_POST['send'] ) {
				if ( !checkToken() ) printInvalidToken();
				else {
					$filter=$set['stats']['referer_filter'];
					unset($filter[$_REQUEST['delfilter']]);
					$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($filter))."' WHERE module='stats' AND varname='referer_filter' LIMIT 1");
					$set['stats']['referer_filter']=$filter;
					printJSRedirect('action.php?action=stats.referer&what=filter');
				}
			}
			else {
				tmessageOverlay('filterdel', array('DELFILTER' => $_REQUEST['delfilter']));
			}
			return;
		}
		
		//Host-Filter hinzufügen
		elseif ( isset($_POST['addfilter']) ) {
			if ( !checkToken() ) printInvalidToken();
			elseif ( !$_POST['addfilter'] ) infoNotComplete();
			else {
				$_POST['addfilter']=strtolower($_POST['addfilter']);
				if ( !in_array($_POST['addfilter'],$set['stats']['referer_filter']) ) {
					$filter=$set['stats']['referer_filter'];
					$filter[]=$_POST['addfilter'];
					$db->query("UPDATE ".PRE."_config SET value='".addslashes(serialize($filter))."' WHERE module='stats' AND varname='referer_filter' LIMIT 1");
					$set['stats']['referer_filter']=$filter;
				}
				printJSRedirect('action.php?action=stats.referer&what=filter');
			}
			return;
		}
		
		echo '<p class="hint">'.$apx->lang->get('REFERER_INFO').'</p>';
		$layerdef[]=array('LAYER_STATS','action.php?action=stats.referer',!$_REQUEST['what']);
		$layerdef[]=array('LAYER_FILTER','action.php?action=stats.referer&amp;what=filter',$_REQUEST['what']=='filter');
		$html->layer_header($layerdef);
		
		//Host-Filter anzeigen
		if ( $_REQUEST['what']=='filter' ) {
			$filter=$set['stats']['referer_filter'];
			asort($filter);
			
			//Ausgabe
			$col[]=array('HOST',100,'');
			if ( count($filter) ) {
				foreach ( $filter AS $i => $host ) {
					$tabledata[$i]['COL1']=replace($host);
					$tabledata[$i]['OPTIONS']=optionHTMLOverlay('del.gif', 'stats.referer', 'what=filter&delfilter='.$i, $apx->lang->get('CORE_DEL'));
				}
			}
			
			$apx->tmpl->assign('TABLE',$tabledata);
			$html->table($col);
			
			$apx->tmpl->assign('DOMAIN',compatible_hsc($_REQUEST['domain']));
			$apx->tmpl->parse('referer_filter');
			return;
		}
		
	} //Filter SP Ende
	
	//Hostfilter erzeugen
	$hostfilter='';
	if ( count($set['stats']['referer_filter']) ) {
		$normalfilter = array();
		$likefilter = array();
		foreach ( $set['stats']['referer_filter'] AS $key => $filter ) {
			if ( strpos($filter,'*')!==false ) {
				$likefilter[] = addslashes(str_replace('*','%',$filter));
			}
			else {
				$normalfilter[] = addslashes($filter);
			}
		}
		$totalfilter = array();
		if ( count($likefilter) ) {
			foreach ( $likefilter AS $like ) {
				$totalfilter[] = " host LIKE '".$like."' ";
			}
		}
		if ( count($normalfilter) ) {
			$totalfilter[] =" host IN ('".implode("','",$normalfilter)."') ";
		}
		$hostfilter = " AND NOT ( ".implode(' OR ',$totalfilter)." ) ";
	}
	
	//Allgemeine Referer-Statistik
	$data=$db->fetch("SELECT url,sum(hits) AS hits,host FROM ".PRE."_stats_referer WHERE daystamp>='".date('Ymd',time()-7*24*3600-TIMEDIFF)."' ".$hostfilter." GROUP BY url ORDER BY hits DESC LIMIT 20");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$mostdata[$i]['HITS']=number_format($res['hits'],0,'','.');
			$mostdata[$i]['URL']=iif(strlen($res['url'])>90,substr($res['url'],0,60).' ... '.substr($res['url'],(strlen($res['url'])-25)),$res['url']);
			$mostdata[$i]['LINK']='../misc.php?action=redirect&amp;url='.urlencode($res['url']);
			if ( $apx->user->has_spright('stats.referer') ) {
				$mostdata[$i]['FILTER']='action.php?action=stats.referer&amp;what=filter&amp;domain='.urlencode($res['host']);
			}
		}
	}
	
	$data=$db->fetch("SELECT url,host FROM ".PRE."_stats_referer WHERE daystamp>='".date('Ymd',time()-7*24*3600-TIMEDIFF)."' ".$hostfilter." ORDER BY time DESC LIMIT 20");
	if ( count($data) ) {
		$hits=array();
		foreach ( $data AS $res ) {
			++$i;
			if ( !isset($hits[$res['url']]) ) list($hits[$res['url']])=$db->first("SELECT sum(hits) FROM ".PRE."_stats_referer WHERE ( daystamp>='".date('Ymd',time()-7*24*3600-TIMEDIFF)."' AND hash='".addslashes(md5($res['url']))."' AND url='".addslashes($res['url'])."' )");
			$latestdata[$i]['HITS']=number_format($hits[$res['url']],0,'','.');
			$latestdata[$i]['URL']=iif(strlen($res['url'])>90,substr($res['url'],0,60).' ... '.substr($res['url'],(strlen($res['url'])-25)),$res['url']);
			$latestdata[$i]['LINK']='../misc.php?action=redirect&amp;url='.urlencode($res['url']);
			if ( $apx->user->has_spright('stats.referer') ) {
				$latestdata[$i]['FILTER']='action.php?action=stats.referer&amp;what=filter&amp;domain='.urlencode($res['host']);
			}
		}
	}
	
	$data=$db->fetch("SELECT sum(hits) AS hits,host FROM ".PRE."_stats_referer WHERE daystamp>='".date('Ymd',time()-7*24*3600)."' ".$hostfilter." GROUP BY host ORDER BY hits DESC LIMIT 20");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$hostdata[$i]['HITS']=number_format($res['hits'],0,'','.');
			$hostdata[$i]['URL']='http://'.$res['host'];
			$hostdata[$i]['LINK']='action.php?action=stats.referer&amp;host='.$res['host'];
		}
	}
	
	$apx->tmpl->assign('MOST',$mostdata);
	$apx->tmpl->assign('LATEST',$latestdata);
	$apx->tmpl->assign('HOST',$hostdata);
	
	$apx->tmpl->parse('referer');
	
	//Layer ENDE, wenn Sonderrechte
	if ( $apx->user->has_spright('stats.referer') ) {
		$html->layer_footer();
	}
}



//***************************** Suchbegriffe *****************************
function searched() {
	global $set,$db,$apx;
	
	$data=$db->fetch("SELECT searchstring,sum(hits) AS hits FROM ".PRE."_stats_referer WHERE ( daystamp>='".date('Ymd',time()-30*24*3600-TIMEDIFF)."' AND searchstring!='' ) GROUP BY searchstring ORDER BY hits DESC, time DESC LIMIT 30");
	if ( count($data) ) {
		foreach ( $data AS $res ) {
			++$i;
			$tabledata[$i]['HITS']=number_format($res['hits'],0,'','.');
			$tabledata[$i]['STRING']=htmlentities($res['searchstring'],ENT_QUOTES,"UTF-8");
		}
	}
	
	$apx->tmpl->assign('SEARCH',$tabledata);
	$apx->tmpl->parse('searched');
}



} //END CLASS


?>