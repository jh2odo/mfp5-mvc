<div id="contenido">
    <div class="bloque">
        <h2 class="titulo">Bienvenido</h2>
        <div style="padding-bottom: 20px;">
            <p>Controlador "Inicio" y acción "index".</p>
        </div>
    </div>
    <div class="bloque">
        <h2 class="titulo">Lista de Tareas (base de datos)</h2>
        <ul>
        <?php
        foreach ($tareas as $tarea){
            echo'<li>'.$tarea["id"].' : '.$tarea["titulo"].'</li>';
        }
        ?>
        </ul>
    </div>
</div>