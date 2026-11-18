<?php /* Formulario de acceso. El controlador procesa las credenciales. */ ?>
<section class="auth-shell app-card">
    <h1>Bienvenido</h1>
    <p class="subtitle">Ingresa para consultar tus pólizas y siniestros.</p>
    <form action="<?php echo urlsite ?>?page=logInAuth" method="post">
        <input type="hidden" name="csrf_token" value="<?php echo Seguridad::escapar(Seguridad::token()); ?>">
        <div class="form-group-app">
            <label for="login-alias">Nombre de usuario</label>
            <input id="login-alias" type="text" name="txtalias" autocomplete="username" value="<?php echo Seguridad::escapar($_SESSION['alias_login'] ?? ''); ?>" required>
        </div>
        <div class="form-group-app">
            <label for="login-password">Contraseña</label>
            <input id="login-password" type="password" name="txtpassword" autocomplete="current-password" required>
        </div>
        <input class="btn btn-block" type="submit" value="Iniciar sesión" name="btnLogIn">
    </form>
    <p class="auth-footer">¿Aún no tienes cuenta? <a href="<?php echo urlsite ?>?page=sigIn">Regístrate</a></p>
</section>
