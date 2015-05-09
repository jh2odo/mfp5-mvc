<?php

/*
	 * Basado en :  http://www.phpguru.org/
	 */

final class Cache
{

    private static $habilitado = FALSE;
    private static $path = "app/cache/";
    private static $prefijo = 'cache_';
    private static $duracion = 7200; // 2 Horas (7200 segundos)
    private static $comprimir = FALSE; // Deflate

    // Para inicio y final (metodos)
    private static $grupo = NULL;
    private static $id = NULL;

    private static function guardar($grupo, $id, $datos, $duracion = NULL)
    {
        $fichero = self::getNombre($grupo, $id);
        if (self::$comprimir) {
            $datos = gzdeflate($datos, 9);
        }
        file_put_contents($fichero, $datos, LOCK_EX);
        if ($duracion != NULL) {
            touch($fichero, time() + $duracion);
        } else {
            touch($fichero, time() + self::$duracion);
        }
    }

    private static function leer($grupo, $id)
    {
        $fichero = self::getNombre($grupo, $id);
        $datos = file_get_contents($fichero);
        if (self::$comprimir) {
            $datos = gzinflate($datos);
        }
        return $datos;
    }

    // $forzar, para forzar a cojer la cache aunque el haya caducado el tiempo(duracion)
    private static function isCache($grupo, $id, $forzar = FALSE)
    {

        $fichero = self::getNombre($grupo, $id);

        if (is_readable($fichero) && $forzar) {
            return TRUE;
        }

        if (is_readable($fichero) && filemtime($fichero) > time()) {
            return TRUE;
        }

        if (is_readable($fichero)) {
            @unlink($fichero);
        }

        return FALSE;
    }

    private static function getNombre($grupo, $id)
    {
        $id = md5($id);
        return self::$path . self::$prefijo . "{$grupo}_{$id}";
    }

    public static function setPrefijo($prefijo)
    {
        self::$prefijo = $prefijo;
    }

    public static function setPath($path)
    {
        self::$path = $path;
    }

    public static function setHabilitado($habilitado = FALSE)
    {
        self::$habilitado = $habilitado;
    }

    public static function setDuracion($duracion = 7200)
    {
        self::$duracion = $duracion;
    }

    public static function setComprimir($comprimir = FALSE)
    {
        self::$comprimir = $comprimir;
    }

    public static function inicio($grupo, $id)
    {

        if (self::$habilitado != TRUE) {
            return FALSE;
        }

        ob_start();

        self::$grupo = $grupo;
        self::$id = $id;

    }


    public static function fin($duracion = NULL)
    {

        if (self::$habilitado != TRUE) {
            return FALSE;
        }

        $datos = ob_get_contents();
        ob_end_flush();

        if ($duracion < 0) {
            $duracion = self::$duracion;
        }

        self::guardar(self::$grupo, self::$id, $datos, $duracion);
    }


    public static function estaCacheado($grupo, $id, $forzar = FALSE)
    {

        if (self::$habilitado != TRUE) {
            return FALSE;
        }

        if (self::isCache($grupo, $id, $forzar)) {
            return TRUE;
        }
        return FALSE;
    }


    public static function get($grupo, $id, $forzar = FALSE, $serializable = TRUE)
    {

        if (self::$habilitado != TRUE) {
            return NULL;
        }

        if (self::isCache($grupo, $id, $forzar)) {
            if ($serializable) {
                return unserialize(self::leer($grupo, $id));
            } else {
                return self::leer($grupo, $id);
            }
        }
        return NULL;
    }

    public static function set($grupo, $id, $datos, $duracion = NULL, $serializable = TRUE)
    {

        if (self::$habilitado != TRUE) {
            return FALSE;
        }

        if ($duracion < 0) {
            $duracion = self::$duracion;
        }

        if ($serializable) {
            self::guardar($grupo, $id, serialize($datos), $duracion);
        } else {
            self::guardar($grupo, $id, $datos, $duracion);
        }

    }


}

?>