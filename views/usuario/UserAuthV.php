<div class="userAuth">
    <div class="texto">
        <h2>Bienvenido <?php echo isset($usuario->alias) ? $usuario->alias : $_SESSION['alias']; ?></h2>
        <p>¿En qué te podemos ayudar hoy?</p>
    </div>
    <div class="btn_grande">
        <button  onclick="window.location.href='<?php echo urlsite ?>?page=report'">
        Reportar un siniestro
        </button>
    </div>
    <div class="btn_sec">
        <button  onclick="window.location.href='<?php echo urlsite ?>?page=polizas'">
        Ver Polizas
        </button>
    
        <button  onclick="window.location.href='<?php echo urlsite ?>?page=misSiniestros'">
        Mis siniestros
        </button>
    </div>
</div>
