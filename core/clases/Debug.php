<?php

/*
 * Basado en http://kohanaphp.com/ - Benchmark
 */

final class Debug
{

    private static $habilitado = FALSE;
    private static $path = "debug/";
    private static $modo = 'a'; // a - Pantalla, b - Fichero
    private static $marcas = array();
    private static $sqls = array();

    public static function setHabilitado($habilitado = FALSE)
    {
        self::$habilitado = $habilitado;
    }

    public static function getHabilitado()
    {
        return self::$habilitado;
    }

    public static function setPath($path = FALSE)
    {
        self::$path = $path;
    }

    public static function setModo($modo = 'a')
    {
        self::$modo = $modo;
    }

    public static function inicio($nombre)
    {

        if (self::$habilitado !== TRUE) {
            return FALSE;
        }

        if (!isset(self::$marcas[$nombre])) {
            self::$marcas[$nombre] = array(
                'tiempo_inicio' => microtime(TRUE),
                'tiempo_fin' => FALSE,
                'memoria_inicio' => memory_get_usage(),
                'memoria_fin' => FALSE
            );
        }
    }

    public static function fin($nombre)
    {

        if (self::$habilitado !== TRUE) {
            return FALSE;
        }

        if (isset(self::$marcas[$nombre]) && self::$marcas[$nombre]['tiempo_fin'] === FALSE) {
            self::$marcas[$nombre]['tiempo_fin'] = microtime(TRUE);
            self::$marcas[$nombre]['memoria_fin'] = memory_get_usage();
        }
    }


    // Medida tiempo: 1(segundos), 1000(milisegundos) :: Medida Memoria: 1(bytes), 1024(kbytes), 1048576(MBytes)
    public static function get($nombre, $decimales = 4, $medidas = array("tiempo" => 1, "memoria" => 1024))
    {

        if (self::$habilitado !== TRUE) {
            return FALSE;
        }

        // Para mostrar todos metemos en nombre un true
        if ($nombre === TRUE) {
            $marcas = array();
            $nombres = array_keys(self::$marcas);

            foreach ($nombres as $nombre) {
                // Conseguimos cada marcar recursivamente
                $marcas[$nombre] = self::get($nombre, $decimales);
            }

            // Return the array
            return $marcas;
        }

        if (!isset(self::$marcas[$nombre]))
            return FALSE;

        if (self::$marcas[$nombre]['tiempo_fin'] === FALSE) {
            // Para prevenir  resultados errones, finalizamos dicha marca
            self::fin($nombre);
        }

        if (!is_array($medidas) || isset($medidas["tiempo"]) || isset($medidas["memoria"])) {
            $medidas = array("tiempo" => 1, "memoria" => 1024);
        }

        return array(
            'tiempo' => number_format((self::$marcas[$nombre]['tiempo_fin'] - self::$marcas[$nombre]['tiempo_inicio']) * $medidas["tiempo"], $decimales),
            'memoria_uso_inicio' => round(self::$marcas[$nombre]['memoria_inicio'] / $medidas["memoria"], $decimales),
            'memoria_uso_fin' => round(self::$marcas[$nombre]['memoria_fin'] / $medidas["memoria"], $decimales),
            'memoria_uso_diferencia' => round((self::$marcas[$nombre]['memoria_fin'] - self::$marcas[$nombre]['memoria_inicio']) / $medidas["memoria"], $decimales)
        );
    }

    public static function getTodos($decimales = 4)
    {

        if (self::$habilitado !== TRUE) {
            return FALSE;
        }

        return self::get(TRUE, $decimales);
    }

    public static function sql($sql)
    {

        if (self::$habilitado !== TRUE) {
            return FALSE;
        }

        self::$sqls[] = $sql;
    }

    private static function memoriaEnUso()
    {
        return memory_get_usage();
    }

    private static function memoriaReservada()
    {
        return memory_get_usage(TRUE);
    }

    public static function volcar()
    {

        if (self::$habilitado !== TRUE) {
            return FALSE;
        }

        if (self::$modo == 'b') {
            $nombre = strtolower(str_replace("/", "_", Cargador::cargar("Enrutador")->getRuta()));
            $datos = '<html><head><title>DEBUG ' . $nombre . '</title></head><body>' .
                self::generar(FALSE) . '</body></html>';

            file_put_contents(self::$path . "debug_" . $nombre . "_" . date('Y-m-d_H-i-s') . ".html", $datos);
        } else if (self::$modo == 'a') {
            // Para anular cualquier otro tipo de header Content-type
            //header("Content-type: text/html",TRUE);
            self::generar(TRUE);
        }
    }

    private static function generar($imprimir = FALSE)
    {

        if (self::$habilitado !== TRUE) {
            return FALSE;
        }

        ob_start();
        echo '<div id="debug_" style="text-align:left;margin:20px;"><h1>DEBUG</h1>';

        $deb = self::get(TRUE);
        echo '<h2 style="cursor:pointer;" onclick="if(document.getElementById(\'debug_tiempos\').style.display == \'none\'){document.getElementById(\'debug_tiempos\').style.display = \'block\';}else{document.getElementById(\'debug_tiempos\').style.display = \'none\';}">TIEMPOS y MEMORIA - ' . count($deb) . '</h2>';
        echo '<div id="debug_tiempos" style="display:block">';
        $tg = array_shift($deb); // Quitamos el tiempo global
        echo '<p>Tiempo: <strong>' . $tg["tiempo"] . '</strong> segundos<br />';
        echo 'Memoria Inicio: ' . $tg["memoria_uso_inicio"] . ' kbytes<br />';
        echo 'Memoria Final: <strong>' . $tg["memoria_uso_fin"] . '</strong> kbytes<br />';
        echo 'Memoria Diferencia: ' . $tg["memoria_uso_diferencia"] . ' kbytes</p>';
        if (!empty($deb)) {
            echo '<pre>' . print_r($deb, true) . '</pre>';
        } else {
            echo '<p>Nigun tiempo o memoria testeado</p>';
        }
        echo '</div>';

        $enrutador = Cargador::cargar("Enrutador");
        echo ';}">ENRUTADOR</h2>';
        echo '<div id="debug_enrutador" style="display:none">';
        if (!empty($enrutador)) {
            echo '<pre>' . print_r($enrutador, true) . '</pre>';
        } else {
            echo '<p>Nigun enrutador testeado</p>';
        }
        echo '</div>';

        $configuracion = Cargador::cargar("Configuracion");
        echo ';}">CONFIGURACION</h2>';
        echo '<div id="debug_config" style="display:none">';
        if (!empty($configuracion)) {
            echo '<pre>' . print_r($configuracion, true) . '</pre>';
        } else {
            echo '<p>Niguna configuracion establecida.</p>';
        }
        echo '</div>';

        /*
        echo '<h2>Clases - '.count(get_declared_classes()).'</h2>';
        $clases_declaradas = get_declared_classes();
        if(!empty($clases_declaradas)){
            echo '<pre>'.print_r(get_declared_classes(),true).'</pre>';
        }else{
            echo '<p>No se declaro niguna clase</p>';
        }*/

        $slqs = self::$sqls;
        echo ';}">SQL - ' . count($slqs) . '</h2>';
        echo '<div id="debug_sql" style="display:none">';
        if (!empty($slqs)) {
            echo '<pre>' . print_r($slqs, true) . '</pre>';
        } else {
            echo '<p>Sin sql</p>';
        }
        echo '</div>';

        $archivos_incluidos = get_included_files();
        echo ';}">INCLUDES y REQUIRE - ' . count($archivos_incluidos) . '</h2>';
        echo '<div id="debug_includes" style="display:none">';

        $core = array();
        $app = array();
        $otros = array();
        $desplazamiento = strlen(BASE_PATH);

        foreach ($archivos_incluidos as $archivo) {
            if (strpos($archivo, 'core' . DIRSEP, $desplazamiento) == $desplazamiento) {
                $core[] = $archivo;
            } else if (strpos($archivo, 'app' . DIRSEP, $desplazamiento) == $desplazamiento) {
                $app[] = $archivo;
            } else {
                $otros[] = $archivo;
            }
        }

        if (!empty($archivos_incluidos)) {
            echo '<p>Core - ' . count($core) . '</p>';
            echo '<pre>' . print_r($core, true) . '</pre>';
            echo '<p>Aplicacion - ' . count($app) . '</p>';
            echo '<pre>' . print_r($app, true) . '</pre>';
            echo '<p>Otros - ' . count($otros) . '</p>';
            echo '<pre>' . print_r($otros, true) . '</pre>';
        } else {
            echo '<p>Sin Achivos incluidos o requeridos</p>';
        }
        echo '</div>';


        $constantes_definidas = get_defined_constants(true);
        echo ';}">CONSTANTES</h2>';
        echo '<div id="debug_const" style="display:none">';
        if (!empty($constantes_definidas['user'])) {
            echo '<pre>' . print_r($constantes_definidas['user'], true) . '</pre>';
        } else {
            echo '<p>Sin Constantes</p>';
        }
        echo '</div>';

        echo ';}">COOKIES: $_COOKIE</h2>';
        echo '<div id="debug_cookies" style="display:none">';
        if (!empty($_COOKIE)) {
            echo '<pre>' . print_r($_COOKIE, true) . '</pre>';
        } else {
            echo '<p>Vacio $_COOKIE</p>';
        }
        echo '</div>';

        echo ';}">VARIABLES DE ENTORNO: $_ENV</h2>';
        echo '<div id="debug_env" style="display:none">';
        if (!empty($_ENV)) {
            echo '<pre>' . print_r($_ENV, true) . '</pre>';
        } else {
            echo '<p>Vacio $_ENV</p>';
        }
        echo '</div>';

        echo ';}">ARCHIVOS: $_FILES - ' . count($_FILES) . '</h2>';
        echo '<div id="debug_files" style="display:none">';
        if (!empty($_FILES)) {
            echo '<pre>' . print_r($_FILES, true) . '</pre>';
        } else {
            echo '<p>Vacio $_FILES</p>';
        }
        echo '</div>';

        echo ';}">$_GET</h2>';
        echo '<div id="debug_get" style="display:none">';
        if (!empty($_GET)) {
            echo '<pre>' . print_r($_GET, true) . '</pre>';
        } else {
            echo '<p>Vacio $_GET</p>';
        }
        echo '</div>';

        echo ';}">$_POST</h2>';
        echo '<div id="debug_post" style="display:none">';
        if (!empty($_POST)) {
            echo '<pre>' . print_r($_POST, true) . '</pre>';
        } else {
            echo '<p>Vacio $_POST</p>';
        }
        echo '</div>';

        echo ';}">$_REQUEST</h2>';
        echo '<div id="debug_request" style="display:none">';
        if (!empty($_REQUEST)) {
            echo '<pre>' . print_r($_REQUEST, true) . '</pre>';
        } else {
            echo '<p>Vacio $_REQUEST</p>';
        }
        echo '</div>';

        echo ';}">$_SERVER</h2>';
        echo '<div id="debug_server" style="display:none">';
        if (!empty($_SERVER)) {
            echo '<pre>' . print_r($_SERVER, true) . '</pre>';
        } else {
            echo '<p>Vacio $_SERVER</p>';
        }
        echo '</div>';

        if (isset($_SESSION)) {
            echo ';}">$_SESSION - ' . count($_SESSION) . '</h2>';
            echo '<div id="debug_session" style="display:none">';
            if (!empty($_SESSION)) {
                echo '<pre>' . print_r($_SESSION, true) . '</pre>';
            } else {
                echo '<p>Vacio $_SESSION</p>';
            }
        } else {
            echo ';}">$_SESSION - 0</h2>';
            echo '<div id="debug_session" style="display:none">';
            echo '<p>Sin sesion</p>';
        }
        echo '</div>';

        echo '</div>';
        $datos = ob_get_contents();
        ob_end_clean();

        if ($imprimir === TRUE) {
            echo $datos;
        } else {
            return $datos;
        }
    }

    public static function dump($expresion, $cadena = FALSE)
    {

        if (self::$habilitado !== TRUE) {
            return FALSE;
        }

        if (!$cadena) {
            var_dump($expresion);
        } else {
            ob_start();
            var_dump($expresion);
            $dump = ob_get_contents();
            ob_end_clean();
            return $dump;
        }
    }

    public static function backtrace($cadena = FALSE)
    {

        if (self::$habilitado !== TRUE) {
            return FALSE;
        }

        if (!$cadena) {
            debug_print_backtrace();
        } else {
            ob_start();
            debug_print_backtrace();
            $traza = ob_get_contents();
            ob_end_clean();
            return $traza;
        }
    }

}

?>