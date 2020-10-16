<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

class templates extends tengine
{
    public $designid = 'default';
    public $headline = [];

    ////////////////////////////////////////////////////////////////////////////////// -> STARTUP

    public function templates()
    {
        global $apx,$set;

        //Variablen
        $this->assign_static('SECTOKEN', $apx->session->get('sectoken'));
        $this->assign_static('CHARSET', $set['main']['charset']);
        $this->assign_static('ACTIVE_MODULE', $apx->module());
        $this->assign_static('ACTIVE_ACTION', $apx->action());
        $this->assign_static('SET_MAXUPLOAD', str_replace('M', 'MB', ini_get('upload_max_filesize')));
        $this->assign_static('SID', SID);
        $this->assign_static('SERVER_REQUEST_METHOD', $_SERVER['REQUEST_METHOD']);

        //Benutzerinfos
        $this->assign_static('LOGGED_ID', $apx->user->info['userid']);
        $this->assign_static('LOGGED_USERNAME', replace($apx->user->info['username']));
        $this->assign_static('LOGGED_EDITOR', $apx->user->info['admin_editor']);

        //Sektionen verwendet?
        if (count($apx->sections)) {
            $this->assign_static('SET_SECTIONS', 1);
            $this->assign_static('SELECTED_SECTION', $apx->session->get('section'));
        }

        //Set-Variablen
        foreach ($set as $module => $settings) {
            if (!is_array($settings)) {
                continue;
            }
            foreach ($settings as $key => $value) {
                $this->assign_static('SET_'.strtoupper($module).'_'.strtoupper($key), $value);
            }
        }

        //Installierte Module
        foreach ($apx->modules as $module => $trash) {
            $this->assign_static('MODULE_'.strtoupper($module), 1);
        }

        //Rechte
        foreach ($apx->actions as $module => $actions) {
            foreach ($actions as $action => $trash) {
                if ($apx->user->has_right($module.'.'.$action)) {
                    $this->assign_static('RIGHT_'.strtoupper($module).'_'.strtoupper($action), 1);
                }

                if ($apx->user->has_spright($module.'.'.$action)) {
                    $this->assign_static('SPRIGHT_'.strtoupper($module).'_'.strtoupper($action), 1);
                }
            }
        }

        ob_start();

        parent::tengine(true);
    }

    // Compiler erzeugen
    public function create_compiler($filepath)
    {
        $compiler = parent::create_compiler($filepath);
        $compiler->autoinclude = false;

        return $compiler;
    }

    ////////////////////////////////////////////////////////////////////////////////// -> HEADLINE

    public function headline($text)
    {
        $this->headline[]['TEXT'] = $text;
    }

    ////////////////////////////////////////////////////////////////////////////////// -> AUSGABE

    //Design laden
    public function loaddesign($prefix = 'default')
    {
        $this->designid = $prefix;
    }

    //Ausgabe vorbereiten
    public function out()
    {
        global $apx,$html;

        //Output holen und löschen
        $this->cache = ob_get_contents();
        ob_clean();

        $this->assign('HEADLINE', $apx->lang->get('TITLE_'.strtoupper($apx->module()).'_'.strtoupper($apx->action())));
        $this->extend('JS_FOOTER', $script);

        //Assign Content
        $this->assign('CONTENT', $this->cache);

        //Ausgabe erfolgt
        $this->final_flush();
    }

    //CACHE-AUSGABE
    public function final_flush()
    {
        //Leeres Design -> Nur Cache ausgeben
        if ('blank' == $this->designid) {
            echo $this->cache;

            return;
        }

        //Design + Cache ausgeben
        $this->parse('design_'.$this->designid, '/');

        //Errorreport
        $this->show_errorreport();
    }

    //Error-Report
    public function show_errorreport()
    {
        if (!$this->errorreport) {
            return;
        }
        echo '<div class="error">'.$this->errorreport.'</div>';
    }
} //END CLASS
