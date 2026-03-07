<nav class="Navbar">

    <?php if($page == 'lista') { ?>
        <a href="javascript:history.back()"> Regresar</a>
    <?php } else { ?>
        <a href="<?php echo urlsite ?>?page=lista">Mis siniestros</a>
    <?php } ?>

    <a href="<?php echo urlsite ?>?page=ajustes">Ajustes</a>    

    <a href="<?php echo urlsite ?>?page=logout">Cerrar Sesión</a>

</nav>