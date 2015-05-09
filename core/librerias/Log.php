<?php

class Log
{

    private $logs;
    private $tipos = array('ERROR' => 1,
        'ALERT' => 2,
        'INFO' => 3,
        'DEBUG' => 4
    );
    private $umbral;
    private $ruta;
    private $fichero;
    private $enabled = FALSE;

    public function __construct($parametros = array())
    {
        $this->ruta = isset($parametros['ruta']) == true ? $parametros['ruta'] : "";
        $this->fichero = isset($parametros['fichero']) == true ? $parametros['fichero'] : 'log-' . date('Y-m-d') . '.log';
        $this->umbral = isset($parametros['umbral']) == true ? $parametros['umbral'] : 2;
    }

    public function getRuta()
    {
        return $this->ruta;
    }

    public function setRuta($ruta)
    {
        $this->ruta = $ruta;
    }

    public function setFichero($fichero)
    {
        $this->fichero = $fichero;
    }

    public function setEnabled($enabled = TRUE)
    {
        $this->enabled = $enabled;
    }

    public function setUmbral($umbral = 2)
    {
        $this->umbral = $umbral;
    }

    public function registrar($tipo, $mensaje)
    {
        if ($this->enabled == TRUE) {
            if ($this->tipos[$tipo] <= $this->umbral) {
                $this->logs[] = array(date('Y-m-d H:i:s'), $tipo, $mensaje);
            }
        }
    }

    public function guardar()
    {

        if ($this->enabled == FALSE) {
            return FALSE;
        }

        if (empty($this->logs)) {
            return FALSE;
        }

        $archivo_log = $this->ruta . $this->fichero;

        $msg = array();
        do {
            // Load the next mess
            list ($fecha, $tipo, $texto) = array_shift($this->logs);

            // Add a new message line
            $msg[] = $fecha . ' :: ' . (($tipo == 'INFO') || ($tipo == 'CORE') ? ($tipo . ' ') : $tipo) . ' :: ' . $texto;
        } while (!empty($this->logs));

        // Write messages to log file
        file_put_contents($archivo_log, implode(PHP_EOL, $msg) . PHP_EOL, FILE_APPEND);

        // Borramos para no guardar dos veces el mismo log
        $this->borrar();

        return TRUE;

    }

    public function borrar()
    {
        if ($this->enabled == TRUE) {
            $this->logs = array();
        }
    }

}

?>