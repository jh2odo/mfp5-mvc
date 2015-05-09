<?php

$config = Cargador::cargar('Configuracion'); // Intocable - NO TOCAR - NO LO MIRES MAS

// HOST, BASE, DOMINIO, LENGUAJE, CODIFICACION...
$config->set('HOST', 'localhost'); // Obligatorio
$config->set('BASE_URL', "http://localhost/"); // Obligatorio
$config->set('DOMINIO', 'localhost'); // Obligatorio

$config->set('IDIOMA', 'es'); //Deberia de ser Obligatorio (Para el html) //internalizacion
$config->set('CHARSET_ENCODING', "utf-8"); //Deberia de ser Obligatorio (iso-8859-1)

########################

// MOSTRAR ERRORES (DESARROLLO:on Y PRODUCCION:off)
$config->set('DISPLAY_ERRORS', 'on');

// BASE DE DATOS (opcional)
$config->set('BD_DRIVER', 'mysql');
$config->set('BD_HOST', 'localhost');
//$config->set('BD_BASEDATOS', 'prensaseria');
$config->set('BD_BASEDATOS', 'test');
$config->set('BD_USUARIO', 'root');
$config->set('BD_PASSWORD', '1234');
$config->set('BD_PERSISTENTE', FALSE);

// TABLAS (sin implementar)
//$config->set('BD_TABLAS',TRUE);
//$config->set('BD_TABLAS_FICHERO','tablas.php');

// LOCALIZACION Y USO HORARIO (opcional) // Por defecto esta localizado españa y horario español
$config->set('LOCALIZACION', array('es_ES', 'es', 'spa', 'esp', 'spanish')); // Obligatorio (setlocale) (array)
$config->set('USO_HORARIO', 'Europe/Madrid'); // Obligatorio

// CONFIGURACION DE DIRECTORIOS DE CLASE (opcional)
$config->set('DIR_CLASES', array(BASE_PATH_APP . 'app' . DIRSEP . 'clases' . DIRSEP)); // por defecto

// MENSAJES ERROR PERSONALIZADOS (Tipos de error: 404,403,500...) (opcional)
// si se desea personalizar los errores, hay que definir un controlador de nombre 'error'.
// En caso contrario, la aplicacion y el sistema mostrara los errores por defecto.
$config->set('CONTROLADOR_ERROR', FALSE); // FALSE ó TRUE

// ENRUTADOR (opcional)
$config->set('CARACTERES_URL_VALIDOS', "/[^a-z0-9\/\.\-\_]/");//Por defecto (obligatorio)
$config->set('CONTROLADOR_PREDETERMINADO', 'inicio'); //Por defecto (obligatorio)
$config->set('ACCION_PREDETERMINADO', 'index'); //Por defecto (obligatorio)
$config->set('SUFIJOS_URL', FALSE); // Mal Funcionamiento (FALSE ó array('html','xml')) //Permitidos

// CACHE (opcional)
$config->set('CACHE', FALSE); // Cache general (obligatorio)
$config->set('CACHE_PATH', BASE_PATH_APP . 'app' . DIRSEP . 'cache' . DIRSEP); // Cache general (obligatorio)
$config->set('CACHE_PAGINA', FALSE); // Cache de la página completa
$config->set('CACHE_FORZAR', FALSE); // Fuerza a usar cache almacenada sin tener encuenta la duraccion
$config->set('CACHE_DURACION', 2 * 60 * 60); // Dos hora 2*60*60
$config->set('CACHE_COMPRIMIR', FALSE); // Comprime los archivos caches

// GESTION DE ERRORES (opcional)
// Habilitar registro de errores internos(no criticos) y personalizados(Excepciones,trigger_error)
// Si no se habilita, no produce logs de errores.
// En niguno de los dos casos(habiltado o no), produce mensajes por pantallas(no criticos)
// En el caso de un error critico, genera un documento en blanco.
$config->set('LOG', TRUE);
$config->set('LOG_PATH', BASE_PATH_APP . "logs" . DIRSEP);
$config->set('LOG_NIVEL', 4); //  'ERROR' => 1,'ALERT' => 2,'INFO'  => 3, 'CORE' => 4
$config->set("LOG_EMAIL", FALSE); // Enviar email en los casos E_USER_ERROR
$config->set("LOG_EMAIL_DE", "admin@localhost"); // Obligatorio si se log_email esta a true
$config->set("LOG_EMAIL_PARA", "admin@localhost");// Obligatorio si se log_email esta a true

// SESIONES (opcional) (sistema nativo php)
$config->set('SESION', FALSE); //(obligatorio)
$config->set('SESION_AUTO_INICIO', FALSE); // Iniciarlo cuando se inicia el controlador frontal. 

// Debug (opcional) 
$config->set('DEBUG', FALSE); //(Obligatorio)
$config->set('DEBUG_MODO', 'a'); // Genera html en un fichero (b) o vuelca en pantalla el html (a)
$config->set('DEBUG_PATH', BASE_PATH . "debug" . DIRSEP);

// EMAIL
$config->set("EMAIL_DE", "admin@localhost"); // Obligatorio
$config->set("EMAIL_PARA", "admin@localhost");// Obligatorio

###########

// DIRECTORIOS (sin implementar)
$config->set('DIR_CONTROLADORES', 'controladores' . DIRSEP);
$config->set('DIR_MODELOS', 'modelos' . DIRSEP);
$config->set('DIR_VISTAS', 'vistas' . DIRSEP);
$config->set('DIR_IMAGENES', 'imagenes' . DIRSEP); // INTOCABLE - NO TOCAR (htaccess)
$config->set('DIR_JS', 'js' . DIRSEP); // INTOCABLE - NO TOCAR (htaccess)
$config->set('DIR_CSS', 'css' . DIRSEP); // INTOCABLE - NO TOCAR (htaccess)


?>