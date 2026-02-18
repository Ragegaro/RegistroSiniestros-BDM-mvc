<?php
$db_host ='localhost';
$db_name ='bdm_aseguradora';
$db_user='root';
$db_pass='';

$db_opttions arrya(
    PDO::ATR_ERRMODE             => PDO::ERRMODE_EXCEPTION, //manejo de errores con excepciones
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FECTH_ASSOC,       // modo de obtencion resultado
    PDO::ATTR_EMULATE-PREPARES   => false,                  // Emulacion de consultas preparadas

);

try{
    $pdo=new PDO ("mysql:host=$db_host;dbname=$db_name;charset=utf-8", $db_user,$db_pass,$db_options)
}
catch (PDOException $e){
    echo "Error de conexción a la base de datos: ".$e->getMessage();
    exit;
}

?>