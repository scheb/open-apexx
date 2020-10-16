<?php

class EpiCurl
{
    const timeout = 3;
    public static $inst = null;
    public static $singleton = 0;
    private $mc;
    private $msgs;
    private $running;
    private $execStatus;
    private $selectStatus;
    private $sleepIncrement = 1.1;
    private $requests = [];
    private $responses = [];
    private $properties = [];

    public function __construct()
    {
        if (0 == self::$singleton) {
            throw new Exception('This class cannot be instantiated by the new keyword.  You must instantiate it using: $obj = EpiCurl::getInstance();');
        }

        $this->mc = curl_multi_init();
        $this->properties = [
            'code' => CURLINFO_HTTP_CODE,
            'time' => CURLINFO_TOTAL_TIME,
            'length' => CURLINFO_CONTENT_LENGTH_DOWNLOAD,
            'type' => CURLINFO_CONTENT_TYPE,
            'url' => CURLINFO_EFFECTIVE_URL,
        ];
    }

    public function addCurl($ch)
    {
        $key = $this->getKey($ch);
        $this->requests[$key] = $ch;
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, [$this, 'headerCallback']);

        $code = curl_multi_add_handle($this->mc, $ch);

        // (1)
        if (CURLM_OK === $code || CURLM_CALL_MULTI_PERFORM === $code) {
            do {
                $code = $this->execStatus = curl_multi_exec($this->mc, $this->running);
            } while (CURLM_CALL_MULTI_PERFORM === $this->execStatus);

            return new EpiCurlManager($key);
        }

        return $code;
    }

    public function getResult($key = null)
    {
        if (null != $key) {
            if (isset($this->responses[$key])) {
                return $this->responses[$key];
            }

            $innerSleepInt = $outerSleepInt = 1;
            while ($this->running && (CURLM_OK == $this->execStatus || CURLM_CALL_MULTI_PERFORM == $this->execStatus)) {
                usleep($outerSleepInt);
                $outerSleepInt = max(1, ($outerSleepInt * $this->sleepIncrement));
                $ms = curl_multi_select($this->mc, 0);
                if ($ms > 0) {
                    do {
                        $this->execStatus = curl_multi_exec($this->mc, $this->running);
                        usleep($innerSleepInt);
                        $innerSleepInt = max(1, ($innerSleepInt * $this->sleepIncrement));
                    } while (CURLM_CALL_MULTI_PERFORM == $this->execStatus);
                    $innerSleepInt = 1;
                }
                $this->storeResponses();
                if (isset($this->responses[$key]['data'])) {
                    return $this->responses[$key];
                }
                $runningCurrent = $this->running;
            }

            return null;
        }

        return false;
    }

    public static function getInstance()
    {
        if (null == self::$inst) {
            self::$singleton = 1;
            self::$inst = new EpiCurl();
        }

        return self::$inst;
    }

    private function getKey($ch)
    {
        return (string) $ch;
    }

    private function headerCallback($ch, $header)
    {
        $_header = trim($header);
        $colonPos = strpos($_header, ':');
        if ($colonPos > 0) {
            $key = substr($_header, 0, $colonPos);
            $val = preg_replace('/^\W+/', '', substr($_header, $colonPos));
            $this->responses[$this->getKey($ch)]['headers'][$key] = $val;
        }

        return strlen($header);
    }

    private function storeResponses()
    {
        while ($done = curl_multi_info_read($this->mc)) {
            $key = (string) $done['handle'];
            $this->responses[$key]['data'] = curl_multi_getcontent($done['handle']);
            foreach ($this->properties as $name => $const) {
                $this->responses[$key][$name] = curl_getinfo($done['handle'], $const);
            }
            curl_multi_remove_handle($this->mc, $done['handle']);
            curl_close($done['handle']);
        }
    }
}

class EpiCurlManager
{
    private $key;
    private $epiCurl;

    public function __construct($key)
    {
        $this->key = $key;
        $this->epiCurl = EpiCurl::getInstance();
    }

    public function __get($name)
    {
        $responses = $this->epiCurl->getResult($this->key);

        return isset($responses[$name]) ? $responses[$name] : null;
    }

    public function __isset($name)
    {
        $val = self::__get($name);

        return empty($val);
    }
}

/*
 * Credits:
 *  - (1) Alistair pointed out that curl_multi_add_handle can return CURLM_CALL_MULTI_PERFORM on success.
 */
