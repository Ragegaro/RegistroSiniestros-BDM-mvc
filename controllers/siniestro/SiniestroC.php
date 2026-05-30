<?php
    require_once "models/siniestro/SiniestroM.php";

    class SiniestroC {
        private $modelo;

        public function __construct() {
            $this->modelo = new SiniestroM();
        }

        public function mostrarFormulario() {
            if(!isset($_SESSION['id_usuario'])) {
                header("Location: index.php");
                exit;
            }

            require "views/layouts/header.php";
            require "views/layouts/navbar.php";
            require "views/siniestros/reporteV.php";   
            require "views/layouts/footer.php";
            
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
        
        
        public function listarSiniestros() {
            if (!isset($_SESSION['id_usuario'])) {
                header("Location: index.php");
                exit;
            }
            $idUsuario = $_SESSION['id_usuario'];
            $rolUsuario = $_SESSION['rol_slug'];
    
            require "views/layouts/header.php";
            require "views/layouts/navbar.php";
            if  ($rolUsuario === 'supervisor') {
                $misSiniestros = $this->modelo->listarTodosLosSiniestros();
            
            } else {
                
                $misSiniestros = $this->modelo->listarSiniestros($idUsuario);
              
            }
            $misSiniestros = is_array($misSiniestros) ? $misSiniestros : [];
            /*switch ($_SESSION['rol_slug']){ 
                case 'supervisor':
                    //require "views/siniestros/listarSiniestrosSupervisorV.php";
                    $todoSiniestros = $this->modelo->listarTodosLosSiniestros($idUsuario); 
    
                break;  

                case 'ajustador':                    
                    //require "views/siniestros/listarSiniestrosAjustadorV.php";
                    $misSiniestros = $this->modelo->listarSiniestros($idUsuario);

                break;

                default:
                $misSiniestros = $this->modelo->listarSiniestros($idUsuario);
                    //return $this->modelo->listarSiniestros($_SESSION['id_usuario']);
                break;
            }*/
          
    require "views/siniestros/listarSiniestrosV.php";
    require "views/layouts/footer.php";
         
            
           

        }

        public function verDetalledSiniestroID() {
            $id_sinestro = isset ($_GET['id']) ? $_GET['id']:null;

            //if ($id_sinestro===)
            $siniestro = $this->modelo->obtenerPorID($id_sinestro);
            return $siniestro;
                
        }

     
     
     


     
    }
    
    /*
       public function editarSiniestroAjustador(){}
        public function editarSiniestroSupervisor(){}
        public function eliminarSiniestro(){}


    public function verDetalledSiniestro() {
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
    /*
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
    }*/
        
?>