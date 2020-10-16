<?php

define('APXRUN', true);

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'includes/_start.php';  /////////////////////////////////////////////////////// SYSTEMSTART ///
////////////////////////////////////////////////////////////////////////////////////////////////////////

if ($_REQUEST['displaynavi']) {
    $apx->tmpl->loaddesign('blank');
    $apx->tmpl->assign('NAVI', $html->mm_navi());
    $apx->tmpl->parse('mediamanager_navi', '/');
}

/////////////////////////////////////////////////////////////////////////////////////// ORDNERSTRUKTUR
else {
    //Ordner auslesen
    function readout_dir($dirname)
    {
        $dirs = [];
        $handle = opendir(BASEDIR.getpath('uploads').$dirname);
        while ($file = readdir($handle)) {
            if ('.' == $file || '..' == $file) {
                continue;
            }
            if (is_dir(BASEDIR.getpath('uploads').iif($dirname, $dirname.'/').$file)) {
                $dirs[] = $file;
            }
        }
        closedir($handle);

        return $dirs;
    }

    //Unterordner auslesen und Templatevariablen erzeugen
    function get_subtree($dir)
    {
        $dirs = readout_dir($dir);
        $numdirs = count($dirs);
        $dirdata = [];
        $i = 0;
        foreach ($dirs as $dirname) {
            ++$i;
            $dirdata[] = [
                'NAME' => compatible_hsc($dirname),
                'PATH' => iif($dir, $dir.'/').$dirname,
                'LAST' => $i == $numdirs,
            ];
        }

        return $dirdata;
    }

    require_once BASEDIR.'lib/class.mediamanager.php';
    $mm = new mediamanager();
    if ($_REQUEST['dir']) {
        $apx->tmpl->assign('DIR', get_subtree($_REQUEST['dir']));
        $apx->tmpl->loaddesign('blank');
        $apx->tmpl->parse('mediamanager_subtree', '/');
    } else {
        $apx->tmpl->assign('DIR', get_subtree(''));
        $apx->lang->dropaction('mediamanager', 'index');
        $apx->tmpl->loaddesign('blank');
        $apx->tmpl->parse('mediamanager_explorer', '/');
    }
}

////////////////////////////////////////////////////////////////////////////////////////////////////////
require 'includes/_end.php';  ////////////////////////////////////////////////////// SCRIPT BEENDEN ///
////////////////////////////////////////////////////////////////////////////////////////////////////////
