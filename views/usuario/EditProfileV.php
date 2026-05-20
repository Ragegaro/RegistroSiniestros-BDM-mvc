<?php require "views/layouts/header.php"; 
    require "views/layouts/navbar.php";  
    require "controllers/LoginC.php";
    require_once "controllers/UsuarioC.php";
    $usuario = UsuarioC::mostrarPerfil();
 

?>

<div class="bg bg-dark">
    <div class="container py-4"> <div class="row justify-content-center mb-4">
            <div class="col-auto text-center">
                <?php if (!empty($usuario->foto_perfil)): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($usuario->foto_perfil); ?>" alt="Perfil" style="width: 150px; height: 150px; border-radius: 50%; border: 3px solid #df3131; object-fit: cover;">
                <?php else: ?>
                    <img src="<?php echo urlsite ?>assets/img/default-avatar.png" alt="Perfil" style="width: 150px; height: 150px; border-radius: 50%; border: 3px solid #ccc; object-fit: cover;">
                <?php endif; ?>
                <h3 class="text-white mt-2"><?php echo htmlspecialchars($usuario->alias); ?></h3>
            </div>
        </div>

        <div class="row justify-content-center">   
            <form action="<?php echo urlsite?>?page=editarPerfil" method="post" enctype="multipart/form-data" class="col-md-6 text-white">
                
                <label for="">Correo electronico actual</label>
                <input type="text" class="form-control mb-2" name="txtEmailActual" value="<?php echo htmlspecialchars($usuario->email); ?>" readonly>

                <label for="">Nuevo correo electronico</label>
                <input type="text" class="form-control mb-2" name="txtNuevoEmail" placeholder="Nuevo correo electronico">

                <label for="">Nombre de usuario</label>
                <input type="text" class="form-control mb-2" name="txtAlias" value="<?php echo htmlspecialchars($usuario->alias); ?>"> 

                <label for="">Antigua contraseña</label>
                <input type="password" class="form-control mb-2" name="txtPasswordAntigua" placeholder="Contraseña">
                
                <label for="">Nueva contraseña</label>
                <input type="password" class="form-control mb-3" name="txtPasswordNueva" placeholder="Nueva Contraseña">
                
                <label>Actualizar Foto de perfil</label><br>
                <input type="file" name="foto_perfil" accept="image/*" class="form-control-file mb-4">

                <input type="submit" class="btn btn-primary btn-block" value="Guardar Cambios" name="btnEdit">

            </form>
        </div>
    </div>
</div>