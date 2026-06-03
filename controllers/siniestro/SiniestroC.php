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

        public function verDetalledSiniestro() {
            if (!isset($_SESSION['id_usuario'])) {
                header("Location: index.php");
                exit;
            }

            $id_siniestro = $_GET['id'];
            $siniestro = $this->modelo->obtenerPorID($id_siniestro);
            $siniestro['multimedia'] = $this->modelo->obtenerMultimediaPorSiniestro($id_siniestro);
            $historial= $this->modelo->getHistorialEstatus($id_siniestro);

            if (!$siniestro) {
                echo "Siniestro no encontrado.";
                exit;
            }

           
            require "views/layouts/header.php";
            require "views/layouts/navbar.php";
            require "views/siniestros/detalle.php";
            require "views/layouts/footer.php";
                
        }



        /*
        public function actualizarEstatus() {
            if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['id_siniestro'])) {
                $idSiniestro = $_POST['id_siniestro'];
                $nuevoEstatus = $_POST['estatus'];
                $comentario = $_POST['comentario'];
                $idUsuario = $_SESSION['id_usuario']; // Quien hace el cambio

                $this->modelo->cambiarEstatus($idSiniestro, $nuevoEstatus, $comentario, $idUsuario);

                header("Location: index.php?controller=Siniestro&action=verDetalledSiniestro&id=" . $idSiniestro . "&success=estatus");
                exit;
            }
        }
        */
/*   INCOMPLETO
    public function guardarEvaluacionAjustador() {
            if ($_SERVER['REQUEST_METHOD'] == 'POST') {
                $idSiniestro = $_POST['id_siniestro'];
                
                // ... Aquí va tu lógica de actualizar estatus, guardar diagnóstico, etc ...

                // Y simplemente reutilizas tu motor de subida para las evidencias del ajustador
                if (isset($_FILES['evidencias']) && !empty($_FILES['evidencias']['name'][0])) {
                    $this->procesarArchivosMultimedia($idSiniestro, $_FILES['evidencias']);
                }

                header("Location: index.php?controller=Siniestro&action=verDetalledSiniestro&id=" . $idSiniestro);
                exit;
            }
        }
*/


        

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