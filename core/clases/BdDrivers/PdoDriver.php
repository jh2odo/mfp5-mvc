<?php

define ("FETCH_ASSOC", 1);
define ("FETCH_ROW", 2);
define ("FETCH_BOTH", 3);
define ("FETCH_OBJECT", 3);

require BASE_PATH . 'core' . DIRSEP . 'clases' . DIRSEP . 'BdDrivers' . DIRSEP . 'BdDriver.php';;

class PdoDriver implements BdDriver
{
    private $connection;
    private $results = array();
    private $lasthash = "";
    private $pdo;
    private $affectedrows;

    public function __construct($bdHost, $bdDatabase, $bdUser, $bdPassword, $bdPersistent = FALSE, $charset = "utf8")
    {
        try {
            if (!empty($bdHost) && !empty($bdDatabase) && !empty($bdUser)) {
                $this->pdo = new PDO($bdDatabase, $bdUser, $bdPassword);
            } else {
                throw new Exception("Debes introducir un usuario, password, host y base de datos para conectarse.");
            }
        } catch (Exception $e) {
            trigger_error($e, E_USER_ERROR);
        }
    }

    public function getEstadoConexion()
    {

    }

    private function getUltimoResultado()
    {
        return $this->results[$this->lasthash];
    }

    public function ejecutar($sql, $datos = array())
    {
        $sql = $this->prepQuery($sql);
        $parts = split(" ", trim($sql));
        $type = strtolower($parts[0]);
        $hash = md5($sql);
        $this->affectedrows = 0;

        if ("select" == $type) {
            $this->lasthash = $hash;
            $this->results[$hash] = $this->pdo->query($sql);
            return $this->results[$hash];

            if (isset($this->results[$hash])) {
                $this->lasthash = $hash;
                if (is_resource($this->results[$hash]))
                    return $this->results[$hash];
            } else {
                $this->lasthash = $hash;
                $this->results[$hash] = $this->pdo->query($sql);
                return $this->results[$hash];
            }
        } else {
            $this->results = array(); //clear the result cache
            $this->affectedrows = $this->pdo->exec($sql);
        }

        if ("insert" == $type) return $this->ultimoInsertId();
        return true;

    }

    public function numeroFilas()
    {
        //print_r($this);
        $lastresult = $this->results[$this->lasthash];
        //print_r($this->results);
        $count = $lastresult->rowCount();
        if (!$count) $count = 0;
        return $count;
    }


    private function prepQuery($sql)
    {
        // "DELETE FROM TABLE" returns 0 affected rows This hack modifies
        // the query so that it returns the number of affected rows
        if (preg_match('/^\s*DELETE\s+FROM\s+(\S+)\s*$/i', $sql)) {
            $sql = preg_replace("/^\s*DELETE\s+FROM\s+(\S+)\s*$/", "DELETE FROM \\1 WHERE 1=1", $sql);
        }


        return $sql;
    }

    public function escape($sql)
    {
        return $sql;
    }


    public function filasAfectadas()
    {
        return $this->affectedRows();
    }

    public function ultimoInsertId()
    {
        return $this->pdo->lastInsertId();
    }


    public function transaccionBegin()
    {
        $this->pdo->beginTransaction();
        return true;
    }

    public function transaccionCommit()
    {
        $this->pdo->commit();
        return true;
    }


    public function transaccionRollback()
    {
        $this->pdo->rollBack();
        return true;
    }


    public function getFila($fetchmode = FETCH_ASSOC)
    {

        $lastresult = $this->results[$this->lasthash];
        if (FETCH_ASSOC == $fetchmode)
            $row = $lastresult->fetch(PDO::FETCH_ASSOC);
        elseif (FETCH_ROW == $fetchmode)
            $row = $lastresult->fetch(PDO::FETCH_NUM);
        elseif (FETCH_OBJECT == $fetchmode)
            $row = $lastresult->fetch(PDO::FETCH_OBJ);
        else
            $row = $lastresult->fetch(PDO::FETCH_BOTH);
        return $row;
    }

    public function getFilaDesde($offset = null, $fetchmode = FETCH_ASSOC)
    {
        if (!empty($offset))
            $lastresult = $this->results[$this->lasthash];
        $lastresult->execute();
        for ($i = 0; $i < $offset; $i++)
            $lastresult->fetch();
        return $this->getFila($fetchmode, $fetchmode);
    }

    public function rebobinar()
    {
        $lastresult = $this->results[$this->lasthash];
    }

    public function getFilas($start, $count, $fetchmode = FETCH_ASSOC)
    {
        $lastresult = $this->results[$this->lasthash];
        $lastresult->execute;
        for ($i = 0; $i < $start; $i++)
            $lastresult->fetch();
        $rows = array();
        for ($i = $start; $i <= ($start + $count); $i++) {
            $rows[] = $this->getFila($fetchmode);
        }
        return $rows;
    }

    function getColumnas($tabla)
    {
        return FALSE;
    }

    function __destruct()
    {
        foreach ($this->results as $result) {
            @pdo_free_result($result);
        }
    }


}

?>
