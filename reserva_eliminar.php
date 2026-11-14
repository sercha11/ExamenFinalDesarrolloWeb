<?php
require 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $st = $pdo->prepare('DELETE FROM reservas WHERE id = ?');
    $st->execute([$id]);
    header('Location: reservas.php?ok=' . urlencode('Reserva eliminada.'));
    exit;
}
header('Location: reservas.php');
