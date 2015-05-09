<?php

/**
 * Sistema de carga para las librerias
 */
class Libreria
{
    private $librerias = array(); // Librerias cargadas

    public function __get($libreria)
    {
        if (!isset($this->librerias[$libreria])) {

            $pathLibreriaCore = BASE_PATH . "core" . DIRSEP . "librerias" . DIRSEP . "{$libreria}.php";
            $pathLibreriaApp = BASE_PATH_APP . "app" . DIRSEP . "librerias" . DIRSEP . "{$libreria}.php";

            if (file_exists($pathLibreriaCore)) {
                require_once($pathLibreriaCore);
                $this->librerias[$libreria] = new $libreria();
            } else if (file_exists($pathLibreriaApp)) {
                require_once($pathLibreriaApp);
                $this->librerias[$libreria] = new $libreria();
            } else {
                throw new Exception("Libreria {$libreria} no encontrada.");
            }
        }
        return $this->librerias[$libreria];
    }

}

?>