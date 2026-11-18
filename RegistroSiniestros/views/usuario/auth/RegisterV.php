<?php /* Alta de asegurados. Los nombres de los campos corresponden a UsuarioC. */ ?>
<section class="app-card">
    <div class="page-heading"><h1>Crear cuenta</h1><p>Completa tus datos para comenzar.</p></div>
    <form action="<?php echo urlsite ?>?page=sigInAuth" method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?php echo Seguridad::escapar(Seguridad::token()); ?>">
        <div class="form-grid">
            <div class="form-group-app"><label for="nombres">Nombre(s)</label><input id="nombres" type="text" name="txtNombres" required></div>
            <div class="form-group-app"><label for="apellido-p">Apellido paterno</label><input id="apellido-p" type="text" name="txtApellidoP" required></div>
            <div class="form-group-app"><label for="apellido-m">Apellido materno</label><input id="apellido-m" type="text" name="txtApellidoM"></div>
            <div class="form-group-app"><label for="Fecha_nacimiento">Fecha de nacimiento</label><input type="date" name="dateNacimiento" id="Fecha_nacimiento" required></div>
            <fieldset class="form-group-app full">
                <legend class="field-label">Género</legend>
                <div class="radio-group">
                    <span class="radio-option"><input type="radio" id="masculino" name="genero" value="masculino" required><label for="masculino">Masculino</label></span>
                    <span class="radio-option"><input type="radio" id="femenino" name="genero" value="femenino"><label for="femenino">Femenino</label></span>
                    <span class="radio-option"><input type="radio" id="no_binario" name="genero" value="no_binario"><label for="no_binario">No binario</label></span>
                </div>
            </fieldset>
            <div class="form-group-app"><label for="email">Correo electrónico</label><input id="email" type="email" name="txtEmail" autocomplete="email" required></div>
            <div class="form-group-app"><label for="alias">Nombre de usuario</label><input id="alias" type="text" name="txtAlias" autocomplete="username" required></div>
            <div class="form-group-app"><label for="password">Contraseña</label><input id="password" type="password" name="txtPassword" autocomplete="new-password" required></div>
            <div class="form-group-app"><label for="password-confirm">Confirmar contraseña</label><input id="password-confirm" type="password" name="txtPassword_Confirm" autocomplete="new-password" required></div>
            <div class="form-group-app full"><label for="foto-perfil">Foto de perfil <span class="helper">(opcional)</span></label><input id="foto-perfil" type="file" name="foto_perfil" accept="image/*"></div>
        </div>
        <div class="form-actions"><input type="submit" value="Crear cuenta" name="btnSigIn"><a class="btn btn-outline-app" href="<?php echo urlsite ?>?page=logIn">Ya tengo una cuenta</a></div>
    </form>
</section>
