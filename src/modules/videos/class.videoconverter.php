<?php

//Security-Check
if (!defined('APXRUN')) {
    die('You are not allowed to execute this file directly!');
}

class VideoConverter
{
    public $logHandle;

    //Software
    public $ffmpeg;
    public $flvtool;
    public $mencoder;

    //Qualität
    public $vBitrate;
    public $aBitrate;
    public $width;
    public $height;

    //Screenshots
    public $picwidth;
    public $picheight;
    public $thumbwidth;
    public $thumbheight;
    public $quality_resize;
    public $watermark;
    public $watermark_position;
    public $watermark_transp;

    //Konstruktor
    public function VideoConverter($id, $cfg)
    {
        //Tools
        $this->ffmpeg = $cfg['ffmpeg'];
        $this->flvtool = $cfg['flvtool2'];
        $this->mencoder = $cfg['mencoder'];

        //Videos
        $this->vBitrate = $cfg['vbitrate'] * 1000;
        $this->aBitrate = $cfg['abitrate'] * 1000;
        $this->width = $cfg['flvwidth'];
        $this->height = $cfg['flvheight'];

        //Screenshots
        $this->picwidth = $cfg['picwidth'];
        $this->picheight = $cfg['picheight'];
        $this->thumbwidth = $cfg['thumbwidth'];
        $this->thumbheight = $cfg['thumbheight'];
        $this->quality_resize = $cfg['quality_resize'];
        $this->watermark = $cfg['watermark'];
        $this->watermark_position = $cfg['watermark_position'];
        $this->watermark_transp = $cfg['watermark_transp'];

        //Log starten
        $this->logHandle = fopen(BASEDIR.getpath('uploads').'videos/logs/'.$id.'.log', 'a');
        ob_start();
    }

    //Log-Schreiben erzwingen
    public function forceLogOut()
    {
        global $logWriter;
        fwrite($this->logHandle, ob_get_contents());
        ob_clean();
    }

    //Größe auslesen
    public function getVideoSize($output)
    {
        preg_match('#Video:.*?, ([0-9]+)x([0-9]+)[^0-9]#si', $output, $matches);
        if (!isset($matches[1])) {
            $size = [
                'width' => $this->width,
                'height' => $this->height,
            ];
        } else {
            $size = [
                'width' => $matches[1],
                'height' => $matches[2],
            ];
        }

        return $size;
    }

    //Framerate auslesen
    public function getVideoFramerate($output)
    {
        preg_match('#Video:.*?, ([0-9]+(?:\.[0-9]+)?) tb#si', $output, $matches);
        if (isset($matches[1])) {
            $fps = floatval($matches[1]);
            if ($fps >= 10 && $fps <= 60) {
                return $fps;
            }
        }

        return 0;
    }

    //Audio-Rate auslesen
    public function getAudioSamplingrate($output)
    {
        preg_match('#Audio:.*?([0-9]+) Hz#si', $output, $matches);
        if (isset($matches[1])) {
            return intval($matches[1]);
        }

        return 0;
    }

    //Spieldauer in Sekunden umrechnen
    public function getSeconds($duration)
    {
        $pp = explode(':', $duration);
        if (!count($pp) >= 3) {
            return 0;
        }
        $seconds = intval($pp[2]);
        $seconds += intval($pp[1]) * 60;
        $seconds += intval($pp[0]) * 60 * 60;

        return $seconds;
    }

    //Spieldauer auslesen
    public function getVideoDuration($output)
    {
        $duration = '';
        preg_match('#Duration: ([0-9]{2}:[0-9]{2}:[0-9]{2})(?:\.([0-9]+))?#si', $output, $matches);
        if (isset($matches[1])) {
            $duration = $matches[1];
        }
        $duration = $this->getSeconds($duration);
        if (isset($matches[2])) {
            $duration += floatval($matches[2]) / 10;
        }

        return $duration;
    }

    //Videoformat auslesen
    public function getVideoFormat($output)
    {
        $format = 'unknown';
        preg_match('#Video: (.+?),#si', $output, $matches);
        if (isset($matches[1])) {
            $format = $matches[1];
        } elseif (preg_match('#Input \#0, swf#si', $output, $matches)) {
            return false;
        }

        return $format;
    }

    //Film-Info auslesen
    public function getMovieInfo($movie)
    {
        //Video-Info auslesen
        $cmd = $this->ffmpeg.' -i "'.$movie.'" 2>&1';
        exec($cmd, $output, $returnval);
        $out = implode("\n", $output);
        echo $cmd."\n".$out."\nreturns ".$returnval."\n";
        if (0 != $returnval && 1 != $returnval) {
            echo "FFMPEG INFO failed!\n";
            echo "\n--------------------------------------------\n\n";

            return false; //fehlgeschlagen
        }

        //Informationen auslesen
        $movieInfo = $this->getVideoSize($out);
        $movieInfo['duration'] = $this->getVideoDuration($out);
        $movieInfo['format'] = $this->getVideoFormat($out);
        $movieInfo['samplingrate'] = $this->getAudioSamplingrate($out);
        $movieInfo['fps'] = $this->getVideoFramerate($out);
        if ('29.97' == $movieInfo['fps']) {
            $movieInfo['fps'] = '30000/1001';
        }
        echo 'Size: '.$movieInfo['width'].'x'.$movieInfo['height']."\n";
        echo 'Duration: '.$movieInfo['duration']."\n";
        echo 'Format: '.$movieInfo['format']."\n";
        echo 'Audio Rate: '.$movieInfo['samplingrate']."\n";
        echo 'Framerate: '.$movieInfo['fps']."\n";
        echo "\n--------------------------------------------\n\n";

        return $movieInfo;
    }

    //Länge des FLV-Movies überprüfen
    public function checkFLVDuration($flvDuration, $orgDuration)
    {
        echo 'Compare duration '.$orgDuration.' vs. '.$flvDuration."\n";

        $ratio = $flvDuration / $orgDuration;

        //Weniger als 10% Unterschied
        return $ratio >= 0.90 && $ratio <= 1.1;
    }

    //Konvertierung durchführen
    public function convert($movieFile, $flvFile, $movieInfo)
    {
        $mencoderFormats = [
            'wmv',
            'wmv1',
            'wmv2',
            'wmv3',
            'mpeg4',
            'msmpeg4',
            'msmpeg4v2',
        ];
        if ($this->mencoder && in_array($movieInfo['format'], $mencoderFormats)) {
            return $this->convertMEncoder($movieFile, $movieInfo, $flvFile);
        }

        return $this->convertFFMPEG($movieFile, $movieInfo, $flvFile);
    }

    //Mit FFMPEG konvertieren
    public function convertFFMPEG($movieFile, $movieInfo, $flvFile)
    {
        //Samplingrate muss passen
        if (in_array($movieInfo['samplingrate'], [44100, 22050, 11025])) {
            $samplingRate = $movieInfo['samplingrate'];
        } else {
            $samplingRate = 44100;
        }

        //FPS
        if ($movieInfo['fps']) {
            $fps = $movieInfo['fps'];
        } else {
            $fps = '30000/1001';
        }

        //Größe
        $newSize = $this->calcNewSize($movieInfo['width'], $movieInfo['height'], $this->width, $this->height);

        //Konvertierung durchführen
        //Lassen die Framerate hier weg, weil es scheinbar auch ohne geht
        $returnval = 0;
        $cmd = $this->ffmpeg.' -y -i "'.$movieFile.'" -s '.$newSize['width'].'x'.$newSize['height'].' -f flv -ar '.$samplingRate.' -ab '.$this->aBitrate.' -b '.$this->vBitrate.' -r 30 "'.$flvFile.'" 2>&1';
        exec($cmd, $output, $returnval);
        echo $cmd."\n".implode("\n", $output)."\nreturns ".$returnval."\n";
        echo "\n--------------------------------------------\n\n";

        //Konvertierung fehlgeschlagen => mit MEncoder versuchen
        if (0 != $returnval || !file_exists($flvFile) || 0 == filesize($flvFile)) {
            if ($this->mencoder) {
                echo "FFMPEG failed! => Trying MENCODER\n";

                return $this->convertMEncoder($movieFile, $movieInfo, $flvFile);
            }

            echo 'FFMPEG failed!';

            return false;
        }

        //Länge des Ergebnisses überprüfen
        /*$flvMovieInfo = $this->getMovieInfo($flvFile);
        if ( !$this->checkFLVDuration($movieInfo['duration'], $flvMovieInfo['duration']) ) {
            echo "Duration-check failed!\n";
            echo "\n--------------------------------------------\n\n";
            @unlink($flvFile);
            return false;
        }
        echo "\n--------------------------------------------\n\n";*/

        //FLVTool laufen lassen
        echo "Running FLVTool\n";
        $flvToolSuccess = $this->runFLVTool($flvFile);
        if (!$flvToolSuccess) {
            echo "FLVTOOL failed!\n";
            echo "\n--------------------------------------------\n\n";
            @unlink($flvFile);

            return false;
        }
        echo "\n--------------------------------------------\n\n";

        return true;
    }

    //Mit MEncoder konvertieren
    public function convertMEncoder($movieFile, $movieInfo, $flvFile)
    {
        //Samplingrate muss passen
        if (in_array($movieInfo['samplingrate'], [44100, 22050, 11025])) {
            $samplingRate = $movieInfo['samplingrate'];
        } else {
            $samplingRate = 44100;
        }

        //FPS
        if ($movieInfo['fps']) {
            $fps = $movieInfo['fps'];
        } else {
            $fps = '30000/1001';
        }

        //Größe
        $newSize = $this->calcNewSize($movieInfo['width'], $movieInfo['height'], $this->width, $this->height);

        //MPEG-Datei mit MEncoder erzeugen
        //Temporäre Datei mit 4000 Bit Video und 224 Bit Audio
        $mpegFile = $flvFile.'.avi';
        $cmd = $this->mencoder.' -ovc lavc -lavcopts vbitrate=4000 -oac mp3lame -lameopts cbr:br=224:mode=0 -af volnorm -ofps '.$fps.' -vf scale='.$newSize['width'].':'.$newSize['height'].' "'.$movieFile.'" -o "'.$mpegFile.'"  2>&1';
        exec($cmd, $output, $returnval);
        echo $cmd."\n".implode("\n", $output)."\nreturns ".$returnval."\n";
        echo "\n--------------------------------------------\n\n";
        if (0 != $returnval || !file_exists($mpegFile) || 0 == filesize($mpegFile)) {
            echo 'MENCODER failed!';
            @unlink($mpegFile);

            return false;
        }

        //FFMPEG
        //Lassen die Framerate hier weg, weil es scheinbar auch ohne geht
        unset($output);
        $cmd = $this->ffmpeg.' -y -i "'.$mpegFile.'" -f flv -ar '.$samplingRate.' -ab '.$this->aBitrate.' -b '.$this->vBitrate.' "'.$flvFile.'" 2>&1';
        exec($cmd, $output, $returnval);
        echo $cmd."\n".implode("\n", $output)."\nreturns ".$returnval."\n";
        echo "\n--------------------------------------------\n\n";
        if (0 != $returnval || !file_exists($flvFile) || 0 == filesize($flvFile)) {
            echo 'MENCODER+FFMPEG failed!';
            @unlink($mpegFile);

            return false;
        }

        //MPEG-Datei löschen
        @unlink($mpegFile);

        //Länge des Ergebnisses überprüfen
        /*$flvMovieInfo = $this->getMovieInfo($flvFile);
        if ( !$this->checkFLVDuration($movieInfo['duration'], $flvMovieInfo['duration']) ) {
            echo "Duration-check failed!\n";
            echo "\n--------------------------------------------\n\n";
            @unlink($flvFile);
            return false;
        }
        echo "\n--------------------------------------------\n\n";*/

        //FLVTool laufen lassen
        echo "Running FLVTool\n";
        $flvToolSuccess = $this->runFLVTool($flvFile);
        if (!$flvToolSuccess) {
            echo "FLVTOOL failed!\n";
            echo "\n--------------------------------------------\n\n";
            @unlink($flvFile);

            return false;
        }
        echo "\n--------------------------------------------\n\n";

        return true;
    }

    //FLV-Tool ausführen
    public function runFLVTool($flvFile)
    {
        $cmd = $this->flvtool.' -U '.$flvFile;
        exec($cmd, $output, $returnval);
        echo $cmd."\n".implode("\n", $output)."\nreturns ".$returnval."\n";

        return 0 == $returnval;
    }

    //Neue Größe des Videos ausrechnen
    public function calcNewSize($imgWidth, $imgHeight, $width, $height)
    {
        $xtoy = $imgWidth / $imgHeight;

        if (0 == $width) {
            return [round($height * $xtoy), $height];
        }
        if (0 == $height) {
            return [$width, round($width / $xtoy)];
        }
        $newWidth = $width;
        $newHeight = round($width / $xtoy);
        if ($newHeight > $height) {
            $newWidth = round($height * $xtoy);
            $newHeight = $height;
        }

        if (1 == $newWidth % 2) {
            --$newWidth;
        }
        if (1 == $newHeight % 2) {
            --$newHeight;
        }

        return ['width' => $newWidth, 'height' => $newHeight];
    }

    //Screenshots erzeugen
    public function makeScreenshots($flvFile, $duration, $id)
    {
        $files = [];
        $screenid = 1;

        require_once BASEDIR.'lib/class.image.php';
        $img = new image();

        //Screenshots machen
        for ($i = 1; $i <= 10; $i += 2) {
            unset($output);

            $newname = 'pic-'.$id.'-'.$screenid.'.jpg';
            $newfile = 'videos/screens/'.$newname;
            $thumbname = 'pic-'.$id.'-'.$screenid.'-thumb.jpg';
            $thumbfile = 'videos/screens/'.$thumbname;

            //Screenshot erzeugen
            $screenfrom = floor($duration / 11 * $i);
            $cmd = $this->ffmpeg.' -itsoffset -'.$screenfrom.' -i '.$flvFile.' -vframes 1 -f mjpeg -sameq '.BASEDIR.getpath('uploads').$newfile.' 2>&1';
            exec($cmd, $output, $returnval);
            echo $cmd."\n".implode("\n", $output)."\nreturns ".$returnval."\n";
            echo "\n--------------------------------------------\n\n";
            $this->forceLogOut();

            //Bild einlesen
            list($picture, $picturetype) = $img->getimage($newfile);

            //////// THUMBNAIL
            $thumbnail = $img->resize($picture, $this->thumbwidth, $this->thumbheight, $this->quality_resize, false);
            $img->saveimage($thumbnail, $picturetype, $thumbfile);

            //////// BILD

            //Bild skalieren
            if (false !== $picture && $this->picwidth && $this->picheight) {
                $scaled = $img->resize(
                    $picture,
                    $this->picwidth,
                    $this->picheight,
                    $this->quality_resize,
                    0
                );

                if ($scaled != $picture) {
                    imagedestroy($picture);
                }
                $picture = $scaled;
            }

            //Wasserzeichen einfügen
            if (false !== $picture && $this->watermark) {
                $watermarked = $img->watermark(
                    $picture,
                    $this->watermark,
                    $this->watermark_position,
                    $this->watermark_transp
                );

                if ($watermarked != $picture) {
                    imagedestroy($picture);
                }
                $picture = $watermarked;
            }

            //Bild erstellen
            $img->saveimage($picture, $picturetype, $newfile);

            //Cleanup
            imagedestroy($picture);
            imagedestroy($thumbnail);
            unset($picture,$thumbnail);

            $files[] = [
                'thumbnail' => $thumbfile,
                'picture' => $newfile,
            ];

            ++$screenid;
        }

        return $files;
    }

    //Konverter Ende
    public function close()
    {
        $this->forceLogOut();
        @fclose($this->logHandle);
        ob_end_clean();
    }
}
