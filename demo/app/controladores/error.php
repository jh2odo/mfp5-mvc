<?php
class error extends Controlador{
	
	// sobrescribo el metodo index
	function index(){
		$this->esqueleto('index','Error');
	}

	function e404(){
		
		// este error no lo registramos porque ya hay una excepcion del sistema definida
		// en el core de la aplicacion que registra el error.
		// Solo queda mostrar
		//trigger_error('Error 404 - Ruta: '.$_SERVER['REQUEST_URI'] ,E_USER_NOTICE);
		header("HTTP/1.0 404 Not Found",TRUE,404);
		$this->esqueleto('e404','Error 404');
		
	}
	
	function e403(){
					
		// Registramos este error //\nRuta Procesada: ". Cargador::cargar("Enrutador")->getRuta() 
		trigger_error('Error 403 - Ruta Real: '. $_SERVER['REQUEST_URI'] ,E_USER_WARNING);
		// Usamos el 404 para ocultar el verdadero error al usuario
		$this->e404();
		//header('HTTP/1.1 403 Forbidden');
		//$this->esqueleto('e403','PrensaSeria - Error 403');
		
	}
	
	function e500(){
		// Registramos este error
		trigger_error('Error 500 - Ruta: '. $_SERVER['REQUEST_URI'] ,E_USER_ERROR);
		// Usamos el 404 para ocultar el verdadero error al usuario
		$this->e404();
		//header('HTTP/1.1 500 Internal Server Error');
		//$this->esqueleto('e500','PrensaSeria - Error 500');
	}
	
	private function esqueleto($error,$titulo){

		
		$head = array();
		$head["title"] = $titulo . ' - Demo';
		$head["keywords"] = NULL;
		$head["descripcion"] = NULL;
		$head["robots"] = 'noindex,nofollow';
		$head["charset_encoding"] = Cargador::cargar('Configuracion')->CHARSET_ENCODING;
		$head["css"] = NULL;
		$head["js"] = NULL;
		$head["extras"] = NULL;
		
		$pagina = array();
		$pagina["todo"] = $this->cargarVista('error/'.$error)->generar(TRUE);
		
		$body = array();
		$body['cabecera'] = $this->cargarVista('cabecera',array("titulo"=>$titulo))->generar(TRUE);
		$body['pagina'] = $this->cargarVista('pagina',array("pagina"=>$pagina))->generar(TRUE);
		$body['pie'] = $this->cargarVista('pie')->generar(TRUE);
		
		$this->cargarVista('head',$head,FALSE,array("Content-type" => "text/html; charset=".$head["charset_encoding"]))->generar();
		$this->cargarVista('body',$body)->generar();
	}

}
?>