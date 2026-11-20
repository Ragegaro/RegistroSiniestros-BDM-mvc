<?php

class Conexion{
    public $conexion;

    public function conectar(){
        try {
        $dsn = "mysql:" . DB_HOSTM . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            $opciones = array(
                PDO::ATTR_ERRMODE=> PDO::ERRMODE_EXCEPTION,
        //        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        
            );
            $this->conexion =new PDO($dsn,DB_USER,DB_PASS,$opciones);
            
         //  echo"exito";
            return $this->conexion;
        }
        catch(PDOException $e){
            // El controlador o el manejador global muestra un aviso seguro en la página.
            throw $e;
        }
    }

    public function desconectar(){
        $this->conexion=null;
    }

    /** Un fallo de rollback no debe ocultar el error original de MySQL. */
    public static function revertir($pdo) {
        try {
            if ($pdo && $pdo->inTransaction()) $pdo->rollBack();
        } catch (Throwable $error) {
            error_log('No se pudo confirmar rollback; código=' . $error->getCode());
        }
    }
}
