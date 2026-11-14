<?php
require 'config.php';

$tipo = $_GET['tipo'] ?? '';
$msg  = $_GET['ok'] ?? '';

if ($tipo !== '' && in_array($tipo, $TIPOS, true)) {
    $st = $pdo->prepare('SELECT * FROM equipos WHERE tipo = ? ORDER BY id');
    $st->execute([$tipo]);
} else {
    $tipo = '';
    $st = $pdo->query('SELECT * FROM equipos ORDER BY id');
}
$equipos = $st->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Reservas de equipos</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
  <strong>Reservas de equipos</strong>
  <nav><a href="index.php" class="activo">Equipos</a> <a href="reservas.php">Reservas</a></nav>
</header>

<main class="contenedor">
  <div class="encabezado">
    <h1>Listado de equipos</h1>
    <div>
      <a class="btn btn-verde" href="equipo_form.php">Nuevo equipo</a>
      <a class="btn btn-rojo" href="reservar.php">Nueva reserva</a>
    </div>
  </div>

  <?php if ($msg): ?><div class="alerta ok"><?= e($msg) ?></div><?php endif; ?>

  <form method="get" class="filtro">
    <select name="tipo">
      <option value="">Todos los tipos</option>
      <?php foreach ($TIPOS as $t): ?>
        <option value="<?= e($t) ?>" <?= $t === $tipo ? 'selected' : '' ?>><?= e($t) ?></option>
      <?php endforeach; ?>
    </select>
    <button class="btn btn-gris">Filtrar</button>
  </form>

  <table>
    <thead>
      <tr><th>Código</th><th>Nombre</th><th>Tipo</th><th>Ubicación</th><th>Estado</th><th>Acción</th></tr>
    </thead>
    <tbody>
    <?php foreach ($equipos as $i => $q): ?>
      <tr>
        <td><?= e($q['codigo']) ?></td>
        <td><?= e($q['nombre']) ?></td>
        <td><?= e($q['tipo']) ?></td>
        <td><?= e($q['ubicacion']) ?></td>
        <td><span class="badge <?= $q['estado'] === 'Disponible' ? 'verde' : 'naranja' ?>"><?= e($q['estado']) ?></span></td>
        <td>
          <?php if ($q['estado'] === 'Disponible'): ?>
            <a class="btn btn-azul" href="reservar.php?equipo=<?= (int)$q['id'] ?>">Reservar</a>
          <?php else: ?>
            <span class="btn btn-off">No disponible</span>
          <?php endif; ?>
          <a class="btn btn-gris" href="equipo_form.php?id=<?= (int)$q['id'] ?>">Editar</a>
          <form method="post" action="equipo_eliminar.php" class="en-linea" onsubmit="return confirm('¿Eliminar este equipo?')">
            <input type="hidden" name="id" value="<?= (int)$q['id'] ?>">
            <button class="btn btn-rojo">Eliminar</button>
          </form>
        </td>
      </tr>
    <?php endforeach; ?>
    <?php if (!$equipos): ?><tr><td colspan="6">Sin resultados.</td></tr><?php endif; ?>
    </tbody>
  </table>
</main>
</body>
</html>
