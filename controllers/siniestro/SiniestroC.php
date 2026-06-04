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

            require_once "models/poliza/PolizaM.php";
            $modeloPoliza = new PolizaM();

            $misPolizas = $modeloPoliza->verPolizas($_SESSION['id_usuario']);
            $misPolizas = is_array($misPolizas) ? $misPolizas : [];

            require "views/layouts/header.php";
            require "views/layouts/navbar.php";
            require "views/siniestros/reporteV.php";   
            require "views/layouts/footer.php";
            
        }

        public function agregarSiniestro() {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                
                $idSiniestro = $this-> modelo->reportar(
                    $_POST['fecha'],    
                    $_POST['hora'],
                    $_POST['ubicacion'],
                    $_POST['descripcion'],
                    $_POST['SelectPoliza']         
                );

                if ($idSiniestro) {
                    if (isset($_FILES['evidencias']) && !empty($_FILES['evidencias']['name'][0])) {
                        $this->procesarArchivosMultimedia($idSiniestro, $_FILES['evidencias']);
                    }                
                    header("Location: index.php?page=misSiniestros&success=1");
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

            require_once "models/siniestro/SiniestroM.php";
            $siniestroModel = new SiniestroM();
            $misSiniestros = $siniestroModel->obtenerSiniestrosPorRol($idUsuario, $rolUsuario);
            $misSiniestros = is_array($misSiniestros) ? $misSiniestros : [];
        
            require "views/layouts/header.php";
            require "views/layouts/navbar.php";
            require "views/siniestros/listarSiniestrosV.php";
            require "views/layouts/footer.php";
        }

        public function verDetalledSiniestro() {
            if (!isset($_SESSION['id_usuario'])) {
                header("Location: index.php");
                exit;
            }

            $id_siniestro = $_GET['id'];
            $siniestro = $this->modelo->obtenerPorID($id_siniestro);
            $siniestro['multimedia'] = $this->modelo->obtenerMultimediaPorSiniestro($id_siniestro);
            $historial= $this->modelo->getHistorialEstatus($id_siniestro);

            $listaEstatus = $this->modelo->obtenerTodosEstatus();

            if (!$siniestro) {
                echo "Siniestro no encontrado.";
                exit;
            }

           
            require "views/layouts/header.php";
            require "views/layouts/navbar.php";
            require "views/siniestros/detalle.php";
            require "views/layouts/footer.php";
                
        }



// Procesa el diagnóstico
        public function guardarEvaluacion() {
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['diagnostico'])) {
                $idSiniestro = $_POST['id_siniestro'];
                $diagnostico = trim($_POST['diagnostico']);
                
                $this->modelo->actualizarDiagnostico($idSiniestro, $diagnostico);
                header("Location: index.php?page=detalle&id=" . $idSiniestro . "&msg=EvaluacionGuardada");
                exit;
            }
        }

        // Procesa el cambio de estatus del Admin
        public function actualizarEstatus() {
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nuevo_estatus'])) {
                $idSiniestro = $_POST['id_siniestro'];
                $estatus = $_POST['nuevo_estatus'];
                
                $this->modelo->cambiarEstatus($idSiniestro, $estatus);
                header("Location: index.php?page=detalle&id=" . $idSiniestro . "&msg=EstatusActualizado");
                exit;
            }
        }


        

        private function procesarArchivosMultimedia($idSiniestro,$archivos){
            $carpetaBase ="uploads/siniestros/SIN-" . $idSiniestro . "/";

            for ($i = 0; $i < count($archivos['name']); $i++) {
                if ($archivos['error'][$i] === UPLOAD_ERR_OK) {
                    
                    $tmpName = $archivos['tmp_name'][$i];
                    $type = $archivos['type'][$i];
                    $nombreOriginal = $archivos['name'][$i];
                    $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
                    
                    if (strpos($type, 'video') !== false) {
                        if (!is_dir($carpetaBase)) {
                            mkdir($carpetaBase, 0777, true);
                        }
                        //CAMBIO DE NOMBRE PARA EVITAR SOBRESCRITURAS
                        $nuevoNombre = "video_" . uniqid() . "." . $extension;
                        $rutaFinal = $carpetaBase . $nuevoNombre;
                        
                        if (move_uploaded_file($tmpName, $rutaFinal)) {
                            $this->modelo->insertarMultimedia('Video', null, $rutaFinal, $idSiniestro);
                        }
                    } 
                    
                    else if (strpos($type, 'image') !== false) {
                        $contenidoBlob = file_get_contents($tmpName);
                        $this->modelo->insertarMultimedia('Foto', $contenidoBlob, null, $idSiniestro);
                    }
                }
            }
    
        }
    }





        
?>