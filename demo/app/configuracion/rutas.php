<?php
// Orden del routing importante a tener encuenta
// Solo regla por enrrutamiento - en cuantro se cumpla una, no se procesan mas reglas.
// El primer parametro es un patron para el preg_match
// directo
//$rutas[] = array("/aaa/i","aaa/");

	// Acortamiento de direcciones
$rutas[] = array("/^\bcontacto\b/i",array(0,"contacto","inicio/contacto"));

$rutas[] = array("/^sitemap\.xml/i",array(0,"sitemap.xml","inicio/site"));
$rutas[] = array("/^favicon\.ico/i",array(0,"favicon.ico","redireccion/favicon/")); // Para corregir peticiones a /favicon.ico


// preg_replace
//$rutas[] = array("noticias/",array(1,'/noticias\//','noticias/ver/'));
?>