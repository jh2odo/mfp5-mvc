<?php

class NoEncontrado404Excepcion extends Exception
{

    protected $url = '';

    // Url de la pagina no econtrada
    public function __construct($mensaje, $url = NULL)
    {
        $this->url = $url != NULL ? $url : $_SERVER['REQUEST_URI'];
        parent::__construct($mensaje, 0);
    }

    public function __toString()
    {
        $cadena = "Excepcion: " . __CLASS__ . "\nCodigo: [{$this->code}]\nMensaje: {$this->message}\nURL: {$this->url}";
        $cadena .= "\nFichero: {$this->file} \nLinea: {$this->line}";
        return $cadena;
    }
}

?>