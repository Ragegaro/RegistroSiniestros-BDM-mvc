<?php
require_once __DIR__ . '/../Conexion.php';
require_once __DIR__ . '/../Procedimientos.php';
require_once __DIR__ . '/../../lib/ChatReglas.php';

class ChatM {
    private $db;
    public function __construct() { $this->db=new Conexion(); }
    /** Transaccion y bloqueo en sp_chat_contexto, antes de cargar mensajes. */
    private function contexto(PDO $pdo,$caso,$actor) {
        try {
            $fila=Procedimientos::fila($pdo,'sp_chat_contexto',[(int)$caso,(int)$actor]);
            if (!$fila) throw new ChatError('No tienes acceso al chat.',403);
            return ['asegurado'=>$fila['asegurado']??'', 'ajustador'=>$fila['ajustador']??null];
        } catch(PDOException $e) {
            if ($e->getCode()==='45000') throw new ChatError('No tienes acceso al chat.',403);
            throw $e;
        }
    }
    public function leer($idSiniestro,$idUsuario,$antes=0,$despues=0) {
        $pdo=$this->db->conectar();
        try {
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
            $pdo->beginTransaction();
            $participantes=$this->contexto($pdo,$idSiniestro,$idUsuario);
            $mensajes=Procedimientos::filas($pdo,'sp_chat_leer',[
                (int)$idSiniestro,(int)$idUsuario,(int)$antes,(int)$despues,ChatReglas::POR_PAGINA+1
            ]);
            $hayMas=count($mensajes)>ChatReglas::POR_PAGINA;
            if ($hayMas) array_pop($mensajes);
            if ($despues===0) $mensajes=array_reverse($mensajes);
            foreach ($mensajes as &$m) {
                $m['id']=(int)$m['id'];
                $m['usuario_id']=(int)$m['usuario_id'];
                $m['rol_nombre']=ChatReglas::nombreRol($m['rol']);
                unset($m['rol']);
            }
            unset($m);
            $pdo->commit();
            return ['usuario_id'=>(int)$idUsuario,'mensajes'=>$mensajes,'participantes'=>$participantes,
               'hay_anteriores'=>$despues===0 && $hayMas,'hay_mas'=>$despues>0 && $hayMas];
        } catch(Throwable $e) { Conexion::revertir($pdo); throw $e; }
        finally { $this->db->desconectar(); }
    }
    public function enviar($idSiniestro,$idUsuario,$texto) {
        $texto=ChatReglas::mensaje($texto);
        $pdo=$this->db->conectar();
        try {
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
            $pdo->beginTransaction();
            $this->contexto($pdo,$idSiniestro,$idUsuario);
            $id=(int)Procedimientos::valor($pdo,'sp_chat_enviar',[(int)$idSiniestro,(int)$idUsuario,$texto]);
            if ($id<=0) throw new RuntimeException('No se guardo el mensaje.');
            $pdo->commit();
            return $id;
        } catch(Throwable $e) { Conexion::revertir($pdo); throw $e; }
        finally { $this->db->desconectar(); }
    }
}
