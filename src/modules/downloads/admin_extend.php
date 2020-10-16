<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

class downloads_functions
{
    public $filepath;
    public $tempfile;

    //Darf der Benutzer Bilder eines Downloads bearbeiten?
    public function access_pics($id, $action)
    {
        global $set,$db,$apx;
        $id = (int) $id;
        if (!$id) {
            die('page access: missing dlid!');
        }

        if ($apx->user->has_spright($action)) {
            return true;
        }
        list($userid) = $db->first('SELECT userid FROM '.PRE."_downloads WHERE id='".$id."' LIMIT 1");
        if ($userid == $apx->user->info['userid']) {
            return true;
        }

        return false;
    }

    //Prüfen ob ein User in einer Kategorie posten darf
    public function category_is_open($catid)
    {
        global $set,$db,$apx;
        $catid = (int) $catid;

        list($groups) = $db->first('SELECT forgroup FROM '.PRE."_downloads_cat WHERE id='".$catid."' LIMIT 1");
        $ingroups = unserialize($groups);

        if ('all' == $groups) {
            return true;
        }
        if (is_array($ingroups) && in_array($apx->user->info['groupid'], $ingroups)) {
            return true;
        }

        return false;
    }

    //Kategorien auflisten + Liste ausgeben
    public function get_catlist($selected = null)
    {
        global $set,$db,$apx;

        if (is_null($selected)) {
            $selected = $_POST['catid'];
        }

        //Neue Kategorie erstellen
        if ($apx->user->has_right('downloads.catadd')) {
            $catlist = '<option value=""></option>';
        }

        $data = $this->cat->getTree(['title', 'open', 'forgroup']);
        foreach ($data as $res) {
            $allowed = unserialize($res['forgroup']);

            if ($res['open'] && ('all' == $res['forgroup'] || (is_array($allowed) && in_array($apx->user->info['groupid'], $allowed)))) {
                $catlist .= '<option value="'.$res['id'].'" '.iif($selected == $res['id'], ' selected="selected"').' style="color:green;">'.str_repeat('&nbsp;&nbsp;', ($res['level'] - 1)).replace($res['title']).'</option>';
            } else {
                $catlist .= '<option value="" disabled="disabled">'.str_repeat('&nbsp;&nbsp;', ($res['level'] - 1)).replace($res['title']).'</option>';
            }
        }

        return $catlist;
    }

    //Autoren-Liste holen
    public function get_userlist()
    {
        global $set,$db,$apx;

        //Get Info
        $info = $db->first('SELECT a.userid,a.send_username,a.send_email,b.username FROM '.PRE.'_downloads AS a LEFT JOIN '.PRE."_user AS b USING(userid) WHERE id='".$_REQUEST['id']."' LIMIT 1");

        //Sendin
        if ($info['send_username']) {
            $userlist = '<option value="send"'.iif('send' == $_POST['userid'], ' selected="selected"').'>'.$apx->lang->get('GUEST').': '.replace($info['send_username']).iif($info['send_email'], ' ('.replace($info['send_email']).')').'</option><option value=""></option>';
        }

        //Benutzergruppen mit Downloadrechten filtern
        $data = $db->fetch('SELECT groupid FROM '.PRE."_user_groups WHERE ( gtype='admin' OR rights LIKE '%downloads.add%' )");
        foreach ($data as $res) {
            $groups[] = $res['groupid'];
        }

        $data = $db->fetch('SELECT userid,username FROM '.PRE.'_user WHERE ( groupid IN ('.implode(',', $groups).') '.iif($info['userid'], "OR userid='".$info['userid']."'").' ) ORDER BY username ASC');
        foreach ($data as $res) {
            $userlist .= '<option value="'.$res['userid'].'"'.iif('send' != $_POST['userid'] && $_POST['userid'] == $res['userid'], ' selected="selected"').'>'.replace($res['username']).'</option>';
        }

        return $userlist;
    }

    //Datei aktualisieren
    public function update_file()
    {
        global $set,$db,$apx;

        $this->filepath = '';
        if ($_REQUEST['id']) {
            list($current_file, $tempfile) = $db->first('SELECT file,tempfile FROM '.PRE."_downloads WHERE id='".$_REQUEST['id']."' LIMIT 1");
            $this->filepath = $current_file;
            $this->tempfile = $tempfile;
        }

        if ($_FILES['file_upload']['tmp_name']) {
            return $this->upload_file();
        }
        if ($_POST['file']) {
            return $this->link_file();
        }

        return true;
    }

    //Datei hochladen
    public function upload_file()
    {
        global $set,$db,$apx;
        if (!is_uploaded_file($_FILES['file_upload']['tmp_name'])) {
            die('file has not been uploaded!');
        }

        $ext = $this->mm->getext($_FILES['file_upload']['name']);
        list($special) = $db->first('SELECT special FROM '.PRE."_mediarules WHERE extension='".$ext."' LIMIT 1");
        if ('block' == $special) {
            info($apx->lang->get('INFO_NOTALLOWED'));

            return false;
        }

        //EDIT -> Alte Dateien löschen
        if ($_REQUEST['id']) {
            if ($this->filepath && file_exists(BASEDIR.getpath('uploads').$this->filepath)) {
                $this->mm->deletefile($this->filepath);
                $this->filepath = '';
            }
            if ($this->tempfile && file_exists(BASEDIR.getpath('uploads').$this->tempfile)) {
                $this->mm->deletefile($this->tempfile);
                $this->tempfile = '';
            }
        }

        $filename = $this->get_uploaded_name($_FILES['file_upload']['name']);
        $this->mm->uploadfile($_FILES['file_upload'], 'downloads', $filename);
        $this->filepath = 'downloads/'.$filename;

        return true;
    }

    //Datei verlinken
    public function link_file()
    {
        global $set,$db,$apx;

        if ($_POST['local'] && $_POST['file'] && !file_exists(BASEDIR.getpath('uploads').$_POST['file'])) {
            //$_POST['file']='';
            info($apx->lang->get('INFO_NOTEXISTS'));

            return false;
        }

        $this->filepath = $_POST['file'];

        return true;
    }

    //Dateiname nach Upload
    public function get_uploaded_name($base)
    {
        $newfilename = $this->mm->getfile($base);
        while (file_exists(BASEDIR.getpath('uploads').'downloads/'.$newfilename)) {
            ++$di;
            $newfilename = $this->mm->getname($base).'-'.$di.'.'.strtolower($this->mm->getext($base));
        }

        return $newfilename;
    }

    //Teaserpic aktualisieren
    public function update_teaserpic()
    {
        global $set,$db,$apx;

        if ($_REQUEST['id']) {
            list($current_teaserpic) = $db->first('SELECT teaserpic FROM '.PRE."_downloads WHERE id='".$_REQUEST['id']."' LIMIT 1");
            $this->teaserpicid = $_REQUEST['id'];
            $this->teaserpicpath = $current_teaserpic;
        } else {
            $tblinfo = $db->first("SHOW TABLE STATUS LIKE '".PRE."_downloads'");
            $this->teaserpicid = $tblinfo['Auto_increment'];
            $this->teaserpicpath = '';
        }

        //Mediamanager
        require_once BASEDIR.'lib/class.mediamanager.php';
        $this->mm = new mediamanager();

        if ($_POST['delpic']) {
            $this->del_teaserpic();
        }
        if ($_FILES['pic_upload']['tmp_name']) {
            return $this->upload_pic();
        }
        if ($_POST['pic_copy']) {
            return $this->copy_pic();
        }

        return true;
    }

    //Teaser-Bild hochladen
    public function upload_pic()
    {
        global $set,$db,$apx;
        if (!is_uploaded_file($_FILES['pic_upload']['tmp_name'])) {
            die('teaserpic has not been uploaded!');
        }

        //Darf das Bild bochgeladen werden?
        $ext = $this->mm->getext($_FILES['pic_upload']['name']);
        list($special) = $db->first('SELECT special FROM '.PRE."_mediarules WHERE extension='".$ext."' LIMIT 1");
        if ('block' == $special) {
            info($apx->lang->get('INFO_NOTALLOWED'));

            return false;
        }
        if (!in_array($ext, ['GIF', 'JPG', 'JPEG', 'JPE', 'PNG'])) {
            info($apx->lang->get('INFO_NOIMAGE'));

            return false;
        }

        //Datei hochladen
        $filename = 'downloads/pics/teaserpic-'.$this->teaserpicid;
        $tempfile = 'downloads/pics/temp-'.time().strtolower(random_string(10)).'.'.strtolower($ext);
        $this->mm->uploadfile($_FILES['pic_upload'], '', $tempfile);

        //Bild verarbeiten
        $feedback = $this->process_pic($tempfile, $filename);
        $this->mm->deletefile($tempfile);

        return $feedback;
    }

    //Teaser-Bild kopieren
    public function copy_pic()
    {
        global $set,$db,$apx;
        if (!file_exists(BASEDIR.getpath('uploads').$_POST['pic_copy'])) {
            die('source-file does not exist!');
        }

        $ext = $this->mm->getext($_POST['pic_copy']);
        /*list($special)=$db->first("SELECT special FROM ".PRE."_mediarules WHERE extension='".$ext."' LIMIT 1");
        if ( $special=="block" ) { info($apx->lang->get('INFO_NOTALLOWED')); return false; }*/
        if (!in_array($ext, ['GIF', 'JPG', 'JPEG', 'JPE', 'PNG'])) {
            info($apx->lang->get('INFO_NOIMAGE'));

            return false;
        }

        //Datei duplizieren
        $filename = 'downloads/pics/teaserpic-'.$this->teaserpicid;
        $tempfile = 'downloads/pics/temp-'.time().strtolower(random_string(10)).'.'.strtolower($ext);
        $this->mm->copyfile($_POST['pic_copy'], $tempfile);

        //Bild verarbeiten
        $feedback = $this->process_pic($tempfile, $filename);
        $this->mm->deletefile($tempfile);

        return $feedback;
    }

    //Bild verarbeiten
    public function process_pic($tempfile, $filename)
    {
        global $set,$db,$apx;

        require_once BASEDIR.'lib/class.image.php';
        $this->img = new image();

        //Dateinamen
        $ext = strtolower($this->mm->getext($tempfile));
        if ('gif' == $ext) {
            $ext = 'jpg';
        }
        $picfile = $filename.'.'.$ext;
        $thumbfile = $filename.'-thumb.'.$ext;

        list($source, $sourcetype) = $this->img->getimage($tempfile);

        //Bild skalieren
        if ($set['downloads']['teaserpic_popup'] && $set['downloads']['teaserpic_popup_width'] && $set['downloads']['teaserpic_popup_height']) {
            $width = $set['downloads']['teaserpic_popup_width'];
            $height = $set['downloads']['teaserpic_popup_height'];
        } else {
            $width = $set['downloads']['teaserpic_width'];
            $height = $set['downloads']['teaserpic_height'];
        }

        if ($width && $height) {
            $pic = $this->img->resize($source, $width, $height, $set['downloads']['teaserpic_quality'], 0);
        } else {
            $pic = $source;
        }
        $this->img->saveimage($pic, $sourcetype, $picfile);

        //Wenn kein Popup hier ENDE
        if (!$set['downloads']['teaserpic_popup'] || !$set['downloads']['teaserpic_width'] || !$set['downloads']['teaserpic_height']) {
            if ($picfile != $this->teaserpicpath) {
                $this->del_teaserpic();
            }
            $this->teaserpicpath = $picfile;

            return true;
        }

        //Thumbnail skalieren
        $thumb = $this->img->resize($source, $set['downloads']['teaserpic_width'], $set['downloads']['teaserpic_height'], $set['downloads']['teaserpic_quality'], 0);
        $this->img->saveimage($thumb, $sourcetype, $thumbfile);

        if ($thumbfile != $this->teaserpicpath) {
            $this->del_teaserpic();
        }
        $this->teaserpicpath = $thumbfile;

        return true;
    }

    //Aktuelles Teaser-Bild löschen
    public function del_teaserpic()
    {
        global $set,$db,$apx;
        if (!$_REQUEST['id']) {
            return;
        }
        $oldpic = $this->teaserpicpath;
        if (!$oldpic) {
            return;
        }
        //MM-Klasse laden
        if (!isset($this->mm)) {
            require_once BASEDIR.'lib/class.mediamanager.php';
            $this->mm = new mediamanager();
        }

        //Teaser-Bild löschen
        if (file_exists(BASEDIR.getpath('uploads').$oldpic)) {
            $this->mm->deletefile($oldpic);
        }

        //Thumbnail löschen
        if (file_exists(BASEDIR.getpath('uploads').str_replace('-thumb.', '.', $oldpic))) {
            $this->mm->deletefile(str_replace('-thumb.', '.', $oldpic));
        }

        $this->teaserpicpath = '';
    }

    //Klicks pro Tag
    public function stats_dlsperday()
    {
        global $set,$db,$apx;
        $datestamp = date('Ymd', time() - TIMEDIFF);

        $data = $db->fetch('SELECT sum(hits) AS count FROM '.PRE."_downloads_stats WHERE daystamp<'".$datestamp."' GROUP BY daystamp");
        if (!count($data)) {
            return '0';
        }
        foreach ($data as $res) {
            $sum += $res['count'];
        }

        return round($sum / count($data));
    }

    //Dateivolumen pro Tag
    public function stats_sizeperday()
    {
        global $set,$db,$apx;
        $datestamp = date('Ymd', time() - TIMEDIFF);

        $data = $db->fetch('SELECT sum(bytes*hits) AS count FROM '.PRE."_downloads_stats WHERE daystamp!='".$datestamp."' GROUP BY daystamp");
        if (!count($data)) {
            return '0 Bytes';
        }
        foreach ($data as $res) {
            $sum += $res['count'];
        }

        return $this->format_size(round($sum / count($data)));
    }

    //Dateigröße formatieren
    public function format_size($fsize, $digits = 1)
    {
        $fsize = (float) $fsize;
        if ($digits) {
            $format = '%01.'.$digits.'f';
        } else {
            $format = '%01d';
        }

        if ($fsize < 1024) {
            return $fsize.' Byte';
        }
        if ($fsize >= 1024 && $fsize < 1024 * 1024) {
            return  number_format($fsize / (1024), $digits, ',', '').' KB';
        }
        if ($fsize >= 1024 * 1024 && $fsize < 1024 * 1024 * 1024) {
            return number_format($fsize / (1024 * 1024), $digits, ',', '').' MB';
        }
        if ($fsize >= 1024 * 1024 * 1024 && $fsize < 1024 * 1024 * 1024 * 1024) {
            return number_format($fsize / (1024 * 1024 * 1024), $digits, ',', '').' GB';
        }

        return number_format($fsize / (1024 * 1024 * 1024 * 1024), $digits, ',', '').' TB';
    }
} //END CLASS
