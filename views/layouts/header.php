<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Sistema de registro y seguimiento de siniestros de Seguros FCFM">
    <title>Seguros FCFM | Gestión de siniestros</title>
    <link rel="stylesheet" href="<?php echo urlsite ?>assets/css/bootstrap-4.6.min.css">
    <link rel="stylesheet" href="<?php echo urlsite ?>assets/css/style.css?v=6">
</head>
<body>
<?php
$mostrarHeader = !isset($_SESSION['rol_slug']) || $_SESSION['rol_slug'] === 'asegurado';
?>
<?php if ($mostrarHeader): ?>
    <header class="site-header">
        <a class="brand" href="<?php echo urlsite ?>?page=" aria-label="Ir al inicio de Seguros FCFM">
            <span class="brand-mark" aria-hidden="true">◆</span>
            <span>Seguros FCFM</span>
        </a>
    </header>
<?php endif; ?>
<main class="page-main">
<?php require __DIR__ . '/avisos.php'; ?>
