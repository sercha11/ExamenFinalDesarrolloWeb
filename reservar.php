<?php
require 'config.php';

$errores = [];
$v = [
    'nombre' => '', 'carne' => '', 'equipo_id' => (int)($_GET['equipo'] ?? 0),
    'fecha' => date('Y-m-d'), 'hora_inicio' => '14:00', 'hora_fin' => '18:00', 'motivo' => '',
];

$id = (int)($_GET['id'] ?? 0);

if ($id) {
    $st = $pdo->prepare('SELECT * FROM reservas WHERE id = ?');
    $st->execute([$id]);
    $fila = $st->fetch();
    if (!$fila) {
        header('Location: reservas.php');
        exit;
    }
    $v = [
        'nombre' => $fila['nombre'], 'carne' => $fila['carne'], 'equipo_id' => (int)$fila['equipo_id'],
        'fecha' => $fila['fecha'], 'hora_inicio' => substr($fila['hora_inicio'], 0, 5),
        'hora_fin' => substr($fila['hora_fin'], 0, 5), 'motivo' => (string)$fila['motivo'],
    ];
}

$st = $pdo->prepare("SELECT id, codigo, nombre FROM equipos WHERE estado = 'Disponible' OR id = ? ORDER BY codigo");
$st->execute([$v['equipo_id']]);
$equipos = $st->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['nombre', 'carne', 'fecha', 'hora_inicio', 'hora_fin', 'motivo'] as $k) {
        $v[$k] = trim($_POST[$k] ?? '');
    }
    $v['equipo_id'] = (int)($_POST['equipo_id'] ?? 0);

    if ($v['nombre'] === '' || mb_strlen($v['nombre']) > 100) $errores[] = 'El nombre completo es obligatorio (máx. 100 caracteres).';
    if (!preg_match('/^\d{4}-\d{4}$/', $v['carne'])) $errores[] = 'El carné no es válido. Formato: 2023-0145.';
    $d = DateTime::createFromFormat('Y-m-d', $v['fecha']);
    if (!$d || $d->format('Y-m-d') !== $v['fecha']) $errores[] = 'La fecha no es válida.';
    elseif (!$id && $v['fecha'] < date('Y-m-d')) $errores[] = 'La fecha no puede ser anterior a hoy.';
    if (!preg_match('/^\d{2}:\d{2}$/', $v['hora_inicio']) || !preg_match('/^\d{2}:\d{2}$/', $v['hora_fin'])) {
        $errores[] = 'Las horas no son válidas.';
    } elseif ($v['hora_fin'] <= $v['hora_inicio']) {
        $errores[] = 'La hora fin debe ser mayor que la hora de inicio.';
    }
    if (mb_strlen($v['motivo']) > 255) $errores[] = 'El motivo es demasiado largo (máx. 255).';

    $st = $pdo->prepare("SELECT id FROM equipos WHERE id = ? AND (estado = 'Disponible' OR id = ?)");
    $st->execute([$v['equipo_id'], $id ? $v['equipo_id'] : 0]);
    if (!$st->fetch()) $errores[] = 'Seleccione un equipo disponible.';

    if (!$errores) {
        $st = $pdo->prepare('SELECT hora_inicio, hora_fin FROM reservas
                             WHERE equipo_id = ? AND fecha = ? AND hora_inicio < ? AND hora_fin > ? AND id <> ? LIMIT 1');
        $st->execute([$v['equipo_id'], $v['fecha'], $v['hora_fin'], $v['hora_inicio'], $id]);
        if ($r = $st->fetch()) {
            $errores[] = 'Error: el equipo ya está reservado el ' . $v['fecha'] . ' de '
                       . substr($r['hora_inicio'], 0, 5) . ' a ' . substr($r['hora_fin'], 0, 5) . '.';
        }
    }

    if (!$errores) {
        $motivo = $v['motivo'] !== '' ? $v['motivo'] : null;
        if ($id) {
            $st = $pdo->prepare('UPDATE reservas SET equipo_id=?, nombre=?, carne=?, fecha=?, hora_inicio=?, hora_fin=?, motivo=? WHERE id=?');
            $st->execute([$v['equipo_id'], $v['nombre'], $v['carne'], $v['fecha'], $v['hora_inicio'], $v['hora_fin'], $motivo, $id]);
            $mensaje = 'Reserva actualizada.';
        } else {
            $st = $pdo->prepare('INSERT INTO reservas (equipo_id, nombre, carne, fecha, hora_inicio, hora_fin, motivo)
                                 VALUES (?, ?, ?, ?, ?, ?, ?)');
            $st->execute([$v['equipo_id'], $v['nombre'], $v['carne'], $v['fecha'], $v['hora_inicio'], $v['hora_fin'], $motivo]);
            $mensaje = 'Reserva guardada correctamente.';
        }
        header('Location: reservas.php?ok=' . urlencode($mensaje));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registrar reserva</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
  <strong>Reservas de Equipos</strong>
  <nav><a href="index.php">Equipos</a> <a href="reservas.php">Reservas</a></nav>
</header>

<main class="contenedor angosto">
  <h1><?= $id ? 'Editar reserva' : 'Registrar reserva' ?></h1>

  <?php foreach ($errores as $er): ?><div class="alerta error"><?= e($er) ?></div><?php endforeach; ?>

  <form method="post" class="formulario">
    <label>Nombre completo
      <input type="text" name="nombre" maxlength="100" required value="<?= e($v['nombre']) ?>" placeholder="Pedro Gómez">
    </label>

    <label>Carné
      <input type="text" name="carne" maxlength="9" required pattern="\d{4}-\d{4}" value="<?= e($v['carne']) ?>" placeholder="2023-0145">
      <small>Ingrese un carné válido (ej. 2023-0145)</small>
    </label>

    <label>Equipo
      <select name="equipo_id" required>
        <option value="">— Seleccione —</option>
        <?php foreach ($equipos as $q): ?>
          <option value="<?= (int)$q['id'] ?>" <?= $q['id'] == $v['equipo_id'] ? 'selected' : '' ?>>
            <?= e($q['codigo'] . ' - ' . $q['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </label>

    <div class="fila">
      <label>Fecha
        <input type="date" name="fecha" <?= $id ? '' : 'min="' . date('Y-m-d') . '"' ?> required value="<?= e($v['fecha']) ?>">
      </label>
      <label>Hora inicio
        <input type="time" name="hora_inicio" required value="<?= e($v['hora_inicio']) ?>">
      </label>
      <label>Hora fin
        <input type="time" name="hora_fin" required value="<?= e($v['hora_fin']) ?>">
      </label>
    </div>

    <label>Motivo (opcional)
      <input type="text" name="motivo" maxlength="255" value="<?= e($v['motivo']) ?>" placeholder="Exposición del curso">
    </label>

    <div class="botones">
      <button type="submit" class="btn btn-azul">Guardar</button>
      <a href="reservas.php" class="btn btn-gris">Cancelar</a>
    </div>
  </form>
</main>
</body>
</html>
