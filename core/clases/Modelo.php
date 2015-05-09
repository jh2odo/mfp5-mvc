<?php

/*
 * Cargador de modelos
 */

class Modelo
{

    // Guardamos los modelos cargados
    private $modelos = array();

    public function __get($modelo)
    {
        $modelo = ucfirst($modelo) . 'Modelo';

        if (!isset($this->modelos[$modelo])) {
            $pathModelo = BASE_PATH_APP . 'app' . DIRSEP . 'modelos' . DIRSEP . $modelo . '.php';

            if (file_exists($pathModelo)) {
                require_once($pathModelo);
                $this->modelos[$modelo] = new $modelo();
            } else {
                throw new Exception("Modelo {$modelo} no encontrado. Ruta: " . $pathModelo);
            }
        }
        return $this->modelos[$modelo];
    }

}

?>