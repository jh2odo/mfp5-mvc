<?php

interface BdDriver
{

    public function __construct($bdHost, $bdDatabase, $bdUser, $bdPassword, $bdPersistent = FALSE, $charset = 'utf8');

    public function getEstadoConexion();

    public function ejecutar($sql, $datos = array());

    public function numeroFilas(); // SELECT

    public function filasAfectadas(); // INSERT, UPDATE o DELETE

    public function ultimoInsertId();

    public function transaccionBegin();

    public function transaccionCommit();

    public function transaccionRollback();

    public function getFila($tipoResultado = 'ASSOC');

    public function getFilaDesde($inicio = NULL, $tipoResultado = 'ASSOC');

    public function rebobinar();

    public function getFilas($inicio, $total, $tipoResultado = 'ASSOC');

    public function getColumnas($tabla);

}

?>