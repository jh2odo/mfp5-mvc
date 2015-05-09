<?php

class Configuracion
{

    private $configuracion;

    function __construct()
    {
        $this->configuracion = array();
    }

    public function __get($var)
    {
        if (isset($this->configuracion[$var]) === TRUE) {
            return $this->configuracion[$var];
        }
        return FALSE;
    }

    //Con set vamos guardando nuestras variables.
    public function set($nombre, $valor)
    {
        // if(!isset($this->configuracion[$nombre])){
        $this->configuracion[$nombre] = $valor;
        // }
    }

    function __destruct()
    {

    }

}

?>
