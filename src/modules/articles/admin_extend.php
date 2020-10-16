<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

class articles_functions
{
    public $artpicid;
    public $artpicpath;

    //Darf der Benutzer Seiten eines Artikels bearbeiten?
    public function access_pages($id)
    {
        global $set,$db,$apx;
        $id = (int) $id;
        if (!$id) {
            die('page access: missing artid!');
        }

        if ($apx->user->has_spright('articles.edit')) {
            return true;
        }
        list($userid) = $db->first('SELECT userid FROM '.PRE."_articles WHERE id='".$id."' LIMIT 1");
        if ($userid == $apx->user->info['userid']) {
            return true;
        }

        return false;
    }

    //Nachbarseiten
    public function get_brothers()
    {
        global $db;

        if (is_int($_REQUEST['pageid'])) {
            list($thisord) = $db->first('SELECT ord FROM '.PRE."_articles_pages WHERE ( artid='".$_REQUEST['id']."' AND id='".$_REQUEST['pageid']."' ) LIMIT 1");
        } else {
            $thisord = 9999999;
        }
        if ($thisord) {
            list($brother1) = $db->first('SELECT id FROM '.PRE."_articles_pages WHERE ( artid='".$_REQUEST['id']."' AND ord<'".$thisord."' ) ORDER BY ord DESC LIMIT 1");
            list($brother2) = $db->first('SELECT id FROM '.PRE."_articles_pages WHERE ( artid='".$_REQUEST['id']."' AND ord>'".$thisord."' ) ORDER BY ord ASC LIMIT 1");
        }

        return [(int) $brother1, (int) $brother2];
    }

    //Prüfen ob ein User in einer Kategorie posten darf
    public function category_is_open($catid)
    {
        global $set,$db,$apx;
        $catid = (int) $catid;

        list($groups) = $db->first('SELECT forgroup FROM '.PRE."_articles_cat WHERE id='".$catid."' LIMIT 1");
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
        if ($apx->user->has_right('articles.catadd')) {
            $catlist = '<option value=""></option>';
        }

        if ($set['articles']['subcats']) {
            $data = $this->cat->getTree(['title', 'open', 'forgroup']);
        } else {
            $data = $db->fetch('SELECT id,title,open,forgroup FROM '.PRE.'_articles_cat ORDER BY title ASC');
        }
        if (!count($data)) {
            return '';
        }
        foreach ($data as $res) {
            $allowed = unserialize($res['forgroup']);
            if ($res['level']) {
                $space = str_repeat('&nbsp;&nbsp;', ($res['level'] - 1));
            }

            if ($res['open'] && ('all' == $res['forgroup'] || (is_array($allowed) && in_array($apx->user->info['groupid'], $allowed)))) {
                $catlist .= '<option value="'.$res['id'].'" '.iif($selected == $res['id'], ' selected="selected"').' style="color:green;">'.$space.replace($res['title']).'</option>';
            } else {
                $catlist .= '<option value="" disabled="disabled">'.$space.replace($res['title']).'</option>';
            }
        }

        return $catlist;
    }

    //Autoren-Liste holen
    public function get_userlist()
    {
        global $set,$db,$apx;

        //Get Info
        $info = $db->first('SELECT a.userid,b.username FROM '.PRE.'_articles AS a LEFT JOIN '.PRE."_user AS b USING(userid) WHERE id='".$_REQUEST['id']."' LIMIT 1");

        //Benutzergruppen mit Artikelrechten filtern
        $data = $db->fetch('SELECT groupid FROM '.PRE."_user_groups WHERE ( gtype='admin' OR rights LIKE '%articles.add%' )");
        foreach ($data as $res) {
            $groups[] = $res['groupid'];
        }

        $data = $db->fetch('SELECT userid,username FROM '.PRE.'_user WHERE ( groupid IN ('.implode(',', $groups).') '.iif($info['userid'], "OR userid='".$info['userid']."'").' ) ORDER BY username ASC');
        foreach ($data as $res) {
            $userlist .= '<option value="'.$res['userid'].'"'.iif('send' != $_POST['userid'] && $_POST['userid'] == $res['userid'], ' selected="selected"').'>'.replace($res['username']).'</option>';
        }

        return $userlist;
    }

    //Artikelpic aktualisieren
    public function update_artpic()
    {
        global $set,$db,$apx;

        if ($_REQUEST['id']) {
            list($current_artpic) = $db->first('SELECT artpic FROM '.PRE."_articles WHERE id='".$_REQUEST['id']."' LIMIT 1");
            $this->artpicid = $_REQUEST['id'];
            $this->artpicpath = $current_artpic;
        } else {
            $tblinfo = $db->first("SHOW TABLE STATUS LIKE '".PRE."_articles'");
            $this->artpicid = $tblinfo['Auto_increment'];
            $this->artpicpath = '';
        }

        //Mediamanager
        require_once BASEDIR.'lib/class.mediamanager.php';
        $this->mm = new mediamanager();

        if ($_POST['delpic']) {
            $this->del_artpic();
        }
        if ($_FILES['pic_upload']['tmp_name']) {
            return $this->upload_pic();
        }
        if ($_POST['pic_copy']) {
            return $this->copy_pic();
        }

        return true;
    }

    //Artikel-Bild hochladen
    public function upload_pic()
    {
        global $set,$db,$apx;
        if (!is_uploaded_file($_FILES['pic_upload']['tmp_name'])) {
            die('artpic has not been uploaded!');
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
        $filename = 'articles/artpic-'.$this->artpicid;
        $tempfile = 'articles/temp-'.time().strtolower(random_string(10)).'.'.strtolower($ext);
        $this->mm->uploadfile($_FILES['pic_upload'], '', $tempfile);

        //Bild verarbeiten
        $feedback = $this->process_pic($tempfile, $filename);
        $this->mm->deletefile($tempfile);

        return $feedback;
    }

    //Artikel-Bild kopieren
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
        $filename = 'articles/artpic-'.$this->artpicid;
        $tempfile = 'articles/temp-'.time().strtolower(random_string(10)).'.'.strtolower($ext);
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
        if ($set['articles']['artpic_popup'] && $set['articles']['artpic_popup_width'] && $set['articles']['artpic_popup_height']) {
            $width = $set['articles']['artpic_popup_width'];
            $height = $set['articles']['artpic_popup_height'];
        } else {
            $width = $set['articles']['artpic_width'];
            $height = $set['articles']['artpic_height'];
        }

        if ($width && $height) {
            $pic = $this->img->resize($source, $width, $height, $set['articles']['artpic_quality'], 0);
        } else {
            $pic = $source;
        }
        $this->img->saveimage($pic, $sourcetype, $picfile);

        //Wenn kein Popup hier ENDE
        if (!$set['articles']['artpic_popup'] || !$set['articles']['artpic_width'] || !$set['articles']['artpic_height']) {
            if ($picfile != $this->artpicpath) {
                $this->del_artpic();
            }
            $this->artpicpath = $picfile;

            return true;
        }

        //Thumbnail skalieren
        $thumb = $this->img->resize($source, $set['articles']['artpic_width'], $set['articles']['artpic_height'], $set['articles']['artpic_quality'], 0);
        $this->img->saveimage($thumb, $sourcetype, $thumbfile);

        if ($thumbfile != $this->artpicpath) {
            $this->del_artpic();
        }
        $this->artpicpath = $thumbfile;

        return true;
    }

    //Aktuelles Artikel-Bild löschen
    public function del_artpic()
    {
        global $set,$db,$apx;
        if (!$_REQUEST['id']) {
            return;
        }
        $oldpic = $this->artpicpath;
        if (!$oldpic) {
            return;
        }
        //MM-Klasse laden
        if (!isset($this->mm)) {
            require_once BASEDIR.'lib/class.mediamanager.php';
            $this->mm = new mediamanager();
        }

        //Artikel-Bild löschen
        if (file_exists(BASEDIR.getpath('uploads').$oldpic)) {
            $this->mm->deletefile($oldpic);
        }

        //Thumbnail löschen
        if (file_exists(BASEDIR.getpath('uploads').str_replace('-thumb.', '.', $oldpic))) {
            $this->mm->deletefile(str_replace('-thumb.', '.', $oldpic));
        }

        $this->artpicpath = '';
    }
} //END CLASS
