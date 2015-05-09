<?php

class GeneralExcepcion extends Exception
{

    public function __toString()
    {
        return "Excepcion: " . __CLASS__ . "\nCodigo: [{$this->code}]\nMensaje: {$this->message}";
    }
}

?>