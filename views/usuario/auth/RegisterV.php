<div>
    <div>   
        <form action="<?php echo urlsite?>?page=sigInAuth" method="post" enctype="multipart/form-data">
            <label for="">Nombre(s)</label>
            <input type="text" class="form-control" name="txtNombres">

            <label for="">Apellido Paterno</label>
            <input type="text" class="form-control" name="txtApellidoP">

            <label for="">Apellido Materno</label>
            <input type="text" class="form-control" name="txtApellidoM">

            <label for="">Fecha de Nacimiento</label>
            <input type="date" class="form-control" name="dateNacimiento" id="Fecha_nacimiento" >

            <label for="">Genero</label> <br>
            <div>
                <input type="radio" id="masculino" name="genero" value="masculino">
                <label for="masculino">Masculino</label>

                <input type="radio" id="femenino" name="genero" value="femenino">
                <label for="femenino">Femenino</label>

                <input type="radio" id="no_binario" name="genero" value="no_binario">
                <label for="no_binario">No binario</label>
            </div>

            <label for="">Correo electronico</label>
            <input type="text" class="form-control" name="txtEmail">

            <label for="">Nombre de usuario</label>
            <input type="text" class="form-control" name="txtAlias">
                
            <label for="">Contraseña</label>
            <input type="password" class="form-control" name="txtPassword">
               
            <label for="">Confirmar contraseña</label>
            <input type="password" class="form-control" name="txtPassword_Confirm">
                
            <label>Foto de perfil</label>
            <input type="file" name="foto_perfil" accept="image/*">
                
            <br>
            <input type ="submit" class="btn btn-primary" value="Registrarse" name="btnSigIn">

        </form>
            
    </div>
</div>
