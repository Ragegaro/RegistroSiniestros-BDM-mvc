<?php
require_once __DIR__ . '/../../models/poliza/PolizaM.php';
require_once __DIR__ . '/../../lib/Seguridad.php';

class PolizaC {
    private $modelo;

    public function __construct() {
        $this->modelo = new PolizaM();
    }

    private function exigirAsegurado() {
        if (empty($_SESSION['id_usuario'])) {
            Avisos::ir('logIn', 'error', 'Inicia sesión para registrar tu póliza.');
        }
        if (($_SESSION['rol_slug'] ?? '') !== 'asegurado') {
            Avisos::paginaError('Solo los asegurados pueden registrar pólizas.', 403);
        }
    }

    public function listarPolizas() {
        if (empty($_SESSION['id_usuario'])) {
            Avisos::ir('logIn');
        }

        $misPolizas = $this->modelo->verPolizas((int) $_SESSION['id_usuario']);
        $misPolizas = is_array($misPolizas) ? $misPolizas : [];

        require 'views/layouts/header.php';
        require 'views/layouts/navbar.php';
        require 'views/polizas/polizasV.php';
        require 'views/layouts/footer.php';
    }

    public function mostrarFormularioAgregar() {
        $this->exigirAsegurado();
        $aseguradoras = $this->modelo->listarAseguradoras();
        $vehiculos = $this->modelo->listarVehiculosPropios((int) $_SESSION['id_usuario']);
        $anterior = $_SESSION['poliza_anterior'] ?? [];
        unset($_SESSION['poliza_anterior']);

        require 'views/layouts/header.php';
        require 'views/layouts/navbar.php';
        require 'views/polizas/agregarPolizaV.php';
        require 'views/layouts/footer.php';
    }

    public function guardarPoliza() {
        $this->exigirAsegurado();
        Seguridad::exigirPost();

        $datos = [
            'numero_poliza' => Seguridad::texto($_POST, 'numero_poliza'),
            'aseguradora_id' => Seguridad::texto($_POST, 'aseguradora_id'),
            'aseguradora_nueva' => Seguridad::texto($_POST, 'aseguradora_nueva'),
            'vehiculo_modo' => Seguridad::texto($_POST, 'vehiculo_modo'),
            'vehiculo_id' => Seguridad::entero($_POST, 'vehiculo_id'),
            'marca' => Seguridad::texto($_POST, 'marca'),
            'modelo' => Seguridad::texto($_POST, 'modelo'),
            'color' => Seguridad::texto($_POST, 'color'),
            'placas' => strtoupper(Seguridad::texto($_POST, 'placas')),
            'serie' => strtoupper(Seguridad::texto($_POST, 'serie'))
        ];

        try {
            if ($datos['numero_poliza'] === '' || strlen($datos['numero_poliza']) > 50) {
                throw new RuntimeException('Escribe un número de póliza de máximo 50 caracteres.');
            }
            if ($datos['aseguradora_id'] === 'nueva') {
                if ($datos['aseguradora_nueva'] === '' || strlen($datos['aseguradora_nueva']) > 100) {
                    throw new RuntimeException('Escribe el nombre de la aseguradora (máximo 100 caracteres).');
                }
            } elseif (!ctype_digit($datos['aseguradora_id']) || (int) $datos['aseguradora_id'] <= 0) {
                throw new RuntimeException('Selecciona una aseguradora.');
            }

            if ($datos['vehiculo_modo'] === 'existente') {
                if ($datos['vehiculo_id'] <= 0) {
                    throw new RuntimeException('Selecciona uno de tus vehículos registrados.');
                }
            } elseif ($datos['vehiculo_modo'] === 'nuevo') {
                if ($datos['marca'] === '' || strlen($datos['marca']) > 30 || $datos['modelo'] === '' || strlen($datos['modelo']) > 50 || strlen($datos['color']) > 30) {
                    throw new RuntimeException('Revisa la marca (30 caracteres), el modelo (50) y el color (30).');
                }
                if (!preg_match('/^[A-Z0-9\-]{3,10}$/D', $datos['placas'])) {
                    throw new RuntimeException('Las placas deben tener de 3 a 10 letras, números o guiones.');
                }
                if (!preg_match('/^[A-Z0-9\-]{5,50}$/D', $datos['serie'])) {
                    throw new RuntimeException('El número de serie debe tener de 5 a 50 letras, números o guiones.');
                }
            } else {
                throw new RuntimeException('Selecciona cómo registrar tu vehículo.');
            }

            $this->modelo->registrarPolizaConVehiculo($datos, (int) $_SESSION['id_usuario']);
            unset($_SESSION['poliza_anterior']);
            Avisos::ir('polizas', 'success', '¡Póliza registrada! Ya puedes seleccionarla al reportar un siniestro.');
        } catch (Throwable $error) {
            $_SESSION['poliza_anterior'] = $datos;
            Avisos::ir('agregarPoliza', 'error', Avisos::registrarError($error, 'registrar la póliza'));
        }
    }
}
