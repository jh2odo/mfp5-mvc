<?php

class Errores extends Controlador
{

    function index()
    {
        header("Content-type: text/html; charset=UTF-8", TRUE);
        $this->mostrar('Error General', 'Generado por la aplicacion.');
    }


    /*
       400 Solicitud incorrecta
       La solicitud contiene sintaxis errónea y no debería repetirse.
     */
    //function e400(){
    //	$this->index();
    //}

    /*
    401 No autorizado
    Similar al 403 Forbidden.
     */
    //function e401(){
    //	$this->index();
    //}

    /*
    403 Prohibido
    La solicitud fue legal, pero el servidor se rehusa a responderla.
     */
    function e403()
    {
        trigger_error('Error 403 - URL: ' . $_SERVER['REQUEST_URI'], E_USER_WARNING);
        header('HTTP/1.1 403 Forbidden', TRUE, 403);
        header("Content-type: text/html; charset=UTF-8");
        $this->mostrar('403 Acceso prohibido', 'Generado por la aplicacion.');
    }

    /*
    404 No encontrado
    Recurso no encontrado.
     */
    function e404()
    {
        trigger_error('Error 404 - URL: ' . $_SERVER['REQUEST_URI'], E_USER_NOTICE); // No seria necesario(par por el controlador frontal y lo gestiona una exccepcion el registro)
        header("HTTP/1.0 404 Not Found", TRUE, 404);
        header("Content-type: text/html; charset=UTF-8");
        $this->mostrar('404 No encontrada', 'La URL solicitada <i>' . htmlspecialchars($_SERVER['REQUEST_URI'], ENT_QUOTES, "UTF-8") . '</i> no se ha encontrado en la aplicacion.');
    }

    /*
    405 Método no permitido
    Una petición fue hecha a una URI utilizando un método de solicitud
     */
    //function e405(){
    //	$this->index();
    //}

    /*
    406 No aceptable
     */
    //function e406(){
    //	$this->index();
    //}

    /*
    408 Tiempo de espera agotado
     */
    //function e408(){
    //	$this->index();
    //}

    /*
    409 Conflicto
     */
    //function e409(){
    //	$this->index();
    //}

    /*
    410 Ya no disponible
    Indica que el recurso solicitado ya no está disponible y no lo estará de nuevo.
    Similar al 404.
     */
    //function e410(){
    //	$this->index();
    //}

    /*
    500 Error interno
     */
    function e500()
    {
        trigger_error('Error 500 - URL: ' . $_SERVER['REQUEST_URI'], E_USER_ERROR);
        header('HTTP/1.1 500 Internal Server Error', TRUE, 500);
        header("Content-type: text/html; charset=UTF-8");
        $this->mostrar('500 Error Interno', 'Generado por la aplicacion.');
    }

    /*
    501 No implementado
     */
    //function e501(){
    //	$this->index();
    //}

    /*
    505 Versión de HTTP no soportada
     */
    //function e505(){
    //	$this->index();
    //}

    private function mostrar($titulo, $descripcion)
    {

        echo '<!DOCTYPE HTML PUBLIC "-//IETF//DTD HTML 2.0//EN">' . "\n" . '<html><head><title>' . $titulo . '</title></head><body><h1>' . $titulo . '</h1><p>' . $descripcion . '</p><p><address>' . Cargador::cargar('Configuracion')->HOST . '</address></p></body></html>';

    }

    public function __toString()
    {
        return get_class($this);
    }

}

?>