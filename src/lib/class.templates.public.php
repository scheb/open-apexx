<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

class templates extends tengine
{
    public $designid = 'default';
    public $titlebar = '';
    public $headline = [];

    ////////////////////////////////////////////////////////////////////////////////// -> STARTUP

    public function templates()
    {
        global $apx,$set;

        $this->assign_static('CHARSET', $set['main']['charset']);
        $this->assign_static('ACTIVE_MODULE', ''); //Alt, Abwärtskompatiblität
        $this->assign_static('APEXX_MODULE', '');

        //Basis
        if (defined('BASEREL')) {
            $this->assign_static('BASEREL', BASEREL);
        }

        //Zeiger
        $this->parsevars['APEXX_MODULE'] = &$apx->active_module;
        $this->parsevars['ACTIVE_MODULE'] = &$apx->active_module;

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

        ob_start();
        parent::tengine(true);
    }

    ////////////////////////////////////////////////////////////////////////////////// -> HEADLINES

    //Headline
    public function headline($text, $url = '')
    {
        $this->headline[] = [
            'TEXT' => $text,
            'LINK' => $url,
        ];
    }

    //Titelleiste
    public function titlebar($text)
    {
        $this->titlebar = $text;
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
        global $apx,$set;

        //Output holen und löschen
        $this->cache = ob_get_contents();
        ob_end_clean();

        if ('blank' != $this->designid) {
            $addcode = '';
            $extendcode = '';

            //Autoload Javascript
            $addcode = '<script type="text/javascript" src="'.HTTPDIR.'lib/yui/yahoo-dom-event/yahoo-dom-event.js"></script>';
            $addcode .= '<script language="JavaScript" type="text/javascript" src="'.HTTPDIR.'lib/javascript/global.js"></script>'."\n";
            $addcode .= '<script language="JavaScript" type="text/javascript" src="'.HTTPDIR.'lib/javascript/public_popups.js"></script>'."\n";
            $addcode .= '<script language="JavaScript" type="text/javascript" src="'.HTTPDIR.'lib/javascript/tooltip.js"></script>'."\n";

            //Cronjobs ausführen
            //Prüfen, ob das Script bereits installiert ist!
            if (isset($set['main']) && $cronhash = cronexec()) {
                $extendcode .= '<img src="'.HTTPDIR.'lib/cronjob/cron.php?hash='.$cronhash.'" width="1" height="1" alt="" />';
            }

            $this->cache = $addcode.$this->cache.$extendcode;
        }

        //Headlines
        $this->assign('HEADLINE', $this->headline);
        $this->assign('TITLEBAR', $this->titlebar);

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
}
