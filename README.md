# MFP5 MVC (core)

El mini framework es un ejemplo de implementación y carga de clases sin el uso del nombrado de clases, y el uso 
recomendado es didáctico en la introdución del patrón modelo vista controlador (MVC), programado y nombrado en español.

**ES RECOMENDABLE PROGRAMAR EN INGLÉS SIEMPRE**

Preparado para ser utilazado en versiones de PHP 5.2 y superiores. Originalmente está desarrollado para PHP 5.2 porque 
no hace uso de las principales novedades con la llegada de PHP 5.3, como los "namespaces" y herramientas como 
[Composer](https://getcomposer.org/ "Composer"), lo cual a día de hoy es recomendable y obligatorio usarlos.

En la actualidad, existen framework profesionales que hacen mucho mas simple los proyectos PHP, como son: 
[Laravel](https://getcomposer.org/ "Laravel"), [Symfony](https://getcomposer.org/ "Symfony"), 
[Silex](https://getcomposer.org/ "Silex"), [Lumen](https://getcomposer.org/ "Lumen"), etc.

## Ejemplos y demos de uso MFP5 MVC con VAGRANT (DebOps)

Los ejemplos están en [MFP5 MVC Examples](https://github.com/jh2odo/mfp5-mvc-examples "GitHub MFP5 MVC Examples") y 
para su buen funcionamiento están preparados para ser ejecutados en el entorno de virtualización VAGRANT + VIRTUALBOX.

Es filosofía DevOps.

## INSTALACIÓN SIN VAGRANT (DIRECTA)

### REQUISITOS

- PHP 5.2 o superiores
- MySQL
- Apache 2
- git

### DESCARGAR CÓDIGO FUENTE O CLONAR CON GIT

Descargar o clonar el repositorio [https://github.com/jh2odo/mfp5-mvc](https://github.com/jh2odo/mfp5-mvc "GitHub MFP5 MVC") en el directorio de publicación 
de apache, o el directorio de un virtual-host 

Ejemplo:

    - Linux: git clone https://github.com/jh2odo/mfp5-mvc /var/www/mfp5
    - Windows: Descargar y descomprimir ZIP in D:\apache|htdocs\mfp5

Tras la descarga los directorios serán:

    - /demo/ -> demo funcional con holamundo listo para empezar a desarrollar
    - /core/ -> el núcleo del mini framework

### CONFIGURACIÓN

Es necesario añadir en el fichero local "hosts" la IP para el nombrado de dominios en nuestra propia máquina:

1. En el fichero "hosts" del PC, hay que añadir en modo administrador: 

    - 127.0.0.1   mfp5.dev
    - localhost   mfp5.dev
    
    Ejemplo:
    
    - Linux: /etc/hosts
    - Windows: C:\Windows\System32\drivers\etc\hosts
    
2. Ir al directorio donde descargó la aplicacion y configurar si es necesario los parametros de su 
aplicación los ficheros: 

    - /demo/app/configuracion/configuracion.php
    - /demo/app/configuracion/rutas.php
    - /demo/app/configuracion/cache.php

3. Crear la base de datos "demo" e importar el fichero /demo/docs/demo.sql para que funcione correctamente.

4. Ir al navegador que utilices y comprobar que está funcionando http://mfp5.dev/demo

Es hora de crear y modificar para desarrollar.

## USO DEL FRAMEWORK

En próximas actualizaciones estará disponible una guía de uso. 

También puede consultar las demos y ejemplos en [MFP5 MVC Examples](https://github.com/jh2odo/mfp5-mvc-examples "GitHub MFP5 MVC Examples")