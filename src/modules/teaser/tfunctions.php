<?php 

//Security-Check
if ( !defined('APXRUN') ) die('You are not allowed to execute this file directly!');



//Teasers
function teaser_show($count=0, $start=0, $group=0, $template='teaser') {
	global $set,$db,$apx;
	
	$count=(int)$count;
	$start=(int)$start;
	$group=(int)$group;
	$tmpl=new tengine;
	
	$groupfilter = '';
	if ( $group ) {
		$groupfilter = " AND `group`='".$group."'";
	}
	
	if ( $set['teaser']['orderby']==1 ) $data=$db->fetch("SELECT id,title,text,link,image,hits FROM ".PRE."_teaser WHERE '".time()."' BETWEEN starttime AND endtime ".$groupfilter.section_filter()." ORDER BY ord ASC".iif($count," LIMIT ".iif($start,$start.',').$count));
	elseif ( $set['teaser']['orderby']==2 ) $data=$db->fetch("SELECT id,title,text,link,image,hits FROM ".PRE."_teaser WHERE '".time()."' BETWEEN starttime AND endtime ".$groupfilter.section_filter()." ORDER BY addtime DESC".iif($count," LIMIT ".iif($start,$start.',').$count));
	else {
		$data=$db->fetch("SELECT id,title,text,link,image,hits FROM ".PRE."_teaser WHERE '".time()."' BETWEEN starttime AND endtime".$groupfilter.section_filter());
		if ( count($data) ) {
			srand((float)microtime()*1000000);
			shuffle($data);
		
			if ( $count ) {
				foreach ( $data AS $res ) {
					++$ii;
					$newdata[]=$res;
					if ( $ii==$count ) break;
				}
				$data=$newdata;
				unset($newdata);
			}
		}
	}
	
	if ( count($data) ) {
		$apx->lang->drop('teaser','teaser');
		
		foreach ( $data AS $res ) {
			++$i;
			$affdata[$i]['TITLE']=$res['title'];
			$affdata[$i]['TEXT']=$res['text'];
			$affdata[$i]['URL']=$res['link'];
			$affdata[$i]['IMAGE']=iif($res['image'], HTTPDIR.getpath('uploads').$res['image']);
			$affdata[$i]['HITS']=number_format($res['hits'],0,'','.');
			$affdata[$i]['LINK']=HTTPDIR.'misc.php?action=teaserlink&amp;id='.$res['id'];
		}
	}
	
	$tmpl->assign('TEASER',$affdata);
	$tmpl->parse($template,'teaser');
}

?>