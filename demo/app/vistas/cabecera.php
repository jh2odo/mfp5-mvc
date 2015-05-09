<div id="cabecera">
	<div id="logo">
		<h1>Demo</h1>
		<p>Esto es una demo sencilla de estructura de plantillas de vista.</p>
	</div>
	<div id="menu">
		<ul>
			<li><a href="inicio/" title="Inicio">Inicio</a></li>
			<li><a href="contacto/" title="Contacto">Contacto</a></li>
		</ul>
	</div>
	<div id="fecha_actual"><?php echo utf8_encode(strftime("%A, %d %B %Y")); ?></div>
</div>
