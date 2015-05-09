<?php

class inicio extends Controlador{

    function index(){

        if(!empty($this->parametros)){
            throw new NoEncontrado404Excepcion("la página 'inicio/index/' no tiene parametros.");
        }

        $tareasModelo = Cargador::cargar('Modelo')->Tareas;

        $tareas = $tareasModelo->listadoTareas();

        $pagina = array();
        $pagina["contenido"] = $this->cargarVista('inicio/index',array("tareas"=>$tareas))->generar(TRUE);

        $head = array();
        $head["title"] = 'Demo - MFP5 MVC';
        $head["keywords"] = 'demo, micro, frameworkk, github, jh2odo';
        $head["descripcion"] = 'Demo de uso de mini framework';
        $head["robots"] = 'index,follow';
        $head["charset_encoding"] = Cargador::cargar('Configuracion')->CHARSET_ENCODING;
        $head["css"][] = array("nombre"=>"general","extension"=>"css");
        $head["js"][] = NULL;
        $head["extras"] = NULL;
        $head["canonical"] = Cargador::cargar('Configuracion')->BASE_URL;

        $titulo = 'Inicio';

        $this->esqueleto($head,$pagina,$titulo);
    }

    function contacto(){

        if(!empty($this->parametros)){
            throw new NoEncontrado404Excepcion("la página 'inicio/contacto/' no tiene parametros.");
        }

        // Humano Captcha
        // Iniciamos la sesion para perdurar valores
        $sesion = Cargador::cargar('Sesion');
        $sesion->start();

        $CAPTCHA = Cargador::cargar('Libreria')->Humano;
        $CAPTCHA->setModo(rand(1,3));
        $CAPTCHA->setTipo('texto');

        $captcha = array();
        $captcha["pregunta"] = $CAPTCHA->automatico();
        $captcha["respuesta"] = $CAPTCHA->getValido();

        // Se ha enviado el formulario
        $formulario = array();
        if(!empty($this->post)){
            $validado = TRUE; //Por defecto validado
            $enviado = FALSE;

            $mensajeUnico = md5($this->post["nombre"].$this->post["email"].$this->post["asunto"].$this->post["mensaje"]);

            $formulario["asunto"] = $this->post["asunto"];
            $formulario["mensaje"] = $this->post["mensaje"];
            $formulario["nombre"] = $this->post["nombre"];
            $formulario["email"] = $this->post["email"];

            $formulario["estado"] = '<ul style="color:red;">';
            // Comprobamos que exiten todos los campos obligatorios
            if(!isset($this->post["asunto"]) || !isset($this->post["mensaje"]) || !isset($this->post["humano"]) || !isset($this->post["enviar"])){
                // Posible envio de datos de otra pagina distinta a la original
                $formulario["estado"] .= '<li>Error, utilice el formulario adecuadamente.</li>';
                $validado = FALSE;
            }

            // Validamos que se haya enviado con el boton Enviar
            if($this->post["enviar"] != "Enviar"){
                $formulario["estado"] .= '<li>Error, envie el formulario correctamente.</li>';
                $validado = FALSE;
            }

            // Validamos el Asunto y el mensaje, nombre y email
            if(empty($this->post["asunto"]) || empty($this->post["mensaje"]) || empty($this->post["nombre"]) || empty($this->post["email"])){
                $formulario["estado"] .= '<li>El nombre, el email, el asunto y el mensaje no pueden estar vacios.</li>';
                $validado = FALSE;
            }

            // Validamos que sea humano
            if(empty($this->post["humano"]) || $this->post["humano"] != $sesion->get("captcha")){
                $formulario["estado"] .= '<li>No ha contestado correctamente a la pregunta.</li>';
                $validado = FALSE;
            }

            // Validamos el maximo de longitud del campo nombre
            if(strlen($formulario["nombre"]) > 40){
                $formulario["estado"] .= '<li>El nombre es demasiado grande(máximo 40 caracteres).</li>';
                $validado = FALSE;
            }

            // Validamos el maximo de longitud del campo nombre
            if(strlen($formulario["nombre"]) > 40){
                $formulario["estado"] .= '<li>El nombre es demasiado grande(máximo 40 caracteres).</li>';
                $validado = FALSE;
            }

            // Validamos el maximo de longitud del campo asunto
            if(strlen($formulario["asunto"]) > 50){
                $formulario["estado"] .= '<li>El asunto es demasiado grande(máximo 50 caracteres).</li>';
                $validado = FALSE;
            }

            // Comprobamos que no se hayan reenviado...
            if(file_exists(BASE_PATH . 'tmp' . DIRSEP . $mensajeUnico)){
                $formulario["estado"] .= '<li>Ya se ha enviado y no puede reenviar un mismo mensaje.</li>';
                $validado = FALSE;
            }

            $formulario["estado"] .= '</ul>';

            $formulario["asunto"] = htmlentities(trim($this->post["asunto"]), ENT_QUOTES,'UTF-8');
            $formulario["mensaje"] = htmlentities(trim($this->post["mensaje"]), ENT_QUOTES,'UTF-8');
            $formulario["nombre"] = htmlentities(trim($formulario["nombre"]), ENT_QUOTES,'UTF-8'); // Los cojemos de la variable formulario
            $formulario["email"] = htmlentities(trim($formulario["email"]), ENT_QUOTES,'UTF-8');

            if($validado === TRUE){

                $this->post["asunto"] = trim($this->post["asunto"]);
                $this->post["mensaje"] = trim($this->post["mensaje"]);
                $this->post["nombre"] = trim($this->post["nombre"]);
                $this->post["email"] = trim($this->post["email"]);


                $email = Cargador::cargar('Libreria')->Email;

                $mensaje   = "Formulario de Contacto...\r\n\r\nASUNTO: " . $this->post["asunto"];
                $mensaje  .= "\r\n\r\nNOMBRE: " . $this->post["nombre"]."\r\n\r\nEMAIL: " . $this->post["email"];
                $mensaje  .= "\r\n\r\nMENSAJE:\r\n" . $this->post["mensaje"];

                $data = array(  Cargador::cargar('Configuracion')->EMAIL_DE,
                    Cargador::cargar('Configuracion')->EMAIL_PARA,
                    'Contacto ' . Cargador::cargar('Configuracion')->DOMINIO,
                    $mensaje);

                $email->setData($data);

                $enviado = $email->enviar();

                if($enviado === TRUE){
                    // Para evitar reenvio...
                    file_put_contents(BASE_PATH . 'tmp' . DIRSEP . $mensajeUnico,"enviado");
                    $formulario["estado"] = '<p style="color:green;">Enviado correctamente.</p>';
                    $formulario["asunto"] = '';
                    $formulario["mensaje"] = '';
                    $formulario["nombre"] = '';
                    $formulario["email"] = '';
                }else{
                    $formulario["estado"] = '<p style="color:red;">No se puedo enviar. Intentelo en unos minutos</p>';
                }
            }
        }else{
            $formulario["asunto"] = '';
            $formulario["mensaje"] = '';
            $formulario["nombre"] = '';
            $formulario["email"] = '';
            $formulario["estado"] = NULL;
        }

        // Guardamos captcha
        $sesion->set('captcha',$captcha["respuesta"]);

        $formulario["captcha"] = $captcha;


        $pagina = array();
        $pagina["contenido"] = $this->cargarVista('inicio/contacto',$formulario)->generar(TRUE);

        $head = array();
        $head["title"] = 'Contacto - Demo - MFP5 MVC';
        $head["keywords"] = 'demo, micro, frameworkk, github, jh2odo';
        $head["descripcion"] = 'Demo de uso de mini framework';
        $head["robots"] = 'noindex,follow';
        $head["charset_encoding"] = Cargador::cargar('Configuracion')->CHARSET_ENCODING;
        $head["css"][] = array("nombre"=>"general","extension"=>"css");
        $head["js"] = NULL;
        $head["extras"] = NULL;

        $titulo = 'Contacto';

        $this->esqueleto($head,$pagina,$titulo);

    }

    public function site(){

        if(!empty($this->parametros)){
            throw new NoEncontrado404Excepcion("la página 'inicio/site/' no tiene parametros.");
        }

        $base_url = Cargador::cargar('Configuracion')->BASE_URL;

        $urls = array(0 => array("loc"=>$base_url,
            "lastmod"=>"",
            "changefreq"=>"monthly",
            "priority"=>"1"));

        $urls[] = array("loc"=> $base_url."contacto/","changefreq"=>"yearly");

        $header = array("Content-type" => "text/xml; charset=utf-8");

        $this->cargarVista('inicio/sitemap',array("urls"=>$urls),FALSE,$header)->generar();

    }

    private function esqueleto($head = array("head"=>array("title"=>"Demo")),$pagina = array(),$titulo = ''){

        $body = array();
        $body['cabecera'] = $this->cargarVista('cabecera',array("titulo"=>$titulo))->generar(TRUE);
        $body['pagina'] = $this->cargarVista('pagina',array("pagina"=>$pagina))->generar(TRUE);
        $body['pie'] = $this->cargarVista('pie')->generar(TRUE);

        $this->cargarVista('head',$head,FALSE,array("Content-type" => "text/html; charset=utf-8"))->generar();
        $this->cargarVista('body',$body)->generar();
    }

}
?>