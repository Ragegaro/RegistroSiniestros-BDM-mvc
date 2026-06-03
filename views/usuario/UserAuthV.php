<div>
    <div>
        <h2>Bienvenido <?php echo $_SESSION['alias']?></h2>
        <p>¿En qué te podemos ayudar hoy?</p>
    </div>

    <div>
        <button  onclick="window.location.href='<?php echo urlsite ?>?page=report'">
        Reportar un siniestro
        </button>
    </div>
    
    <div>
        <button  onclick="window.location.href='<?php echo urlsite ?>?page=polizas'">
        Ver Polizas
        </button>
    
        <button  onclick="window.location.href='<?php echo urlsite ?>?page=misSiniestros'">
        Mis siniestros
        </button>
    </div>
</div>
