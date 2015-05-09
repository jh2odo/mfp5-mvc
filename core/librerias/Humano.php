<?php
// Completely Automated Public Turing test to tell Computers and Humans Apart 
// Prueba de Turing pública y automática para diferenciar a máquinas y humanos
class Humano
{

    private $tipo = 'imagen'; // Dos valores imagen ó texto
    private $datos = NULL;
    private $valido = NULL; // Valor que pasa el captcha
    private $modo = 1;

    public function __construct($modo = 1, $tipo = 'texto')
    {
        $this->inicializar($modo, $tipo);
    }

    public function setModo($modo)
    {

        if ($modo === 1) {
            $this->sumarNumeros();
        } else if ($modo === 2) {
            $this->sumarCifrasAlternas(TRUE);
        } else if ($modo === 3) {
            $this->sumarCifrasAlternas(FALSE);
        } else {
            $this->sumarNumeros();
            $modo = 1;
        }
        $this->modo = $modo;
    }

    public function setTipo($tipo)
    {
        if ($tipo == 'texto') {
            $this->tipo = 'texto';
        } else {
            $this->tipo = 'imagen';
        }
    }

    public function inicializar($modo = 1, $tipo = 'texto')
    {
        $this->setTipo($tipo);
        $this->setModo($modo);
    }

    public function getValido()
    {
        return $this->valido;
    }

    public function mostrar()
    {
        if ($this->tipo == 'imagen') {
            header("Content-type: image/png");
            echo $this->datos;
            exit;
        } else {
            return $this->datos;
        }
    }

    public function automatico()
    {
        if ($this->modo == 1) {
            $texto = 'Escribe la suma de los números siguientes: ' . $this->mostrar();
        } else if ($this->modo == 2) {
            $texto = 'Escribe la suma de las cifras impares de este conjunto de números: ' . $this->mostrar();
        } else if ($this->modo == 3) {
            $texto = 'Escribe la suma de las cifras pares de este conjunto de números: ' . $this->mostrar();
        } else {
            $texto = 'Escribe la suma de los números siguientes: ' . $this->mostrar();
        }
        return $texto;
    }

    // Solo para el tipo imagen
    public function base64()
    {
        if ($this->tipo == 'imagen') {
            return 'data:image/png;base64,' . base64_encode($this->datos);
        }
    }

    private function sumarNumeros()
    {
        // Suma de dos números aleatorios
        $a = rand(0, 99);
        $b = rand(0, 99);

        $this->valido = $a + $b;

        if ($this->tipo == 'imagen') {
            $imagen = imagecreatetruecolor(85, 35);
            imagestring($imagen, 5, 10, 10, "$a + $b", imagecolorallocate($imagen, 255, 255, 255));
            ob_start();
            imagepng($imagen);
            $this->datos = ob_get_contents();
            ob_clean();
        } else {
            $this->datos = $this->numerotexto($a) . ' mas ' . $this->numerotexto($b);
            unset($util);
        }
    }

    private function sumarCifrasAlternas($impar = TRUE)
    {

        $a = rand(0, 999);
        $b = rand(10, 999);
        $c = str_split($a . $b);

        $longitud = count($c);
        $this->valido = 0;
        for ($index = 0; $index < $longitud; $index++) {
            $i = ($index + 1) % 2;
            if ($i == 1 && $impar) {
                $this->valido += $c[$index];
            } else if ($i == 0 && !$impar) {
                $this->valido += $c[$index];
            }
        }

        if ($this->tipo == 'imagen') {
            $imagen = imagecreatetruecolor(75, 35);
            imagestring($imagen, 5, 10, 10, "$a$b", imagecolorallocate($imagen, 255, 255, 255));
            ob_start();
            imagepng($imagen);
            $this->datos = ob_get_contents();
            ob_clean();
        } else {
            $this->datos = $a . $b;
        }
    }

    private function numerotexto($numero) {
        // Primero tomamos el numero y le quitamos los caracteres especiales y extras
        // Dejando solamente el punto "." que separa los decimales
        // Si encuentra mas de un punto, devuelve error.
        // NOTA: Para los paises en que el punto y la coma se usan de forma
        // inversa, solo hay que cambiar la coma por punto en el array de "extras"
        // y el punto por coma en el explode de $partes

        $extras= array("/[\$]/","/ /","/,/","/-/");
        $limpio=preg_replace($extras,"",$numero);
        $partes=explode(".",$limpio);
        if (count($partes)>2) {
            return "Error, el número no es correcto";
        }

        // Ahora explotamos la parte del numero en elementos de un array que
        // llamaremos $digitos, y contamos los grupos de tres digitos
        // resultantes

        $digitos_piezas=chunk_split ($partes[0],1,"#");
        $digitos_piezas=substr($digitos_piezas,0,strlen($digitos_piezas)-1);
        $digitos=explode("#",$digitos_piezas);
        $todos=count($digitos);
        $grupos=ceil (count($digitos)/3);

        // comenzamos a dar formato a cada grupo

        $unidad = array  ('un','dos','tres','cuatro','cinco','seis','siete'  ,'ocho','nueve');
        $decenas = array ('diez','once','doce', 'trece','catorce','quince');
        $decena = array  ('dieci','veinti','treinta','cuarenta','cincuenta','sesenta','setenta','ochenta','noventa');
        $centena = array ('ciento','doscientos','trescientos','cuatrocientos','quinientos','seiscientos','setecientos','ochocientos','novecientos');
        $resto=$todos;

        for ($i=1; $i<=$grupos; $i++) {

            // Hacemos el grupo
            if ($resto>=3) {
                $corte=3; } else {
                $corte=$resto;
            }
            $offset=(($i*3)-3)+$corte;
            $offset=$offset*(-1);

            // la siguiente seccion es una adaptacion de la contribucion de cofyman y JavierB

            $num=implode("",array_slice ($digitos,$offset,$corte));
            $resultado[$i] = "";
            $cen = (int) ($num / 100);              //Cifra de las centenas
            $doble = $num - ($cen*100);             //Cifras de las decenas y unidades
            $dec = (int)($num / 10) - ($cen*10);    //Cifra de las decenas
            $uni = $num - ($dec*10) - ($cen*100);   //Cifra de las unidades
            if ($cen > 0) {
                if ($num == 100) $resultado[$i] = "cien";
                else $resultado[$i] = $centena[$cen-1].' ';
            }//end if
            if ($doble>0) {
                if ($doble == 20) {
                    $resultado[$i] .= " veinte";
                }elseif (($doble < 16) and ($doble>9)) {
                    $resultado[$i] .= $decenas[$doble-10];
                }else {
                    if(isset($decena[$dec - 1])) {
                        $resultado[$i] .= ' ' . $decena[$dec - 1];
                    }
                }//end if
                if ($dec>2 and $uni<>0) $resultado[$i] .=' y ';
                if (($uni>0) and ($doble>15) or ($dec==0)) {
                    if ($i==1 && $uni == 1) $resultado[$i].="uno";
                    elseif ($i==2 && $num == 1) $resultado[$i].="";
                    else $resultado[$i].=$unidad[$uni-1];
                }
            }

            // Le agregamos la terminacion del grupo
            switch ($i) {
                case 2:
                    $resultado[$i].= ($resultado[$i]=="") ? "" : " mil ";
                    break;
                case 3:
                    $resultado[$i].= ($num==1) ? " millón " : " millones ";
                    break;
            }
            $resto-=$corte;
        }

        // Sacamos el resultado (primero invertimos el array)
        $resultado_inv= array_reverse($resultado, TRUE);
        $final="";
        foreach ($resultado_inv as $parte){
            $final.=$parte;
        }
        return $final;
    }

}

?>