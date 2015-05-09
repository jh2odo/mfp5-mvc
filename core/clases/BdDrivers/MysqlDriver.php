<?php

require BASE_PATH . 'core' . DIRSEP . 'clases' . DIRSEP . 'BdDrivers' . DIRSEP . 'BdDriver.php';;

class MysqlDriver implements BdDriver
{

    private $conexion;
    private $resultados = array(); // Almacena un array de las consultas SELECT
    private $ultimaConsulta = ""; // La ultima consulta

    // Constantes de tipo de resultados
    const ASSOC = 'ASSOC';
    const NUM = 'NUM';
    const BOTH = 'BOTH';
    const ROW = 'ROW';
    const OBJECT = 'OBJECT';

    public function __construct($bdHost, $bdDatabase, $bdUser, $bdPassword, $bdPersistent = FALSE, $charset = "utf8")
    {
        try {
            if (!empty($bdHost) && !empty($bdDatabase) && !empty($bdUser)) {
                if ($bdPersistent == TRUE) {
                    $this->conexion = mysql_pconnect($bdHost, $bdUser, $bdPassword);
                } else {
                    $this->conexion = mysql_connect($bdHost, $bdUser, $bdPassword);
                }

                if (!$this->conexion) {
                    throw new Exception("La Base de datos no se pudo conectar. Error Mysql: " . mysql_error());
                }

                if (!mysql_select_db($bdDatabase, $this->conexion)) {
                    throw new Exception("La Base de datos '{$bdDatabase}'. Error Mysql: " . mysql_error());
                }

                mysql_set_charset($charset, $this->conexion);

            } else {
                throw new Exception("Debes introducir un usuario, password, host y base de datos para conectarse.");
            }
        } catch (Exception $e) {
            trigger_error($e, E_USER_ERROR);
        }
    }

    // Limpiamos los resultados guardados
    public function borrarResultados()
    {
        $this->resultados = array();
    }

    private function getUltimoResultado()
    {
        if (!$this->conexion) {
            return FALSE;
        }
        return $this->resultados[$this->ultimaConsulta];
    }

    public function getEstadoConexion()
    {
        if (!$this->conexion) {
            return FALSE;
        }
        return TRUE;
    }

    public function ejecutar($sql, $datos = array())
    {

        if (!$this->conexion) {
            return FALSE;
        }

        $sql = $this->prepararConsulta($sql, $datos);

        //file_put_contents(BASE_PATH.'tmp/sql.txt',$sql,"+a");

        $partes = explode(" ", trim($sql));
        $tipo = strtoupper($partes[0]);
        $hash = md5($sql);

        /*
        $resultado = mysql_query($sql,$this->conexion);
        Cargador::cargar("Debug")->sql($sql);
        if ("SELECT" == $tipo || 'SHOW'== $tipo || 'DESCRIBE'== $tipo || 'EXPLAIN'== $tipo){
            $this->ultimaConsulta = $hash;
            $this->resultados[$hash] = $resultado;
        }else if("INSERT"==$type){
            return $this->ultimoInsertId();
        }
        return TRUE;
        */
        //Cargador::cargar("Debug")->sql($sql);

        if ("SELECT" == $tipo || 'SHOW' == $tipo || 'DESCRIBE' == $tipo || 'EXPLAIN' == $tipo) {
            // Se guarda el hash de la ultima consulta select, aunque falle
            if (isset($this->resultados[$hash])) {
                $this->ultimaConsulta = $hash;
                if (is_resource($this->resultados[$hash])) {
                    return TRUE;
                }
            }
            $this->ultimaConsulta = $hash;
        } else if ("UPDATE" == $tipo || "DELETE" == $tipo) {
            $this->resultados = array();
        }


        $resultado = mysql_query($sql, $this->conexion);


        if (!$resultado) {
            trigger_error("Consulta Invalida: " . mysql_error() . "\n SQL: " . $sql . "\n",
                E_USER_ERROR);
            return FALSE; //Traza: \n".Debug::backtrace(TRUE)
        }

        if ("SELECT" == $tipo || 'SHOW' == $tipo || 'DESCRIBE' == $tipo || 'EXPLAIN' == $tipo) {
            $this->resultados[$hash] = $resultado;
        } else if ("INSERT" == $tipo) {
            return $this->ultimoInsertId();
        }

        return TRUE;
    }

    public function numeroFilas()
    {

        if (!$this->conexion) {
            return FALSE;
        }

        $ultimoResultado = $this->resultados[$this->ultimaConsulta];

        $total = mysql_num_rows($ultimoResultado);
        if (!$total) {
            $total = 0;
        }
        return $total;
    }

    public function filasAfectadas()
    {

        if (!$this->conexion) {
            return FALSE;
        }

        return @mysql_affected_rows($this->conexion);
    }

    public function ultimoInsertId()
    {

        if (!$this->conexion) {
            return FALSE;
        }

        return @mysql_insert_id($this->conexion);
    }


    public function transaccionBegin()
    {
        $this->ejecutar('SET AUTOCOMMIT=0');
        $this->ejecutar('START TRANSACTION'); // can also be BEGIN or BEGIN WORK
        return TRUE;
    }

    public function transaccionCommit()
    {
        $this->ejecutar('COMMIT');
        $this->ejecutar('SET AUTOCOMMIT=1');
        return TRUE;
    }


    public function transaccionRollback()
    {
        $this->ejecutar('ROLLBACK');
        $this->ejecutar('SET AUTOCOMMIT=1');
        return TRUE;
    }


    public function getFila($tipoResultado = 'ASSOC')
    {

        if (!$this->conexion) {
            return FALSE;
        }

        $ultimaConsulta = $this->resultados[$this->ultimaConsulta];
        if (self::ASSOC == $tipoResultado) {
            $row = mysql_fetch_assoc($ultimaConsulta);
        } elseif (self::ROW == $tipoResultado) {
            $row = mysql_fetch_row($ultimaConsulta);
        } elseif (self::OBJECT == $tipoResultado) {
            $row = mysql_fetch_object($ultimaConsulta);
        } else {
            if (self::NUM || self::BOTH) {
                $row = mysql_fetch_array($ultimaConsulta, 'MYSQL_' . $tipoResultado);
            }
            $row = mysql_fetch_array($ultimaConsulta, 'MYSQL_BOTH');
        }
        return $row;
    }

    public function getFilaDesde($inicio = 0, $tipoResultado = 'ASSOC')
    {

        if (!$this->conexion) {
            return FALSE;
        }

        $ultimaConsulta = $this->resultados[$this->ultimaConsulta];
        if (($inicio >= 0) && ($this->numeroFilas() > 0)) {
            mysql_data_seek($ultimaConsulta, $inicio);
        } else {
            $this->rebobinar();
        }
        return $this->getFila($tipoResultado);
    }

    public function rebobinar()
    {

        if (!$this->conexion) {
            return FALSE;
        }

        $ultimaConsulta = $this->resultados[$this->ultimaConsulta];
        if ($this->numeroFilas() > 0) {
            return mysql_data_seek($ultimaConsulta, 0);
        } else {
            return FALSE;
        }
    }

    public function getFilas($inicio, $total, $tipoResultado = 'ASSOC')
    {

        if (!$this->conexion) {
            return FALSE;
        }

        $ultimaConsulta = $this->resultados[$this->ultimaConsulta];
        $totalFilas = $this->numeroFilas();

        if (($inicio >= 0) && ($totalFilas > 0)) {
            mysql_data_seek($ultimaConsulta, $inicio);
        } else {
            return array();
        }

        if ($totalFilas < $total) {
            $total = $totalFilas;
        }

        $filas = array();
        for ($i = $inicio; $i <= ($inicio + $total); $i++) {
            $filas[] = $this->getFila($tipoResultado);
        }
        return $filas;
    }

    function __destruct()
    {

        if (!$this->conexion) {
            return FALSE;
        }

        foreach ($this->resultados as $resultado) {
            if (is_resource($resultado)) {
                mysql_free_result($resultado);
            } else {
                unset($resultado);
            }
        }
        mysql_close($this->conexion);
    }

    function getColumnas($tabla)
    {

        $this->ejecutar("SHOW COLUMNS FROM {$tabla}");
        $columnas = array();
        while ($row = $this->getFila()) {
            $columnas[] = $fila['Field'];
        }
        return $columnas;
    }

    private function prepararConsulta($sql, $datos)
    {

        if (!$this->conexion) {
            return FALSE;
        }

        if (Cargador::cargar('Configuracion')->DEBUG === TRUE)
            Debug::sql($sql);

        $sql = explode(" ", trim($sql));

        $st = array();
        for ($index = 0; $index < count($sql); $index++) {
            $valor = trim($sql[$index]);
            if (!empty($valor)) {
                $st[] = $sql[$index];
            }
        }
        $sql = $st;

        //echo "<pre>";
        //print_r($sql);
        //echo "<pre>";

        $total = count($sql);
        $totalDatos = count($datos);

        if ($totalDatos > 0) {
            $posicionInicio = FALSE;
            $posicionFin = FALSE;
            $sqlTmp = "";
            $contDatos = 0;
            for ($i = 0; $i < $total; $i++) {
                $posicionInicio = strpos($sql[$i], '\'%');
                if ($posicionInicio !== FALSE) {
                    $posicionFin = strpos($sql[$i], '\'', ($posicionInicio + 2));
                    if ($posicionFin !== FALSE) {
                        if ($contDatos < $totalDatos) {

                            //No estan controlados los datos que son distintos de %s
                            // Para d, no hace falta poner '', pero hay que pornerlas para que lo reconozca el metodo
                            // y luego este se encarga de quitarlas al hacer la consulta sql final
                            if (strpos($sql[$i], 'd', ($posicionInicio + 1)) !== FALSE) {
                                $sql[$i] = substr($sql[$i], 0, $posicionInicio) . '%d' . substr($sql[$i], ($posicionFin + 1));
                            }

                            $sqlTmp .= sprintf($sql[$i], mysql_real_escape_string($datos[$contDatos], $this->conexion)) . ' ';
                            $contDatos++;
                        }
                    }
                } else {
                    $sqlTmp .= $sql[$i] . ' ';
                }
            }
            $sql = trim($sqlTmp);
        } else {
            $sql = trim(implode(' ', $sql));
        }

        if (Cargador::cargar('Configuracion')->DEBUG === TRUE)
            Debug::sql($sql);

        return $sql;
    }

}

?>
