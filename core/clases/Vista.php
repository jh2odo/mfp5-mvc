<?php

class Vista
{

    private $vista;
    private $vars = array(); // Variables
    private $layout = NULL;
    private $cache = false;
    private $header = array();

    /*private $headers = array("Content-type" => array("html" => 'text/html;',
                                    "xml" => 'text/xml;',
                                    "xhtml" => 'application/xhtml+xml;'));
    */
    public function __construct($vista)
    {
        $this->vista = $vista;
    }

    public function set($nombre, $valor)
    {
        if (!isset($this->vars[$nombre]) && !empty($nombre) & !empty($valor)) {
            $this->vars[$nombre] = $valor;
        }
    }

    public function get($nombre)
    {
        if (isset($this->vars[$nombre])) {
            return $this->vars[$nombre];
        }
    }

    public function setVars($vars)
    {
        return $this->vars = $vars;
    }

    public function setHeader($header = array("Content-type" => 'text/html'))
    {
        foreach ($header as $clave => $valor) {
            $this->header[$clave] = $valor;
        }
    }

    public function setLayout($layout = 'default')
    {
        $this->layout = $layout;
    }

    public function setCache($cache = false)
    {
        $this->cache = $cache;
    }

    public function getLayout()
    {
        return $this->layout;
    }

    public function generar($cadena = FALSE)
    {

        $configuracion = Cargador::cargar('Configuracion');

        // Cabeceras
        //var_dump($_SERVER['HTTP_ACCEPT']);
        //exit;
        foreach ($this->header as $clave => $valor) {
            if (($clave === "Content-type") && (!isset($_SERVER['HTTP_ACCEPT']) || (strpos($_SERVER['HTTP_ACCEPT'], "application/xhtml+xml") === FALSE))) {
                $valor = str_replace("application/xhtml+xml", "text/html", $valor);
            }
            header($clave . ': ' . $valor, true);
        }

        ob_start();

        extract($this->vars, EXTR_OVERWRITE);

        if (file_exists(BASE_PATH_APP . "app/vistas/{$this->vista}.php")) {
            include(BASE_PATH_APP . "app/vistas/{$this->vista}.php");
        } else {
            throw new Exception("No ha sido encontrado en vistas/{$this->vista}/ directorio.");
        }

        if (!is_null($this->layout) && $this->layout !== FALSE) {
            $layoutdata = ob_get_clean(); // Datos de la plantilla

            $layoutfile = BASE_PATH_APP . "app/vistas/layout/{$this->layout}.php";

            ob_start();
            if (file_exists($layoutfile)) {
                include_once($layoutfile);
            } else {
                include_once(BASE_PATH_APP . "app/vistas/layout/default.php");
            }
        }

        $data = ob_get_clean();

        if ($cadena == TRUE) {
            return $data;
        } else {
            echo $data;
        }

    }

}

?>