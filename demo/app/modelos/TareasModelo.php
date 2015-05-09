<?php
class TareasModelo extends ModeloBase{
	
	public function __construct(){
		if(Cargador::cargar("Configuracion")->CACHE === TRUE){
			parent::__construct(TRUE,TRUE); // Habilitamos la cache en el modelo
		}else{
			parent::__construct(TRUE,FALSE);
		}
	}

	public function listadoTareas(){
			
		$tareas = array();
		$consulta = $this->bd->ejecutar("SELECT id_tarea as id, titulo_tarea as titulo
											 FROM tarea
											 ORDER BY id_tarea ASC");
		if($consulta !== FALSE){
			$this->bd->rebobinar();
			for($i=0;$i<$this->bd->numeroFilas();$i++){
                $tareas[$i] = $this->bd->getFila();
			}
			unset($consulta);
		}

		return $tareas;
	}




}
?>