
<div>
    <h2>Bienvenido <?php echo $_SESSION['alias']?></h2>




<?php if ($_SESSION['rol_slug'] === 'supervisor' && isset($datosDashboard)): ?>
<div class="dashboard-admin" style="display: flex; gap: 15px; margin-bottom: 15px; justify-content: center; flex-wrap: wrap;">
    
    <div class="card" style="width: 220px; border: 1px solid #e0e0e0; padding: 10px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background-color: #fff;">
        <h4 style="font-size: 14px; margin: 0 0 8px 0; color: #333; border-bottom: 1px solid #eee; padding-bottom: 5px;"> Resumen de Casos</h4>
        <ul style="padding-left: 15px; font-size: 12px; color: #555; margin: 0;">
            <?php foreach ($datosDashboard['resumen_estatus'] as $est): ?>
                <li style="margin-bottom: 3px;"><strong><?php echo $est['Estatus_Siniestro']; ?>:</strong> <?php echo $est['Cantidad_Total']; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="card" style="width: 220px; border: 1px solid #e0e0e0; padding: 10px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background-color: #fff;">
        <h4 style="font-size: 14px; margin: 0 0 8px 0; color: #333; border-bottom: 1px solid #eee; padding-bottom: 5px;"> Carga Ajustadores</h4>
        <ul style="padding-left: 15px; font-size: 12px; color: #555; margin: 0;">
            <?php foreach ($datosDashboard['ajustadores'] as $ajus): ?>
                <li style="margin-bottom: 3px;"><?php echo $ajus['Nombre_Ajustador']; ?> (Casos: <?php echo $ajus['Siniestros_Asignados']; ?>)</li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="card" style="width: 220px; border: 1px solid #e0e0e0; padding: 10px; border-radius: 6px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); background-color: #fff;">
        <h4 style="font-size: 14px; margin: 0 0 8px 0; color: #333; border-bottom: 1px solid #eee; padding-bottom: 5px;"> Clientes Recientes</h4>
        <ul style="padding-left: 15px; font-size: 12px; color: #555; margin: 0;">
            <?php foreach ($datosDashboard['clientes'] as $cli): ?>
                <li style="margin-bottom: 3px;"><?php echo $cli['Nombre_Completo']; ?> (Pólizas: <?php echo $cli['Total_Polizas']; ?>)</li>
            <?php endforeach; ?>
        </ul>
    </div>
</div>
<hr style="margin-bottom: 20px;">
<?php endif; ?>
                

        

        
</div>
