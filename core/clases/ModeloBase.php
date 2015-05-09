<?php

class ModeloBase
{
    protected $bd = FALSE;
    protected $cache = FALSE; // True o False

    public function __construct($bd = FALSE, $cache = FALSE)
    {
        //Traemos la unica instancia de PDO
        if ($bd === true) {
            $this->bd = Cargador::cargar('Bd');
        } else {
            $this->bd = NULL;
        }
        if ($cache === TRUE) {
            $this->cache = TRUE;
        }
    }

    public function arrayToObject($array = array())
    {
        return (object)$array;
    }

    public function bdNull()
    {
        if ($this->bd != null && $this->bd != FALSE) {
            $this->bd = NULL;
            Cargador::bdNull();
        }
    }

}

?>