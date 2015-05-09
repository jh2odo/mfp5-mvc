<?php

class Base
{
    public static function pr($array)
    {
        echo "<pre>";
        print_r($array);
        echo "</pre>";
    }

    public static function backtrace()
    {
        echo "<pre>";
        debug_print_backtrace();
        echo "</pre>";
    }

    public static function basePath()
    {
        $conf = Cargador::cargar("Configuracion");
        return $conf->base_path;
        //return getcwd();
    }

    public static function baseUrl()
    {
        $conf = Cargador::cargar("Configuracion");
        return $conf->BASE_URL;
    }

    function isIE()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($agent, "MSIE") !== false) return true;
        return false;
    }

    function isIE7()
    {
        $agent = $_SERVER['HTTP_USER_AGENT'];
        if (strpos($agent, "MSIE 7.0") !== false) return true;
        return false;
    }

    static function esMetodoPublico($clase, $metodo)
    {
        if ((method_exists($clase, $metodo) == TRUE) && in_array($metodo, get_class_methods($clase))) {
            return TRUE;
        } else {
            return FALSE;
        }
    }

    static function esXml($url)
    {
        if ((strpos($url, "http")) === false) $url = "http://" . $url;
        if (is_array(@get_headers($url))) {
            $a = get_headers($url, 1);
            //por si viene un array dentro de Content-Type
            if (is_array($a["Content-Type"])) {
                for ($f = 0; $f < count($a["Content-Type"]); $f++) {
                    if (strpos($a["Content-Type"][$f], 'xml') === false) {
                        if ($f == count($a["Content-Type"]) - 1) {
                            return false;
                        }
                    } else {
                        return true;
                    }
                }
            } else {
                if (strpos($a["Content-Type"], 'xml') === false) {
                    return false;
                } else {
                    return true;
                }
            }
        } else {
            return false;
        }
    }

    static function esRss($feedxml)
    {
        @$feed = simplexml_load_file($feedxml);
        return ($feed->channel->item) ? true : false;
    }

    static function esRssOrXml($feedxml)
    {
        @$feed = new simplexml_load_file($feedxml);
        if (isset($feed->channel->item)) {
            return true;
        } else {
            return self::esXml($feedxml);
        }
    }

    static function enlaceUrl($valor)
    {
        $carac = array("^", "]", "..", "á", "é", "í", "ó", "ú", "ü", '-', 'ñ', ' ');
        $carac_sano = array("", "", "", "a", "e", "i", "o", "u", "u", '_', 'n', '-');
        $valor = str_replace($carac, $carac_sano, $valor);
        $valor = strtolower($valor);
        return $valor;
    }

    //http://gmt-4.blogspot.com/2008/04/conversion-de-unicode-y-latin1-en-php-5.html
    //Función que converte un string a ISO-8859-1 (LATIN1)
    static function latin1($txt)
    {
        $encoding = mb_detect_encoding($txt, 'ASCII,UTF-8,ISO-8859-1');
        if ($encoding == "UTF-8") {
            $txt = utf8_decode($txt);
        }
        return $txt;
    }

    //Función que converte un string a UTF-8
    static function utf8($txt)
    {
        $encoding = mb_detect_encoding($txt, 'ASCII,UTF-8,ISO-8859-1');
        if ($encoding == "ISO-8859-1") {
            $txt = utf8_encode($txt);
        }
        return $txt;
    }
}

?>