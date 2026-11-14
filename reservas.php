<?php
require 'config.php';
$msg = $_GET['ok'] ?? '';

$reservas = $pdo->query('SELECT r.*, e.codigo, e.nombre AS equipo
 FROM reservas r JOIN equipos e ON e.id = r.equipo_id
 ORDER BY r.fecha DESC, r.hora_inicio DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reservas</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
  <strong>Reservas de Equipos</strong>
  <nav><a href="index.php">Equipos</a> <a href="reservas.php" class="activo">Reservas</a></nav>
</header>

<main class="contenedor">
  <div class="encabezado">
    <h1>Listado de reservas</h1>
    <a class="btn btn-rojo" href="reservar.php">Nueva reserva</a>
  </div>

  <?php if ($msg): ?><div class="alerta ok"><?= e($msg) ?></div><?php endif; ?>

  <table>
    <thead>
      <tr><th>#</th><th>Equipo</th><th>Nombre</th><th>Carné</th><th>Fecha</th><th>Horario</th><th>Motivo</th><th>Acción</th></tr>
    </thead>
    <tbody>
    <?php foreach ($reservas as $i => $r): ?>
      <tr>
        <td><?= $i + 1 ?></td>
        <td><?= e($r['codigo'] . ' - ' . $r['equipo']) ?></td>
        <td><?= e($r['nombre']) ?></td>
        <td><?= e($r['carne']) ?></td>
        <td><?= e($r['fecha']) ?></td>
        <td><?= e(substr($r['hora_inicio'], 0, 5) . ' - ' . substr($r['hora_fin'], 0, 5)) ?></td>
        <td><?= e($r['motivo'] ?? '—') ?></td>
        <td>
          <a class="btn btn-gris" href="reservar.php?id=<?= (int)$r['id'] ?>">Editar</a>
          <form method="post" action="reserva_eliminar.php" class="en-linea" onsubmit="return confirm('¿Eliminar esta reserva?')">
            <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
            <button class="btn btn-rojo">Eliminar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$reservas): ?><tr><td colspan="8">Todavia no hay reservas</td></tr><?php endif; ?>
    </tbody>
  </table>
</main>
</body>
</html>
