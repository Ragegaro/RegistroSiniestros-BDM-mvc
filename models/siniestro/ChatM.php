<?php
require_once __DIR__ . '/../Conexion.php';
require_once __DIR__ . '/../../lib/ChatReglas.php';

/** Conversación por expediente. No depende de procedimientos almacenados nuevos. */
class ChatM {
    private $db;

    public function __construct() {
        $this->db = new Conexion();
    }

    /**
     * Se ejecuta DENTRO de una transacción en cada lectura y envío.
     * Bloquea primero el mismo siniestro que bloquea asignarAjustador(): una
     * reasignación no puede intercalarse entre comprobar acceso y leer/escribir.
     * El rol y el estado se consultan de nuevo; no se confía en rol_slug de sesión.
     */
    private function contexto(PDO $pdo, $idSiniestro, $idUsuario) {
        $stmt = $pdo->prepare('SELECT id, poliza_id, ajustador_id FROM siniestro WHERE id = ? FOR UPDATE');
        $stmt->execute([(int) $idSiniestro]);
        $caso = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        if (!$caso) throw new ChatError('No tienes acceso a este chat o el siniestro ya no está disponible.', 403);

        $stmt = $pdo->prepare('SELECT usuario_id FROM poliza WHERE id = ? FOR UPDATE');
        $stmt->execute([(int) $caso['poliza_id']]);
        $caso['Usuario'] = (int) $stmt->fetchColumn();
        $stmt->closeCursor();

        $stmt = $pdo->prepare('SELECT u.id, u.estatus, r.slug AS rol FROM usuario u
            INNER JOIN rol r ON r.id = u.rol_id WHERE u.id = ? FOR UPDATE');
        $stmt->execute([(int) $idUsuario]);
        $actor = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        ChatReglas::autorizar($caso, $actor);

        $stmt = $pdo->prepare("SELECT COALESCE(NULLIF(alias, ''), nombre) FROM usuario WHERE id = ?");
        $stmt->execute([$caso['Usuario']]);
        $asegurado = (string) $stmt->fetchColumn();
        $stmt->closeCursor();
        $stmt->execute([(int) $caso['ajustador_id']]);
        $ajustador = $stmt->fetchColumn();
        $stmt->closeCursor();
        return ['asegurado' => $asegurado, 'ajustador' => $ajustador === false ? null : (string) $ajustador];
    }

    /** Últimos 50, anteriores a un ID, o nuevos después de un ID. Siempre por caso. */
    public function leer($idSiniestro, $idUsuario, $antes = 0, $despues = 0) {
        $pdo = $this->db->conectar();
        try {
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->beginTransaction();
            $participantes = $this->contexto($pdo, $idSiniestro, $idUsuario);
            $sql = "SELECT c.id, c.usuario_id, c.mensaje, c.fechamensaje AS fecha,
                COALESCE(NULLIF(u.alias, ''), u.nombre) AS autor, r.slug AS rol
                FROM chat c INNER JOIN usuario u ON u.id = c.usuario_id
                INNER JOIN rol r ON r.id = u.rol_id WHERE c.siniestro_id = ?";
            $parametros = [(int) $idSiniestro];
            if ($antes > 0) { $sql .= ' AND c.id < ?'; $parametros[] = (int) $antes; }
            if ($despues > 0) { $sql .= ' AND c.id > ?'; $parametros[] = (int) $despues; }
            // Los límites son constantes del servidor, nunca texto recibido por GET.
            $sql .= ' ORDER BY c.id ' . ($despues > 0 ? 'ASC' : 'DESC') . ' LIMIT ' . (ChatReglas::POR_PAGINA + 1);
            $stmt = $pdo->prepare($sql);
            $stmt->execute($parametros);
            $mensajes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            $hayMas = count($mensajes) > ChatReglas::POR_PAGINA;
            if ($hayMas) array_pop($mensajes);
            if ($despues === 0) $mensajes = array_reverse($mensajes);
            foreach ($mensajes as &$mensaje) {
                $mensaje['id'] = (int) $mensaje['id'];
                $mensaje['usuario_id'] = (int) $mensaje['usuario_id'];
                $mensaje['rol_nombre'] = ChatReglas::nombreRol($mensaje['rol']);
                unset($mensaje['rol']);
            }
            unset($mensaje);
            $pdo->commit();
            return ['usuario_id' => (int) $idUsuario, 'mensajes' => $mensajes, 'participantes' => $participantes,
                'hay_anteriores' => $despues === 0 && $hayMas, 'hay_mas' => $despues > 0 && $hayMas];
        } catch (Throwable $error) {
            Conexion::revertir($pdo);
            throw $error;
        } finally {
            $this->db->desconectar();
        }
    }

    /** Emisor de sesión; hora de MySQL. No admite autor/rol/fecha enviados por POST. */
    public function enviar($idSiniestro, $idUsuario, $texto) {
        $texto = ChatReglas::mensaje($texto);
        $pdo = $this->db->conectar();
        try {
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            $pdo->beginTransaction();
            $this->contexto($pdo, $idSiniestro, $idUsuario);
            $stmt = $pdo->prepare('INSERT INTO chat (mensaje, siniestro_id, usuario_id, mensaje_padre)
                VALUES (?, ?, ?, NULL)');
            $stmt->execute([$texto, (int) $idSiniestro, (int) $idUsuario]);
            $id = (int) $pdo->lastInsertId();
            $pdo->commit();
            return $id;
        } catch (Throwable $error) {
            Conexion::revertir($pdo);
            throw $error;
        } finally {
            $this->db->desconectar();
        }
    }
}
