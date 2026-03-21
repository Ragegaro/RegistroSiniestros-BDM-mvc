<?php  require "views/layouts/header.php";?>
<div class="bg bg-dark">

    <div class="container">
        <div class="row justify-content-center">   
            <form action="<?php echo urlsite?>?page=login" method="post">
                <label for="">Nombre(s)</label>
                <input type="text" class="form-control" name="txtalias" placeholder="Nombres">

                <label for="">Apellido</label>
                <input type="text" class="form-control" name="txtalias" placeholder="Apellido">

                <label for="">Fecha de Nacimiento</label>
                <input type="date" class="form-control" name="txtalias" id="Fecha_nacimiento" >

                <label for="">Genero</label>
                <select class ="form-control" name="estatus" required>
                    <option value="Hombre">Seleccione</option>
                    <option value="Hombre">Masculino</option>
                    <option value="Mujer">Femenino</option>
                    <option value="Rechazado">No binario</option>
                </select>

                <label for="">Correo electronico</label>
                <input type="text" class="form-control" name="txtalias" placeholder="Correo electronico">

                <label for="">Nombre de usuario</label>
                <input type="text" class="form-control" name="txtalias" placeholder="Nombre de usuario">
                
                <label for="">Contraseña</label>
                <input type="password" class="form-control" name="txtpassword" placeholder="Contraseña">
                
                <label for="">Confirmar contraseña</label>
                <input type="password" class="form-control" name="txtpassword" placeholder="Confirmar contraseña">
                
                <label>Foto de perfil</label>
                <input type="file" name="fotos[]" multiple accept="image/*">

                <input type ="submit" class="btn btn-primary" value="Registrarse" name="btnSigIn">

            </form>
        
        
        </div>

    </div>
</div>


