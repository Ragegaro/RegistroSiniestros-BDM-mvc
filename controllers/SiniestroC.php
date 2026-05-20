<?php
    require_once "models/SiniestroM.php";
    //require_once "models/PolizaM.php";
    //require_once "controllers/PolizaC.php";

    class SiniestroC {
        private $modelo;

        public function __construct() {
            $this->modelo = new SiniestroM();
        }

        public function agregarSiniestro() {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                
                $resultado = $this-> modelo->reportar(
                    $_POST['fecha'],    
                    $_POST['hora'],
                    $_POST['ubicacion'],
                    $_POST['descripcion'],
                    $_POST['SelectPoliza']         
                );

                    if ($resultado) {
                            header("Location: index.php?page=lista&success=1");
                            exit;
                    } else {
                        echo "Error al insertar en la base de datos";
                        //header("Location: index.php?page=sigIn&error=1");
                        exit;
                    }
            

            }
        }
public static function listarSiniestros() {
    
    $modelo = new SiniestroM();
    $rol = $_SESSION['rol'] ?? '';
    $idUsuario = $_SESSION['id_usuario'];

    
    if ($rol === 'ajustador' || $rol === 'Supervisor') {
        return $modelo->listarTodosLosSiniestros(); 
    } 
    
    else {
        return $modelo->listarTodosLosSiniestros();
    }
}

        public function verDetalledSiniestro() {
            $id_sinestro = isset ($_GET['id']) ? $_GET['id']:null;

            //if ($id_sinestro===)
            $siniestro = $this->modelo->obtenerPorID($id_sinestro);
            return $siniestro;
            
        }
    }
    
/*public function verDetalledSiniestro() {
    $idSiniestro = isset($_GET['id']) ? $_GET['id'] : 0;
    
    // 1. Traemos los datos del siniestro
    $siniestro = $this->modelo->obtenerPorID($idSiniestro); 
    
    if ($siniestro) {
        // 2. Si el siniestro existe, le pegamos la multimedia adentro del array/objeto
        // Convertimos el resultado a array si es que manejas arrays en la vista
        $siniestroArray = (array)$siniestro;
        $siniestroArray['multimedia'] = $this->modelo->obtenerMultimediaPorSiniestro($idSiniestro);
        
        return $siniestroArray;
    }
    
    return null;
}*/

public function procesarEvaluacionYMultimedia() {
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['btnEvaluacion'])) {
        
        $idSiniestro = $_POST['id_siniestro'];
        $diagnostico = $_POST['diagnostico'];
        $monto = $_POST['monto'];
        $estatus = $_POST['estatus'];

        require_once "models/SiniestroM.php";
        $modeloSiniestro = new SiniestroM();

        // 1. Actualizamos los datos básicos del siniestro primero
        $modeloSiniestro->guardarDatosEvaluacion($idSiniestro, $diagnostico, $monto, $estatus);

        // 2. Procesamos la ráfaga de archivos múltiples
        if (isset($_FILES['evidencias']) && !empty($_FILES['evidencias']['name'][0])) {
            $archivos = $_FILES['evidencias'];
            $carpetaDestino = "uploads/multimedia/";

            if (!is_dir($carpetaDestino)) {
                mkdir($carpetaDestino, 0777, true);
            }

            // Ciclo para procesar cada archivo subido uno por uno
            foreach ($archivos['name'] as $index => $nombreOriginal) {
                if ($archivos['error'][$index] === UPLOAD_ERR_OK) {
                    
                    $tmpName = $archivos['tmp_name'][$index];
                    $type = $archivos['type'][$index];
                    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
                    
                    // Definir si es Foto o Video para tu ENUM de la BD
                    $tipoEnum = (strpos($type, 'video') !== false) ? 'Video' : 'Foto';
                    
                    // Nombre único único para evitar colisiones
                    $nuevoNombre = "evidencia_" . uniqid() . "_" . $index . "." . $extension;
                    $rutaFinal = $carpetaDestino . $nuevoNombre;

                    // Mover al servidor físico
                    if (move_uploaded_file($tmpName, $rutaFinal)) {
                        // Guardar registro de la ruta en la tabla multimedia
                        $modeloSiniestro->insertarMultimedia($tipoEnum, $rutaFinal, $idSiniestro);
                    }
                }
            }
        }

        // Redireccionar al detalle del siniestro con éxito
        header("Location: index.php?page=detalle&id=" . $idSiniestro . "&success=evaluacion");
        exit;
    }
}
?>