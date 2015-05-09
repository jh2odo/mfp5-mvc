<?php

final class ControladorFrontal
{

    private static $configuracion;
    private static $enrutador;

    private static $directoriosClases = array();

    private static $cacheHabilitado = FALSE;
    private static $cachePaginaHabilitado = FALSE;
    private static $cacheDuracion = 7200; // 2 horas(segundos)
    private static $cacheForzar = FALSE;

    private static $log = FALSE;
    private static $logNivel = 1; // Por defecto
    private static $logPath = 'logs/'; // Por defecto , pero se redefine en la inicializacion
    private static $logEmail = FALSE; // Por defecto
    private static $logEmailDe = FALSE; // Por defecto
    private static $logEmailPara = FALSE; // Por defecto

    private static $controladorError = FALSE;

    private static $debug = FALSE;

    private static $inicializado = FALSE;

    public static function inicializar($app = 'demo')
    {

        error_reporting(E_ALL | E_STRICT); // E_ALL | E_STRICT
        if (version_compare(phpversion(), '5.2.0', '<=') == TRUE) {
            die ('PHP5.2.0 Solo');
        }

        ini_set('display_errors', 'off'); // On solo en desarrollo y en produccion off
        ini_set('display_startup_errors', 'off'); // On solo en desarrollo y en produccion off
        ini_set('magic_quotes_gpc', 'off'); // Deshabilitadas totalmente

        // Constantes:
        define ('DIRSEP', DIRECTORY_SEPARATOR);

        // Path del sitio
        define ('BASE_PATH', substr(dirname(__FILE__), 0, strlen(dirname(__FILE__)) - 11));
        define ('BASE_PATH_APP', BASE_PATH . $app . DIRSEP);


        // Estructura de directorios de las clases del core
        self::$directoriosClases = array(BASE_PATH . 'core' . DIRSEP . 'clases' . DIRSEP,
            BASE_PATH . 'core' . DIRSEP . 'clases' . DIRSEP . 'Excepciones' . DIRSEP,
            BASE_PATH . 'core' . DIRSEP . 'clases' . DIRSEP . 'BdDrivers' . DIRSEP);

        // Inclusiones basicas para ahorrar autload
        include_once BASE_PATH . 'core' . DIRSEP . 'clases' . DIRSEP . 'Cargador.php';
        include_once BASE_PATH . 'core' . DIRSEP . 'clases' . DIRSEP . 'Configuracion.php';
        include_once BASE_PATH . 'core' . DIRSEP . 'clases' . DIRSEP . 'Enrutador.php';

        self::cargarConfiguracion();

        // Por defecto estan a off(recomenadado altamente)
        if (self::$configuracion->DISPLAY_ERRORS === 'on') {
            ini_set('display_errors', 'on');
            ini_set('display_startup_errors', 'on');
        }

        // Localizacion  y Uso horario
        if (date_default_timezone_set(self::$configuracion->USO_HORARIO) == FALSE) {
            date_default_timezone_set("Europe/Madrid"); // Uso horario español por defecto
        }

        // Para todas las funciones que dependan de la localizacion
        if (!is_array(self::$configuracion->USO_HORARIO) || setlocale(LC_ALL, self::$configuracion->USO_HORARIO) == FALSE) {
            setlocale(LC_ALL, 'es_ES', 'es', 'spa', 'esp', 'spanish'); // Idioma españa por defecto
        }

        // Comprobacion  e Inclusion del array de  estructura de directorios de las clases definidas por el usuario
        if (is_array(self::$configuracion->DIR_CLASES)) {
            self::$directoriosClases = array_merge(self::$directoriosClases, self::$configuracion->DIR_CLASES);
        }

        // Set autoload
        spl_autoload_register(array('ControladorFrontal', 'autoCargaClases'));

        // Registrar una función para su ejecución al finalizar
        register_shutdown_function(array('ControladorFrontal', "shutdown"));

        // Cargamos el enrutador
        self::$enrutador = Cargador::cargar("Enrutador");

        // LOG
        if (self::$configuracion->LOG === TRUE) {
            self::cargarLog();
        }

        if (self::$configuracion->CONTROLADOR_ERROR === TRUE) {
            self::$controladorError = TRUE;
        }

        // Gestion de errores y excepciones (Log)
        set_error_handler(array('ControladorFrontal', 'gestorErroresExcepciones'), E_ALL | E_STRICT);
        set_exception_handler(array('ControladorFrontal', 'gestorErroresExcepciones'));

        // CACHE
        if (self::$configuracion->CACHE === TRUE) {
            self::cargarCache();
        }

        // DEBUG
        if (self::$configuracion->DEBUG === TRUE) {
            self::cargarDebug();
        }

        // Sesiones (sitema nativo)
        if (self::$configuracion->SESION === TRUE) {

            include_once BASE_PATH . 'core' . DIRSEP . 'clases' . DIRSEP . 'Sesion.php';

            if (self::$configuracion->SESION_AUTO_INICIO === TRUE) {
                Cargador::cargar('Sesion')->start();
            }

        }

        // Sesiones
        //if (self::$configuracion->sesiones){
        //	$session = Cargador::cargar("Session");
        //	$session->setSavePath(self::$configuracion->base_path.'tmp/');
        //	if (self::$configuracion->sesiones_auto_start){
        //		$session->start();
        //	}
        //}


        // Permite la ejecucion de otros metodos de la clase, que necesitan de esta para su buen funcionamiento
        self::$inicializado = TRUE;
    }

    public static function ejecutar()
    {

        if (!self::$inicializado) {
            return FALSE;
        }

        if (self::$debug) {
            Debug::inicio('tiempo_global');
        }

        //self::$configuracion->set('tiempo_ejecucion',microtime(true));

        try {

            $controlador = self::$enrutador->getControlador();

            // Para los errores que el sistema manda directamente sin pasar por el controlador frontal y los controla el sistema por defecto.
            if (self::$controladorError == FALSE && $controlador == "error") {
                // si no se han enviado los header, se ejecuta el error y este finaliza la ejecucion tras el. (no continua)
                if (!headers_sent()) {
                    self::error(self::$enrutador->getAccion());
                }
                // si se ha producido el envio de header avisamos que se ha parado por un error anterior al error lanzado por el sistema.
                trigger_error("Error al enviar el error: " . self::$enrutador->getAccion() . '. La cabeceras ya se enviaron al navegador.', E_USER_ERROR);
                // Paramos la ejecucion
                exit;
            }
            // en caso controraio el controlador de error definido por el usuario se ejecuta como un controlador normal

            if (!class_exists($controlador, FALSE)) {
                $fichero_controlador = BASE_PATH_APP . 'app' . DIRSEP . 'controladores' . DIRSEP . "{$controlador}.php";
                if (!is_readable($fichero_controlador)) {
                    self::error404("Fichero del controlador '{$controlador}' no encontrado en {$fichero_controlador}");
                }
                require_once($fichero_controlador);
            }

            if (!class_exists($controlador, FALSE)) {
                self::error404("Fichero del controlador '{$controlador}' cargado correctamente pero la clase '{$controlador}' no esta definida en su interior");
            }

            $clase = new ReflectionClass($controlador);

            if (!$clase->isSubclassOf(new ReflectionClass('Controlador'))) {
                self::error404("El controlador {$controlador} no hereda de la clase 'Controlador'");
            }

            $accion = self::$enrutador->getAccion();

            if (!$clase->hasMethod($accion)) {
                self::error404("El controlador {$controlador} no tiene declarada la accion {$accion} (metodo de la clase)");
            }

            if (!$clase->getMethod($accion)->isPublic()) {
                self::error404("El controlador {$controlador} tiene declarada la accion {$accion} pero no es publica");
            }

            // Si existe cache pagina, la mostramos y terminamos la ejecucion
            $url_cache = self::$enrutador->getControlador() . '_' . self::$enrutador->getAccion() . '_' . implode("_", self::$enrutador->getParametros());

            if (self::$cachePaginaHabilitado && $cache = Cache::get($url_cache, $url_cache, self::$cacheForzar, FALSE)) {
                //echo "Cache";
                echo $cache;
                exit;
            }

            //ob_start(array('ControladorFrontal',"error")); //Experimental - captura de errores fatales

            if (self::$debug)
                Debug::inicio('controlador_ejecucion');
            // Instanciamos el controlador
            $controlador = $clase->newInstance();

            $controlador->setParametros(self::$enrutador->getParametros());
            $controlador->setPost(self::$enrutador->getPost());

            $accion = new ReflectionMethod($controlador, $accion);

            if (self::$cachePaginaHabilitado) {
                Cache::inicio($url_cache, $url_cache);
                $accion->invoke($controlador);
                Cache::fin(self::$cacheDuracion);
            } else {
                $accion->invoke($controlador);
            }

            if (self::$debug)
                Debug::fin('controlador_ejecucion');

            //ob_end_flush();

        } catch (NoEncontrado404Excepcion $excepcion) {
            trigger_error($excepcion, E_USER_NOTICE);
            if (!headers_sent()) {
                self::error("e404");
            }
        } catch (Exception $excepcion) {
            trigger_error("Excepcion " . get_class($excepcion) . "\nMensaje: " . $excepcion->getMessage() . "\nFichero: " .
                $excepcion->getFile() . "\nLinea: " . $excepcion->getLine(), E_USER_ERROR);
            if (!headers_sent()) {
                self::error(self::$enrutador->getAccion());
            }
        }


        if (self::$debug) {
            Debug::fin('tiempo_global');
        }
    }

    private static function error404($mensaje)
    {
        throw new NoEncontrado404Excepcion($mensaje);
    }

    public static function error($accion = 'index')
    {

        $controlador = "error";
        if (!self::$controladorError || !is_readable(BASE_PATH_APP . 'app' . DIRSEP . 'controladores' . DIRSEP . "error.php")) {
            require_once(BASE_PATH . "core/Errores.php");
            $controlador = "Errores";
        } else {
            require_once(BASE_PATH_APP . 'app' . DIRSEP . 'controladores' . DIRSEP . "error.php");
        }

        if ($controlador == "error" && !class_exists($controlador, FALSE)) {
            require_once(BASE_PATH . "core/Errores.php");
            $controlador = "Errores";
        }

        $clase = new ReflectionClass($controlador);

        if ($controlador == "error" && (!$clase->hasMethod($accion) || !$clase->getMethod($accion)->isPublic())) {
            require_once(BASE_PATH . "core/Errores.php");
            $controlador = "Errores";
        }

        // Para el caso que no este definido en el core el error
        if ($controlador != "error" && (!$clase->hasMethod($accion) || !$clase->getMethod($accion)->isPublic())) {
            trigger_error("Gestor de errores de paginas: " . $accion . ' No esta definido en el core.', E_USER_NOTICE);
            $accion = "index";
        }

        // Instanciamos el controlador y ejecutamos la accion
        $controlador = $clase->newInstance();
        $accion = new ReflectionMethod($controlador, $accion);
        $accion->invoke($controlador);
        exit; // Paramos la ejecucion una vez mostrado el error,.
    }

    private static function cargarConfiguracion()
    {

        require BASE_PATH . 'core' . DIRSEP . 'configuracion.php';

        if (is_readable(BASE_PATH_APP . 'app' . DIRSEP . 'configuracion' . DIRSEP . 'configuracion.php')) {
            require BASE_PATH_APP . 'app' . DIRSEP . 'configuracion' . DIRSEP . 'configuracion.php';
        } else {
            echo 'falso';
        }

        self::$configuracion = Cargador::cargar("Configuracion");

        return TRUE;
    }

    private static function autoCargaClases($clase)
    {

        if (class_exists($clase, FALSE)) {
            return TRUE;
        }

        foreach (self::$directoriosClases as $directorio) {
            if (is_readable($directorio . $clase . '.php')) {
                require_once($directorio . $clase . '.php');
                return TRUE;
            }
        }

        return FALSE;
    }

    public static function shutdown()
    {
        if (self::$debug) {
            Debug::volcar();
        }
    }

    // La idea de juntar excepcion y errores es inspirado de kohana Framework (Las excepciones que pasan por aqui, son las no capturadas)

    public static function gestorErroresExcepciones($excepcion, $mensaje, $fichero, $linea)
    {

        if (!self::$inicializado) {
            return FALSE;
        }

        // Si no está activado no generamos nigun log
        if (self::$log != TRUE) {
            return FALSE;
        }

        // marca de fecha/hora para el registro de error
        $fecha = date("Y-m-d H:i:s");

        // definir una matriz asociativa de cadenas de error
        // en realidad las �nicas entradas que deber�amos
        // considerar son E_WARNING, E_NOTICE, E_USER_ERROR,
        // E_USER_WARNING y E_USER_NOTICE

        $niveles_error = array('ERROR' => 1,
            'ALERT' => 2,
            'INFO' => 3,
            'CORE' => 4);

        $tipos_error = array(
            E_ERROR => array('ERROR', 'Error'),
            E_WARNING => array('ALERT', 'Advertencia'),
            E_PARSE => array('ERROR', 'Error de Intérprete'),
            E_NOTICE => array('INFO', 'Anotación'),
            E_CORE_ERROR => array('CORE', 'Error de Núcleo'),
            E_CORE_WARNING => array('CORE', 'Advertencia de Núcleo'),
            E_COMPILE_ERROR => array('CORE', 'Error de Compilación'),
            E_COMPILE_WARNING => array('CORE', 'Advertencia de Compilación'),
            E_USER_ERROR => array('ERROR', 'Error de Usuario'),
            E_USER_WARNING => array('ALERT', 'Advertencia de Usuario'),
            E_USER_NOTICE => array('INFO', 'Anotación de Usuario'),
            E_STRICT => array('INFO', 'Anotación de tiempo de ejecución'), // PHP 5
            E_RECOVERABLE_ERROR => array('ERROR', 'Error Fatal Atrapable'), // PHP 5.2
            0 => array('ERROR', 'Excepcion') // Excepciones con el codigo 0 por defecto
        );

        // Es un error PHP
        $error = (func_num_args() === 5);

        // Si es un error, la variable $exception es el codigo, y habria una quinta variable
        if ($error) {
            $codigo = $excepcion;
            $tipo = 'Error PHP: ' . $tipos_error[$codigo][1];
        } else {
            $codigo = $excepcion->getCode();
            $tipo = 'Excepcion: ' . get_class($excepcion);
            $mensaje = $excepcion->getMessage();
            $fichero = $excepcion->getFile();
            $linea = $excepcion->getLine();
        }

        // Removiendo el BASE_PATH de los mensajes por precaucion(kohana)
        $fichero = str_replace('\\', '/', realpath($fichero));
        $fichero = preg_replace('|^' . preg_quote(BASE_PATH) . '|', '', $fichero);

        // conjunto de errores de los cuales se almacenará un registro
        //$errores_de_usuario = array(E_USER_ERROR, E_USER_WARNING, E_USER_NOTICE);

        $xml = "<error>\n";
        $xml .= "\t<fecha>" . $fecha . "</fecha>\n";
        $xml .= "\t<codigo>" . $codigo . "</codigo>\n";
        $xml .= "\t<tipo>" . $tipo . "</tipo>\n";
        $xml .= "\t<mensaje><![CDATA[" . $mensaje . "]]></mensaje>\n";
        $xml .= "\t<fichero>" . $fichero . "</fichero>\n";
        $xml .= "\t<linea>" . $linea . "</linea>\n";
        $xml .= "</error>\n";

        // Se registran los errores segun el nivel de error definido al inicializar :: '
        //$mensaje = $tipo.' con mensaje "'.$mensaje.'" en el fichero "'.$fichero.'" y linea '.$linea;
        //Log::registrar($tipos_error[$codigo][0], $mensaje);
        //error_log($fecha.' :: '.(($tipos_error[$codigo][0] == 'INFO')?($tipo.' '):$tipos_error[$codigo][0]).' :: '.$mensaje, 3, self::$logPath."log_error".date('Y-m-d').".xml");

        $ficheroPath = self::$logPath . "error-log_" . date('Y-m-d') . ".xml";

        // Registro por defecto
        if (self::$logNivel >= $niveles_error[$tipos_error[$codigo][0]]) { // 4 es CORE

            // Si no existe lo creamos y le ponemos la cabecera
            if (!is_file($ficheroPath)) {
                file_put_contents($ficheroPath, '<?xml version="1.0" encoding="UTF-8" ?>' . "\n" . '<errores></errores>', LOCK_EX);
                chmod($ficheroPath, 0644);
            }

            // Registramos el error
            //error_log($xml, 3, $ficheroPath);

            $datos = str_replace("</errores>", "", file_get_contents($ficheroPath));
            file_put_contents($ficheroPath, $datos . $xml . "</errores>", LOCK_EX);

            // Solo se manda un email si el error es del tipo E_USER_ERROR y esta habilitado
            if (($codigo == E_USER_ERROR) && self::$logEmail === TRUE) {
                $para = self::$logEmailPara;
                $asunto = self::$configuracion->DOMINIO . ' - Error en la aplicacion';
                $mensaje = $error;
                $cabeceras = 'From: ' . self::$logEmailDe . "\r\n" .
                    'Reply-To: ' . self::$logEmailDe . "\r\n" .
                    'X-Mailer: PHP/' . phpversion();

                $mensaje = str_replace("\n.", "\n..", $mensaje); // solo windows

                mail($para, $asunto, $mensaje, $cabeceras);
            }

        }

        // En el caso de ser una excepcion, ella decide como actuar si lo tiene definido y no se han enviado los header
        if (!$error) {
            if (method_exists($excepcion, 'ejecutarSolucion') && !headers_sent()) {
                $excepcion->ejecutarSolucion();
            }
        }

        return TRUE;
    }

    /*
        Devuelve False si no se pudo ejecutar
        Nivel TRUE :: E_ALL | E_STRICT
        Nivel FALSE :: 0 (No se registra nigun error)
        Nivel E_ALL :: E_ALL (Sin el E_STRICT)
    */
    public static function setGestorErroresExcepciones($nivel = TRUE)
    {

        if (!self::$inicializado) {
            return FALSE;
        }

        if ($nivel == TRUE) {
            error_reporting(E_ALL | E_STRICT);
            set_error_handler(array('ControladorFrontal', 'gestorErroresExcepciones'));
            set_exception_handler(array('ControladorFrontal', 'gestorErroresExcepciones'));
        } else if ($nivel === 'E_ALL') {
            error_reporting(E_ALL);
            set_error_handler(array('ControladorFrontal', 'gestorErroresExcepciones'));
            set_exception_handler(array('ControladorFrontal', 'gestorErroresExcepciones'));
        } else {
            error_reporting(0);
            restore_error_handler();
            restore_exception_handler();
        }
    }

    private static function cargarCache()
    {

        // incluimos la clase Cache
        include_once BASE_PATH . 'core' . DIRSEP . 'clases' . DIRSEP . 'Cache.php';

        self::$cacheHabilitado = TRUE;
        Cache::setHabilitado(TRUE);

        if (self::$configuracion->CACHE_PATH != FALSE) {
            if (is_readable(self::$configuracion->CACHE_PATH)) {
                Cache::setPath(self::$configuracion->CACHE_PATH);
            } else {
                Cache::setPath(BASE_PATH_APP . 'app' . DIRSEP . 'cache' . DIRSEP);
            }
        } else {
            Cache::setPath(BASE_PATH_APP . 'app' . DIRSEP . 'cache' . DIRSEP);
        }

        if (self::$configuracion->CACHE_DURACION != FALSE) {
            if (is_numeric(self::$configuracion->CACHE_DURACION) && self::$configuracion->CACHE_DURACION > 0) {
                self::$cacheDuracion = self::$configuracion->CACHE_DURACION;
                Cache::setDuracion(self::$cacheDuracion);
            }
        }

        if (self::$cacheHabilitado && self::$configuracion->CACHE_PAGINA === TRUE) {
            self::$cachePaginaHabilitado = TRUE;
        }

        if (self::$cacheHabilitado && self::$configuracion->CACHE_FORZAR === TRUE) {
            self::$cacheForzar = TRUE;
        }

        if (self::$cacheHabilitado && self::$configuracion->CACHE_COMPRIMIR === TRUE) {
            Cache::setComprimir(TRUE);
        }

        // Definicion de páginas no cacheables (afecata a cachePagina)
        if (is_readable(BASE_PATH_APP . 'app' . DIRSEP . 'configuracion' . DIRSEP . 'cache.php')) {
            require BASE_PATH_APP . 'app' . DIRSEP . 'configuracion' . DIRSEP . 'cache.php';
        }

        if (isset($nocaches)) {
            foreach ($nocaches as $nocache) {
                if (preg_match($nocache, self::$enrutador->getRuta())) {
                    //echo "Página NO Cacheable";
                    self::$cachePaginaHabilitado = FALSE;
                    break;
                }
            }
        }

        return TRUE;
    }

    private static function cargarDebug()
    {
        // incluimos la clase Debug
        include_once BASE_PATH . 'core' . DIRSEP . 'clases' . DIRSEP . 'Debug.php';

        //self::$configuracion->DEBUG_PATH
        self::$debug = TRUE;
        Debug::setHabilitado(TRUE);

        if (self::$configuracion->DEBUG_PATH != FALSE) {
            if (is_readable(self::$configuracion->DEBUG_PATH)) {
                Debug::setPath(self::$configuracion->DEBUG_PATH);
            } else {
                Debug::setPath(BASE_PATH . 'debug' . DIRSEP);
            }
        }

        if (self::$configuracion->DEBUG_MODO != FALSE) {
            if (is_string(self::$configuracion->DEBUG_MODO)) {
                if (self::$configuracion->DEBUG_MODO === 'a') {
                    Debug::setModo('a'); // Modo Pantalla
                } else if (self::$configuracion->DEBUG_MODO === 'b') {
                    Debug::setModo('b'); // Modo Fichero
                } else {
                    Debug::setModo('a'); // Modo Pantalla
                }
            } else {
                Debug::setModo('a'); // Modo Pantalla
            }
        }
    }

    private static function cargarLog()
    {
        self::$log = TRUE;
        // Redefinimos el path del log
        self::$logPath = BASE_PATH_APP . 'logs' . DIRSEP; // Por defecto

        if (self::$configuracion->LOG_NIVEL != FALSE) {
            if (is_numeric(self::$configuracion->LOG_NIVEL) && (self::$configuracion->LOG_NIVEL >= 1) && (self::$configuracion->LOG_NIVEL <= 4)) {
                self::$logNivel = self::$configuracion->LOG_NIVEL;
            }
        }

        if (self::$configuracion->LOG_PATH != false) {
            if (is_readable(self::$configuracion->LOG_PATH)) {
                self::$logPath = self::$configuracion->LOG_PATH;
            }
        }

        if (self::$log && self::$configuracion->LOG_EMAIL === TRUE) {
            self::$logEmail = TRUE;

            if (self::$configuracion->LOG_EMAIL_DE != FALSE && self::$configuracion->LOG_EMAIL_PARA != FALSE) {
                // Habria que validar los email

                // Deshabilitamos el envio por email
            } else {
                self::$logEmail = FALSE;
            }

        }

    }


}

?>