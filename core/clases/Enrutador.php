<?php

/**
 * Enrutador maneja todos los request
 */
class Enrutador
{

    private $ruta;
    private $controlador = 'inicio';
    private $accion = 'index';
    private $parametros = array();
    private $sufijo = FALSE;

    public function __construct()
    {

        $ruta = strtolower($_SERVER['QUERY_STRING']);

        $configuracion = Cargador::cargar("Configuracion");

        // Con o sin Sufijo
        //print_r($_GET);
        if ($configuracion->SUFIJOS_URL !== FALSE) {
            $this->sufijo = $configuracion->SUFIJOS_URL;
        }

        // Sanamos la ruta mediante un patron y la guardamos
        //$caracteres_url_validos = "/[^A-z0-9\/\^]/";
        $caracteres_url_validos = $configuracion->CARACTERES_URL_VALIDOS; // Patron
        $ruta = preg_replace($caracteres_url_validos, "", $ruta);
        $carac = array("^", "]", "..", "á", "é", "í", "ó", "ú");
        $carac_sano = array("", "", "", "a", "e", "i", "o", "u");
        $ruta = str_replace($carac, $carac_sano, $ruta);
        //$ruta = str_replace("]","",$ruta);
        //$ruta = str_replace("..","",$ruta);

        //echo $ruta;

        //Si no se permiten sufijos elimino todos los puntos
        //if($this->sufijo === FALSE){
        //	$sufijos = array(".html",".htm",".php");
        //$ruta = str_replace(".", "", $ruta);
        //}

        // Para añadir una barra al final de la ruta
        $posicion = strrpos($ruta, "/", strlen($ruta) - 1);
        if ($posicion === false && !empty($ruta)) {
            $ruta = $ruta . '/';
        }
        $this->ruta = $ruta; // Guardamos la ruta

        // Troceamos la ruta
        $segmentos = explode("/", $ruta);
        unset($ruta); // Eliminaos ruta
        $segmentos = array_filter($segmentos); // Filtramos el array

        // En el caso de que no existan parametros (sin controlador)
        if (empty($segmentos)) {
            // Establecemos el controlador por defecto si esta en configuracion y si no el por defecto de
            // la clase enrutador
            if ($configuracion->controlador_predeterminado !== FALSE) {
                $this->controlador = $configuracion->CONTROLADOR_PREDETERMINADO;
            }
            // La accion
            if ($configuracion->accion_predeterminada !== FALSE) {
                $this->accion = $configuracion->ACCION_PREDETERMINADO;
            }

            //Para el resto de casos con existencia de parametros
        } else {
            // Minimo existe 1 -> El controlador
            $this->controlador = $segmentos[0];
            array_shift($segmentos); // Quitamos el controlador

            // Si no esta vacio, existe una accion
            if (!empty($segmentos)) {
                $this->accion = $segmentos[0];
                array_shift($segmentos); // Quitamos la accion
            }

            // Si sigue sin estar vacio, entonces son parametros lo restante
            if (!empty($segmentos)) {
                foreach ($segmentos as $parametro) {
                    if (!empty($parametro)) {
                        $this->parametros[] = $parametro;
                    }
                }
            }

            // Validamos el sufijo
            $this->validarSufijo();

        }
        unset($segmentos); // Eliminaos segementos

        /*
        echo "QUERY_STRING: ".$_SERVER['QUERY_STRING']."<br />";
        echo "Ruta: ". $this->ruta."<br />";
        echo "Controlador: ". $this->controlador."<br />";
        echo "Accion: ". $this->accion."<br />";
        echo "Sufijo: ". $this->sufijo."<br />";
        echo 'Paramtros: <pre>';
        print_r($this->parametros);
        echo '</pre>';
        exit;
        */

        // ROUTING
        // Definiendo patrones de direcciones por un archivo

        if (file_exists(BASE_PATH_APP . "app" . DIRSEP . "configuracion" . DIRSEP . "rutas.php")) {
            include_once(BASE_PATH_APP . "app" . DIRSEP . "configuracion" . DIRSEP . "rutas.php");
        }

        if (isset($rutas)) {
            foreach ($rutas as $ruta) {
                $patron = $ruta[0];
                if (preg_match($patron, $this->ruta)) {
                    $ruta_destino = $ruta[1];
                    if (is_array($ruta[1])) {
                        if ($ruta[1][0] === 0) {
                            $ruta_destino = str_replace($ruta[1][1], $ruta[1][2], $this->ruta);
                        } else if ($ruta[1][0] === 1) {
                            $ruta_destino = preg_replace($ruta[1][1], $ruta[1][2], $this->ruta);
                        }
                    }
                    //echo $ruta_destino.' - '.$patron.'<br />';
                    $segmentos = explode("/", $ruta_destino);
                    $segmentos = array_filter($segmentos);
                    $this->controlador = $segmentos[0];
                    array_shift($segmentos);
                    $this->accion = $segmentos[0];
                    array_shift($segmentos);
                    // Si se permiten los sufijos hay que limpiar el segemento con sufijo
                    if ($this->sufijo !== FALSE) {
                        if (!empty($segmentos)) {
                            $param = count($segmentos) - 1;
                            foreach ($this->sufijo as $sufijo) {
                                $segmentos[$param] = str_replace("." . $sufijo, "", $segmentos[$param]);
                            }
                        } else if (!empty($this->accion)) {
                            foreach ($this->sufijo as $sufijo) {
                                $this->accion = str_replace("." . $sufijo, "", $this->accion);
                            }
                        }
                    }
                    $this->parametros = array_unique(array_merge($segmentos, $this->parametros));
                    // Nos salimos, solo apliacamos un enrutado
                    break;
                }
            }

        }

        /*
        echo "QUERY_STRING: ".$_SERVER['QUERY_STRING']."<br />";
        echo "Ruta: ". $this->ruta."<br />";
        echo "Controlador: ". $this->controlador."<br />";
        echo "Accion: ". $this->accion."<br />";
        echo "Sufijo: ". $this->sufijo."<br />";
        echo 'Paramtros: <pre>';
        print_r($this->parametros);
        echo '</pre>';
        exit;
        */
    }

    public function setRuta($ruta)
    {
        $this->ruta = $ruta;
    }

    public function getRuta()
    {
        return $this->ruta;
    }

    public function getControlador()
    {
        return $this->controlador;
    }

    public function getAccion()
    {
        return $this->accion;
    }

    public function getParametros()
    {
        return $this->parametros;
    }

    public function getPost($sanar = TRUE)
    {
        $post = array();
        if (is_array($_POST)) {
            if ($sanar == TRUE) {
                foreach ($_POST as $key => $val) {
                    if (is_array($val)) {
                        $tmp = array();
                        foreach ($val as $key2 => $val2) {
                            if (strpos($val2, "\r") !== FALSE) {
                                // Standardize newlines
                                $val2 = str_replace(array("\r\n", "\r"), "\n", $val2);
                            }
                            $tmp[preg_replace("/[^a-z0-9\-]/", "", trim($key2))] = trim($val2);
                        }
                        $post[preg_replace("/[^a-z0-9\-]/", "", trim($key))] = $tmp;
                    } else {
                        if (strpos($val, "\r") !== FALSE) {
                            // Standardize newlines
                            $val = str_replace(array("\r\n", "\r"), "\n", $val);
                        }
                        $post[preg_replace("/[^a-z0-9\-]/", "", trim($key))] = trim($val);
                    }
                }
            } else {
                foreach ($_POST as $key => $val) {
                    $post[$key] = trim($val);
                }
            }
        }
        return $post;
    }

    public function forward($controlador = "index", $accion = "index", $parametros = array())
    {
        $this->controlador = $controlador;
        $this->accion = $accion;
        $this->parametros = $parametros;
        $this->ruta = $controlador . '/' . $accion . '/' . implode("/", $parametros);

        // Ejecutamos el forward
        ControladorFrontal::setEnrutador($this);
        ControladorFrontal::ejecutar();
    }

    public function redireccionar($url)
    {
        header("Location: " . $url, TRUE);
        exit;
    }

    private function validarSufijo()
    {
        // Si se permiten los sufijos hay que limpiar el segemento con sufijo
        if ($this->sufijo !== FALSE) {
            if (!empty($this->parametros)) {
                // Para todos los sufijos permitidos
                $param = count($this->parametros) - 1;
                foreach ($this->sufijo as $sufijo) {
                    $this->parametros[$param] = str_replace("." . $sufijo, "", $this->parametros[$param]);
                }
            } else if (!empty($this->accion)) {
                // Para todos los sufijos permitidos
                $param = count($this->parametros) - 1;
                foreach ($this->sufijo as $sufijo) {
                    $this->accion = str_replace("." . $sufijo, "", $this->accion);
                }
            }
        }
    }

    function __destruct()
    {

    }


}

?>
