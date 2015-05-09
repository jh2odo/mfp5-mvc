<?php

class Xhtml
{

    public function enlace($url, $texto, $atributos = array())
    {
        $enlace = '<a href="' . $url . '"';

        foreach ($atributos as $atributo => $valor) {
            $enlace .= ' ' . $atributo . '="' . $valor . '"';
        }

        $enlace .= '>' . $texto . '</a>';
        return $enlace;
    }

    public function ancla($nombre, $atributos = array())
    {
        $ancla = $this->enlace($nombre, '', $atributos);
        return substr_replace($ancla, "<a name", 0, 7);
    }

    public function input($tipo, $valor = "")
    {
        return '<input type="' . $tipo . '" value="' . $valor . '" />';
    }

    public function loadScript($file)
    {
        echo '<script type="text/javascript" src="' . $file . '" ></script>';
    }

    public static function addCSS($file)
    {
        echo '<link rel="stylesheet" href="' . $file . '" type="text/css" media="screen, projection" />';
    }
}

?>