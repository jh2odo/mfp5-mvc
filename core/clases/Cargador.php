<?php

class Cargador
{

    private static $objetosValidos = array('Configuracion',
        'Enrutador',
        'Helper',
        'Libreria',
        'Bd',
        'Modelo',
        'Sesion');

    private static $objetos = array();

    public static function cargar($objeto, $parametros = array())
    { // string
        // Comprobamos que el objeto a cargar sea valido
        if (!in_array($objeto, self::$objetosValidos)) {
            throw new Exception("El objecto '{$objeto}' no es valido");
        }
        // Comprobamos si esta o no cargado
        if (empty(self::$objetos[$objeto])) {
            if (is_array($parametros) && count($parametros) > 0) {
                self::$objetos[$objeto] = new $objeto($parametros); // cargamos el objeto con un array pasado por parametro
                //echo $nombre_a_llamar;
                //exit;
                //self::$objetos[$objeto] = call_user_func_array(array($objeto, "__construct"), $parametros);
                //self::$objetos[$objeto] = call_user_func(array($objeto,$objeto),$parametros);
            } else {
                self::$objetos[$objeto] = new $objeto(); // cargamos el objeto
            }
        }
        return self::$objetos[$objeto]; // Devolvemos el objecto cargado
    }


    public static function bdNull()
    {
        foreach (self::$objetos as $key => $valor) {
            if ($key == 'Bd') {
                self::$objetos[$key]->__destruct();
                unset(self::$objetos[$key]);
            }
        }
        //self::$objetos = array();
    }

}

?>