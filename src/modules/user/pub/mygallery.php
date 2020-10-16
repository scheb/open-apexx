<?php

if (!$set['user']['gallery']) {
    die('function disabled!');
}
$apx->lang->drop('mygallery');
headline($apx->lang->get('HEADLINE_MYGALLERY'), mklink('user.php?action=mygallery', 'user,mygallery.html'));
titlebar($apx->lang->get('HEADLINE_MYGALLERY'));
$_REQUEST['galid'] = (int) $_REQUEST['galid'];

////////////// GALERIE AUSGEWÄHLT
if ($_REQUEST['galid']) {
    //Zugangsrechte?
    list($galid, $galtitle) = $db->first('SELECT id,title FROM '.PRE."_user_gallery WHERE id='".$_REQUEST['galid']."' AND owner='".$user->info['userid']."' LIMIT 1");
    if (!$galid) {
        die('access denied!');
    }

    //BILDER HOCHLADEN
    if ('add' == $_REQUEST['do']) {
        if ($_POST['send']) {
            require_once BASEDIR.'lib/class.mediamanager.php';
            $mm = new mediamanager();

            //Dateien temporär hochladen
            $files = [];
            for ($i = 1; $i <= 3; ++$i) {
                if (!$_FILES['upload'.$i]['tmp_name']) {
                    continue;
                }
                //Erfolgreichen Upload prüfen
                if (!$mm->uploadfile($_FILES['upload'.$i], 'temp', $mm->getfile($_FILES['upload'.$i]['tmp_name']))) {
                    continue;
                }
                $ext = strtolower($mm->getext($_FILES['upload'.$i]['name']));
                if ('gif' == $ext) {
                    $ext = 'jpg';
                }

                $files[] = [
                    'ext' => $ext,
                    'source' => 'temp/'.$mm->getfile($_FILES['upload'.$i]['tmp_name']),
                    'caption' => $_POST['caption'.$i],
                ];
            }

            //Bilderzahl auslesen
            $piccount = 0;
            if ($set['user']['gallery_maxpics']) {
                $data = $db->fetch('SELECT id FROM '.PRE."_user_gallery WHERE owner='".$user->info['userid']."'");
                $galids = get_ids($data, 'id');
                if (count($galids)) {
                    list($piccount) = $db->first('SELECT count(id) FROM '.PRE.'_user_pictures WHERE galid IN ('.implode(',', $galids).')');
                }
            }

            //Akzeptierte Dateien verarbeiten
            if (!count($files)) {
                message('back');
            } elseif ($set['user']['gallery_maxpics'] && $piccount + count($files) > $set['user']['gallery_maxpics']) {
                message($apx->lang->get('MSG_PICLIMIT', ['LIMIT' => $set['user']['gallery_maxpics']]), 'back');
            } else {
                require_once BASEDIR.'lib/class.image.php';
                $img = new image();

                $tblinfo = $db->first("SHOW TABLE STATUS LIKE '".PRE."_user_pictures'");
                $now = time(); //Einheitliche Upload-Zeit, auch wenns nicht stimmt
                $i = 0;
                foreach ($files as $file) {
                    ++$i;

                    $random = random_string(5);
                    $newname = 'pic'.'-'.($tblinfo['Auto_increment'] + $i - 1).'-'.$random.'.'.$file['ext'];
                    $newfile = 'user/gallery-'.$galid.'/'.$newname;
                    $thumbname = 'pic'.'-'.($tblinfo['Auto_increment'] + $i - 1).'-thumb-'.$random.'.'.$file['ext'];
                    $thumbfile = 'user/gallery-'.$galid.'/'.$thumbname;

                    //Bild einlesen
                    list($picture, $picturetype) = $img->getimage($file['source']);

                    //////// THUMBNAIL
                    $thumbnail = $img->resize($picture, $set['user']['gallery_thumbwidth'], $set['user']['gallery_thumbheight'], $set['user']['gallery_quality_resize'], $set['user']['gallery_thumb_fit']);
                    $img->saveimage($thumbnail, $picturetype, $thumbfile);

                    //////// BILD

                    //Skalieren
                    if (false !== $picture && !$file['noresize'] && $set['user']['gallery_picwidth'] && $set['user']['gallery_picheight']) {
                        $scaled = $img->resize(
                            $picture,
                            $set['user']['gallery_picwidth'],
                            $set['user']['gallery_picheight'],
                            $set['user']['gallery_quality_resize'],
                            0
                        );

                        if ($scaled != $picture) {
                            imagedestroy($picture);
                        }
                        $picture = $scaled;
                    }

                    //Bild erstellen
                    $img->saveimage($picture, $picturetype, $newfile);

                    //Cleanup
                    imagedestroy($picture);
                    imagedestroy($thumbnail);
                    unset($picture,$thumbnail);
                    $mm->deletefile($file['source']);

                    $db->query('INSERT INTO '.PRE."_user_pictures (galid,thumbnail,picture,caption,addtime) VALUES ('".$galid."','".$thumbfile."','".$newfile."','".addslashes($file['caption'])."','".$now."')");
                }
                $db->query('UPDATE '.PRE."_user_gallery SET lastupdate='".time()."' WHERE id='".$galid."' AND owner='".$user->info['userid']."' LIMIT 1");
                message($apx->lang->get('MSG_ADDPICS_OK'), mklink('user.php?action=mygallery&amp;galid='.$galid, 'user,mygallery.html?galid='.$galid));
            }
        } else {
            //Speicher bestimmen
            if ($set['user']['gallery_maxpics']) {
                $data = $db->fetch('SELECT id FROM '.PRE."_user_gallery WHERE owner='".$user->info['userid']."'");
                $galids = get_ids($data, 'id');
                $piccount = 0;
                if (count($galids)) {
                    list($piccount) = $db->first('SELECT count(id) FROM '.PRE.'_user_pictures WHERE galid IN ('.implode(',', $galids).')');
                }
                $space = $piccount.'/'.$set['user']['gallery_maxpics'];
                $percent = (round(($set['user']['gallery_maxpics'] - $piccount) / iif(0 == $set['user']['gallery_maxpics'], 1, $set['user']['gallery_maxpics']), 2) * 100).'%';
                $width = (100 - $percent).'%';
                $apx->tmpl->assign('SPACE', $space);
                $apx->tmpl->assign('SPACE_PERCENT', $percent);
                $apx->tmpl->assign('SPACE_WIDTH', $width);
            }

            //Limit erreicht
            if ($set['user']['gallery_maxpics'] && $piccount >= $set['user']['gallery_maxpics']) {
                message($apx->lang->get('MSG_PICLIMITREACHED', ['LIMIT' => $set['user']['gallery_maxpics']]), 'back');
                require 'lib/_end.php';
            }

            $apx->tmpl->assign('POSTTO', mklink('user.php?action=mygallery&amp;galid='.$galid.'&amp;do=add', 'user,mygallery.html?galid='.$galid.'&amp;do=add'));
            $apx->tmpl->parse('mygallery_pics_add');
        }
        require 'lib/_end.php';
    }

    //BEARBEITEN
    elseif ('edit' == $_REQUEST['do']) {
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }
        if ($_POST['send']) {
            $db->query('UPDATE '.PRE."_user_pictures SET caption='".addslashes($_POST['caption'])."' WHERE id='".$_REQUEST['id']."' AND galid='".$galid."' LIMIT 1");
            message($apx->lang->get('MSG_EDITPICS_OK'), mklink('user.php?action=mygallery&amp;galid='.$galid, 'user,mygallery.html?galid='.$galid));
        } else {
            list($caption) = $db->first('SELECT caption FROM '.PRE."_user_pictures WHERE id='".$_REQUEST['id']."' AND galid='".$galid."'");
            $input = [
                'ID' => $_REQUEST['id'],
                'CAPTION' => compatible_hsc($caption),
            ];
            tmessage('editcaption', $input);
        }
        require 'lib/_end.php';
    }

    //LÖSCHEN
    elseif ('del' == $_REQUEST['do']) {
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }
        if ($_POST['send']) {
            list($picture, $thumbnail) = $db->first('SELECT picture,thumbnail FROM '.PRE."_user_pictures WHERE id='".$_REQUEST['id']."' AND galid='".$galid."' LIMIT 1");
            require_once BASEDIR.'lib/class.mediamanager.php';
            $mm = new mediamanager();
            if ($picture && file_exists(BASEDIR.getpath('uploads').$picture)) {
                $mm->deletefile($picture);
            }
            if ($thumbnail && file_exists(BASEDIR.getpath('uploads').$thumbnail)) {
                $mm->deletefile($thumbnail);
            }
            $db->query('DELETE FROM '.PRE."_user_pictures WHERE id='".$_REQUEST['id']."' AND galid='".$galid."' LIMIT 1");
            message($apx->lang->get('MSG_DELPICS_OK'), mklink('user.php?action=mygallery&amp;galid='.$galid, 'user,mygallery.html?galid='.$galid));
        } else {
            tmessage('delgallerypic', ['ID' => $_REQUEST['id']]);
        }
        require 'lib/_end.php';
    }

    //ÜBERSICHT
    list($count) = $db->first('SELECT count(id) FROM '.PRE."_user_pictures WHERE galid='".$galid."'");
    pages(
        mklink(
            'user.php?action=mygallery&amp;galid='.$galid,
            'user,mygallery.html?galid='.$galid
        ),
        $count,
        20
    );

    //Einträge auslesen
    $data = $db->fetch('SELECT * FROM '.PRE."_user_pictures WHERE galid='".$galid."' ORDER BY addtime DESC".getlimit(20));
    if (count($data)) {
        foreach ($data as $res) {
            ++$i;

            $size = getimagesize(BASEDIR.getpath('uploads').$res['picture']);

            $tabledata[$i]['ID'] = $res['id'];
            $tabledata[$i]['IMAGE'] = HTTPDIR.getpath('uploads').$res['thumbnail'];
            $tabledata[$i]['FULLSIZE'] = HTTPDIR.getpath('uploads').$res['picture'];
            $tabledata[$i]['LINK'] = "javascript:popuppic('misc.php?action=picture&amp;pic=".$res['picture']."','".$size[0]."','".$size[1]."');";
            $tabledata[$i]['CAPTION'] = replace($res['caption']);
            $tabledata[$i]['LINK_EDIT'] = mklink('user.php?action=mygallery&amp;galid='.$galid.'&amp;do=edit&amp;id='.$res['id'], 'user,mygallery.html?galid='.$galid.'&amp;do=edit&amp;id='.$res['id']);
            $tabledata[$i]['LINK_DEL'] = mklink('user.php?action=mygallery&amp;galid='.$galid.'&amp;do=del&amp;id='.$res['id'], 'user,mygallery.html?galid='.$galid.'&amp;do=del&amp;id='.$res['id']);
        }
    }

    $apx->tmpl->assign('PICTURE', $tabledata);
    $apx->tmpl->assign('LINK_NEW', mklink('user.php?action=mygallery&amp;galid='.$galid.'&amp;do=add', 'user,mygallery.html?galid='.$galid.'&amp;do=add'));
    $apx->tmpl->parse('mygallery_pics');
}

////////////// KEINE GALERIE AUSGEWÄHLT
else {
    //GALERIE ERSTELLEN
    if ('add' == $_REQUEST['do']) {
        if ($_POST['send']) {
            if (!$_POST['title']) {
                message('back');
            } else {
                $_POST['addtime'] = $_POST['lastupdate'] = time();
                $_POST['owner'] = $user->info['userid'];
                $db->dinsert(PRE.'_user_gallery', 'owner,title,description,password,addtime,lastupdate,allowcoms');
                $nid = $db->insert_id();

                //Ordner erstellen
                require_once BASEDIR.'lib/class.mediamanager.php';
                $mm = new mediamanager();
                $mm->createdir('gallery-'.$nid, 'user');

                message($apx->lang->get('MSG_ADD_OK'), mklink('user.php?action=mygallery', 'user,mygallery.html'));
            }
        }
        $apx->tmpl->assign('POSTTO', mklink('user.php?action=mygallery&amp;do=add', 'user,mygallery.html?do=add'));
        $apx->tmpl->assign('ALLOWCOMS', 1);
        $apx->tmpl->assign('ACTION', 'add');
        $apx->tmpl->parse('mygallery_addedit');
        require 'lib/_end.php';
    }

    //GALERIE BEARBEITEN
    elseif ('edit' == $_REQUEST['do']) {
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }
        if ($_POST['send']) {
            if (!$_POST['title']) {
                message('back');
            } else {
                $db->dupdate(PRE.'_user_gallery', 'title,description,password,allowcoms', "WHERE id='".$_REQUEST['id']."' AND owner='".$user->info['userid']."' LIMIT 1");
                message($apx->lang->get('MSG_EDIT_OK'), mklink('user.php?action=mygallery', 'user,mygallery.html'));
            }
        } else {
            list($_POST['title'], $_POST['description'], $_POST['password'], $_POST['allowcoms']) = $db->first('SELECT title,description,password,allowcoms FROM '.PRE."_user_gallery WHERE id='".$_REQUEST['id']."' AND owner='".$user->info['userid']."' LIMIT 1");
        }
        $apx->tmpl->assign('POSTTO', mklink('user.php?action=mygallery&amp;do=add', 'user,mygallery.html?do=add'));
        $apx->tmpl->assign('ID', $_REQUEST['id']);
        $apx->tmpl->assign('ACTION', 'edit');
        $apx->tmpl->assign('TITLE', compatible_hsc($_POST['title']));
        $apx->tmpl->assign('DESCRIPTION', compatible_hsc($_POST['description']));
        $apx->tmpl->assign('PASSWORD', compatible_hsc($_POST['password']));
        $apx->tmpl->assign('ALLOWCOMS', intval($_POST['allowcoms']));
        $apx->tmpl->parse('mygallery_addedit');
        require 'lib/_end.php';
    }

    //GALERIE LÖSCHEN
    elseif ('del' == $_REQUEST['do']) {
        $_REQUEST['id'] = (int) $_REQUEST['id'];
        if (!$_REQUEST['id']) {
            die('missing ID!');
        }
        if ($_POST['send']) {
            $db->query('DELETE FROM '.PRE."_user_gallery WHERE id='".$_REQUEST['id']."' AND owner='".$user->info['userid']."' LIMIT 1");
            if ($db->affected_rows()) {
                $data = $db->fetch('SELECT picture,thumbnail FROM '.PRE."_user_pictures WHERE galid='".$_REQUEST['id']."'");
                $db->query('DELETE FROM '.PRE."_user_pictures WHERE galid='".$_REQUEST['id']."'");
                require_once BASEDIR.'lib/class.mediamanager.php';
                $mm = new mediamanager();
                if (count($data)) {
                    foreach ($data as $res) {
                        $picture = $res['picture'];
                        $thumbnail = $res['thumbnail'];
                        if ($picture && file_exists(BASEDIR.getpath('uploads').$picture)) {
                            $mm->deletefile($picture);
                        }
                        if ($thumbnail && file_exists(BASEDIR.getpath('uploads').$thumbnail)) {
                            $mm->deletefile($thumbnail);
                        }
                    }
                }
                $mm->deletedir('user/gallery-'.$_REQUEST['id']);
            }
            message($apx->lang->get('MSG_DEL_OK'), mklink('user.php?action=mygallery', 'user,mygallery.html'));
        } else {
            tmessage('delgallery', ['ID' => $_REQUEST['id']]);
        }
        require 'lib/_end.php';
    }

    //GALERIEN AUFLISTEN
    $data = $db->fetch('SELECT id,title FROM '.PRE."_user_gallery WHERE owner='".$user->info['userid']."'");
    if (count($data)) {
        foreach ($data as $res) {
            ++$i;

            //Bilder
            list($images) = $db->first('SELECT count(*) FROM '.PRE."_user_pictures WHERE galid='".$res['id']."'");

            //Link
            $link = mklink(
                'user.php?action=gallery&amp;id='.$user->info['userid'].'&amp;galid='.$res['id'],
                'user,gallery,'.$user->info['userid'].','.$res['id'].',0.html'
            );

            $tabledata[$i]['ID'] = $res['id'];
            $tabledata[$i]['TITLE'] = replace($res['title']);
            $tabledata[$i]['LINK'] = $link;
            $tabledata[$i]['COUNT'] = $images;
            $tabledata[$i]['LINK_EDIT'] = mklink('user.php?action=mygallery&amp;do=edit&amp;id='.$res['id'], 'user,mygallery.html?do=edit&amp;id='.$res['id']);
            $tabledata[$i]['LINK_DEL'] = mklink('user.php?action=mygallery&amp;do=del&amp;id='.$res['id'], 'user,mygallery.html?do=del&amp;id='.$res['id']);
            $tabledata[$i]['LINK_PICS'] = mklink('user.php?action=mygallery&amp;galid='.$res['id'], 'user,mygallery.html?galid='.$res['id']);
        }
    }

    $apx->tmpl->assign('GALLERY', $tabledata);
    $apx->tmpl->assign('LINK_NEW', mklink('user.php?action=mygallery&amp;do=add', 'user,mygallery.html?do=add'));
    $apx->tmpl->parse('mygallery');
}
