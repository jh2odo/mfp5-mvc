<?php

class Bd
{

    private $bd;

    public function __construct()
    {
        $config = Cargador::cargar("Configuracion");

        $bdDriver = $config->BD_DRIVER;
        if (empty($bdDriver)) {
            throw new Exception('Debe de especificar un driver (motor) de la base de datos.');
        }

        $driver = ucfirst($bdDriver) . 'Driver';

        $pathDriver = BASE_PATH . 'core' . DIRSEP . 'clases' . DIRSEP . 'BdDrivers' . DIRSEP . $driver . '.php';

        if (file_exists($pathDriver)) {
            require_once($pathDriver);
        } else {
            throw new Exception("Driver {$driver} no encontrado. Ruta: " . $pathDriver);
        }

        // Falta un control de si el driver implementa la interfaz BdDriver

        $this->bd = new $driver($config->BD_HOST, $config->BD_BASEDATOS, $config->BD_USUARIO, $config->BD_PASSWORD, $config->BD_PERSISTENTE);
    }

    /*
     * Sobrecargamos los metodos de la clase de la base de datos
     */
    public function __call($metodo, $argumentos)
    {
        if (empty($this->bd)) {
            return FALSE;
        }
        if (in_array($metodo, get_class_methods($this->bd))) {
            return call_user_func_array(array($this->bd, $metodo), $argumentos);
        }
        return FALSE;
    }

}

?>
