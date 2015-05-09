<body>

<?php if(Cargador::cargar("Configuracion")->GOOGLE_ANALYTIC == TRUE){ ?>
    <script src="http://www.google-analytics.com/ga.js" type="text/javascript"></script>

    <noscript>
        <div><?php echo Cargador::cargar("Configuracion")->DOMINIO; ?></div>
    </noscript>
    <script type="text/javascript">
        //<![CDATA[
        var _gaq = _gaq || [];
        _gaq.push(['_setAccount', '<?php echo Cargador::cargar("Configuracion")->GOOGLE_ANALYTIC_CODE; ?>']);
        _gaq.push(['_trackPageview']);
        //]]>
    </script>
<?php } ?>

<?php echo $cabecera; ?>

<?php echo $pagina; ?>

<?php echo $pie; ?>

<?php if(Cargador::cargar("Configuracion")->GOOGLE_ANALYTIC == TRUE){ ?>
    <noscript>
        <div>
            Estadísticas - <a href="http://www.google.es/analytics/" title="Google Analytics" rel="external">Analytics</a>
        </div>
    </noscript>
<?php } ?>
</body>
</html>