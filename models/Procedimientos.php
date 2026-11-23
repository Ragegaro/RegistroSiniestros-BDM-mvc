<?php
/** Unica puerta de acceso SQL: PHP solo ejecuta CALL con parametros enlazados.
 * Toda lectura/escritura y toda regla de acceso a datos viven en rutinas SQL. */
final class Procedimientos {
    public static function llamar(PDO $pdo, string $nombre, array $parametros = []): PDOStatement {
        if (!preg_match('/^sp_[A-Za-z0-9_]+$/D', $nombre)) {
            throw new InvalidArgumentException('Nombre de procedimiento no permitido.');
        }
        $marcadores = implode(', ', array_fill(0, count($parametros), '?'));
        $stmt = $pdo->prepare('CALL ' . $nombre . '(' . $marcadores . ')');
        $stmt->execute(array_values($parametros));
        return $stmt;
    }
    public static function filas(PDO $pdo, string $nombre, array $parametros = [], int $modo = PDO::FETCH_ASSOC): array {
        $stmt = self::llamar($pdo, $nombre, $parametros);
        try { return $stmt->fetchAll($modo); }
        finally { $stmt->closeCursor(); }
    }
    public static function fila(PDO $pdo, string $nombre, array $parametros = [], int $modo = PDO::FETCH_ASSOC) {
        $stmt = self::llamar($pdo, $nombre, $parametros);
        try { return $stmt->fetch($modo); }
        finally { $stmt->closeCursor(); }
    }
    public static function valor(PDO $pdo, string $nombre, array $parametros = []) {
        $stmt = self::llamar($pdo, $nombre, $parametros);
        try { return $stmt->fetchColumn(); }
        finally { $stmt->closeCursor(); }
    }
    public static function ejecutar(PDO $pdo, string $nombre, array $parametros = []): bool {
        $stmt = self::llamar($pdo, $nombre, $parametros);
        $stmt->closeCursor();
        return true;
    }
}
