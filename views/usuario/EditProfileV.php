<?php /* Edición del perfil de la sesión actual. */ ?>
<section class="profile-layout">
    <aside class="app-card profile-summary">
        <?php if (!empty($usuario->foto_perfil)): ?>
            <img class="profile-avatar" src="data:image/jpeg;base64,<?php echo base64_encode($usuario->foto_perfil); ?>" alt="Foto de perfil">
        <?php else: ?>
            <div class="profile-avatar" aria-label="Sin foto de perfil"></div>
        <?php endif; ?>
        <h2><?php echo htmlspecialchars($usuario->alias); ?></h2>
        <p class="helper">Perfil de usuario</p>
    </aside>
    <div class="app-card">
        <div class="page-heading"><h1>Editar perfil</h1><p>Actualiza solo los datos que necesites cambiar.</p></div>
        <form action="<?php echo urlsite ?>?page=editarPerfil" method="post" enctype="multipart/form-data">
            <div class="form-group-app"><label for="email-actual">Correo actual</label><input id="email-actual" type="email" name="txtEmailActual" value="<?php echo htmlspecialchars($usuario->email); ?>" readonly></div>
            <div class="form-group-app"><label for="nuevo-email">Nuevo correo</label><input id="nuevo-email" type="email" name="txtNuevoEmail" placeholder="nombre@correo.com"></div>
            <div class="form-group-app"><label for="perfil-alias">Nombre de usuario</label><input id="perfil-alias" type="text" name="txtAlias" value="<?php echo htmlspecialchars($usuario->alias); ?>" required></div>
            <div class="form-grid">
                <div class="form-group-app"><label for="pass-antigua">Contraseña actual</label><input id="pass-antigua" type="password" name="txtPasswordAntigua" autocomplete="current-password"></div>
                <div class="form-group-app"><label for="pass-nueva">Nueva contraseña</label><input id="pass-nueva" type="password" name="txtPasswordNueva" autocomplete="new-password"></div>
                <div class="form-group-app full"><label for="nueva-foto">Actualizar foto</label><input id="nueva-foto" type="file" name="foto_perfil" accept="image/*"></div>
            </div>
            <div class="form-actions"><input type="submit" value="Guardar cambios" name="btnEdit"></div>
        </form>
    </div>
</section>
