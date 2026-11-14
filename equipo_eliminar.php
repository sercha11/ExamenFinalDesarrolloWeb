<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);

    $st = $pdo->prepare('SELECT COUNT(*) FROM reservas WHERE equipo_id = ?');
    $st->execute([$id]);

    if ($st->fetchColumn() > 0) {
        $mensaje = 'EL EQUIPO ESTA RESERVADO';
    } else {
        $st = $pdo->prepare('DELETE FROM equipos WHERE id = ?');
        $st->execute([$id]);
        $mensaje = 'equipo eliminado.';
    }
    header('Location: index.php?ok=' . urlencode($mensaje));
    exit;
}
header('Location: index.php');
