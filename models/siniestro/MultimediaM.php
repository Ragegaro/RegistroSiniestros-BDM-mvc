<?php
require_once __DIR__ . '/../Conexion.php';
require_once __DIR__ . '/../Procedimientos.php';
require_once __DIR__ . '/../../lib/Archivos.php';

class MultimediaM {
    private $db;
    private function conectar() {
        if (!$this->db) $this->db=new Conexion();
        $pdo=$this->db->conexion ?: $this->db->conectar();
        $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);
        return $pdo;
    }
    /** Evidencias nuevas: archivo fisico y ruta en base de datos; nada de BLOBs nuevos. */
    public static function insertarRuta(PDO $pdo,$archivo,$idSiniestro) {
        $base=dirname(__DIR__,2);
        $idSiniestro=(int)$idSiniestro;
        $directorio=$base.'/uploads/siniestros/SIN-'.$idSiniestro;
        if (!is_dir($directorio) && !mkdir($directorio,0755,true) && !is_dir($directorio))
            throw new RuntimeException('No se pudo crear la carpeta de evidencias.');
        $extension=strtolower(preg_replace('/[^a-zA-Z0-9]/','',$archivo['extension']??($archivo['tipo']==='Video'?'mp4':'jpg')));
        if ($extension==='') $extension=$archivo['tipo']==='Video'?'mp4':'jpg';
        $nombre=strtolower($archivo['tipo']).'_'.bin2hex(random_bytes(16)).'.'.$extension;
        $rutaRelativa='uploads/siniestros/SIN-'.$idSiniestro.'/'.$nombre;
        $rutaFisica=$directorio.'/'.$nombre;
        if (!move_uploaded_file($archivo['tmp'],$rutaFisica))
            throw new RuntimeException('No se pudo guardar el archivo en uploads/siniestros.');
        // No registrar en MySQL una ruta a un archivo incompleto o inaccesible.
        clearstatcache(true, $rutaFisica);
        if (!is_file($rutaFisica) || !is_readable($rutaFisica)
            || filesize($rutaFisica) !== (int) $archivo['bytes']) {
            @unlink($rutaFisica);
            throw new RuntimeException('La evidencia no se pudo verificar despues de guardarla.');
        }
        try {
            Procedimientos::ejecutar($pdo,'sp_multimedia_guardar_ruta',[$archivo['tipo'],$rutaRelativa,$idSiniestro]);
            return $rutaFisica;
        } catch(Throwable $e) { @unlink($rutaFisica); throw $e; }
    }
    public function obtener($id,$actor) {
        $pdo=$this->conectar();
        return Procedimientos::fila($pdo,'sp_multimedia_metadatos',[(int)$id,(int)$actor]);
    }
    public function bloque($id,$inicio,$cantidad,$actor) {
        $pdo=$this->conectar();
        $datos=Procedimientos::valor($pdo,'sp_multimedia_bloque',[(int)$id,(int)$actor,(int)$inicio,(int)$cantidad]);
        return is_resource($datos)?stream_get_contents($datos):$datos;
    }
}
