<?php

abstract class Controlador
{

    protected $vistas; // Objetos Vista
    protected $parametros;
    protected $post;
    protected $headers;

    function __construct()
    {
        $this->vistas = array();
        $this->parametros = array();
        $this->post = array();
        $this->headers = array();
    }

    function setParametros($parametros)
    {
        $this->parametros = $parametros;
    }

    function setPost($post)
    {
        $this->post = $post;
    }

    private function setHeader($header)
    {
        $this->headers[] = $header;
    }

    protected function cargarVista($vista, $datos = array(), $lala = FALSE, $header = array())
    {
        $this->vistas[$vista] = new Vista($vista);
        $this->vistas[$vista]->setVars($datos);
        //$this->vistas[$vista]->setHeader($header);
        return $this->vistas[$vista];
    }

    protected function renderVista($vista, $cadena = FALSE)
    {
        if ($cadena == TRUE) {
            return $this->vistas[$vista]->generar(TRUE);
        } else {
            return $this->vistas[$vista]->generar();
        }
    }

    function index()
    {
    } // Por defecto

    public function __toString()
    {
        return get_class($this);
    }

    function __destruct()
    {
        $total = count($this->vistas);
        for ($i = 0; $i < $total; $i++) {
            unset($this->vistas[$i]);
        }
    }

}

?>