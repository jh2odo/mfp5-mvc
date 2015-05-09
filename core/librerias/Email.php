<?php

class Email
{

    private $de = '';
    private $para = '';
    private $asunto = '';
    private $mensaje = '';
    private $cabeceras = array();

    private $exito = FALSE;

    public function __construct($de = '', $para = '', $asunto = '', $mensaje = '', $cabeceras = array())
    {
        $this->setData($de, $para, $asunto, $mensaje, $cabeceras);
    }

    public function setData($de = '', $para = '', $asunto = '', $mensaje = '', $cabeceras = array())
    {
        $this->de = $de;
        $this->para = $para;
        $this->asunto = $asunto;
        $this->mensaje = $mensaje;
        $this->cabeceras = $cabeceras;
    }

    private function procesar()
    {
        $this->cabeceras = 'From: ' . $this->de . "\r\n" .
            'Reply-To: ' . $this->de . "\r\n" .
            'X-Mailer: PHP/' . phpversion() . "\r\n" .
            'Content-type: text/plain; charset=utf-8';

        $this->mensaje = wordwrap($this->mensaje, 70);
    }

    public function enviar()
    {
        $this->procesar();
        $this->exito = mail($this->para, $this->asunto, wordwrap($this->mensaje, 70), $this->cabeceras);
        if (!$this->exito) {
            return FALSE;
        } else {
            return TRUE;
        }
    }

}

?>