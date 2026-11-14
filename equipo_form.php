<?php
require 'config.php';

$id = (int)($_GET['id'] ?? 0);
$errores = [];
$v = ['codigo' => '', 'nombre' => '', 'tipo' => '', 'descripcion' => '', 'ubicacion' => '', 'estado' => 'Disponible'];

if ($id) {
    $st = $pdo->prepare('SELECT * FROM equipos WHERE id = ?');
    $st->execute([$id]);
    $fila = $st->fetch();
    if (!$fila) {
        header('Location: index.php');
        exit;
    }
    $v = $fila;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach (['codigo', 'nombre', 'tipo', 'descripcion', 'ubicacion', 'estado'] as $k) {
        $v[$k] = trim($_POST[$k] ?? '');
    }

    if ($v['codigo'] === '' || mb_strlen($v['codigo']) > 20) $errores[] = 'El código es obligatorio';
    if ($v['nombre'] === '' || mb_strlen($v['nombre']) > 100) $errores[] = 'El nombre es obligatorio';
    if (!in_array($v['tipo'], $TIPOS, true)) $errores[] = 'Seleccione un tipo';
    if ($v['ubicacion'] === '' || mb_strlen($v['ubicacion']) > 100) $errores[] = 'La ubicación es obligatoria';
    if (!in_array($v['estado'], ['Disponible', 'Mantenimiento'], true)) $errores[] = 'Seleccione un estado.';
    if (mb_strlen($v['descripcion']) > 255) $errores[] = 'Mucho texto';

    if (!$errores) {
        $st = $pdo->prepare('SELECT id FROM equipos WHERE codigo = ? AND id <> ?');
        $st->execute([$v['codigo'], $id]);
        if ($st->fetch()) $errores[] = 'Ya existe un equipo con ese codigo';
    }

    if (!$errores) {
        $desc = $v['descripcion'] !== '' ? $v['descripcion'] : null;
        if ($id) {
            $st = $pdo->prepare('UPDATE equipos SET codigo=?, nombre=?, tipo=?, descripcion=?, ubicacion=?, estado=? WHERE id=?');
            $st->execute([$v['codigo'], $v['nombre'], $v['tipo'], $desc, $v['ubicacion'], $v['estado'], $id]);
            $mensaje = 'Equipo actualizado';
        } else {
            $st = $pdo->prepare('INSERT INTO equipos (codigo, nombre, tipo, descripcion, ubicacion, estado) VALUES (?, ?, ?, ?, ?, ?)');
            $st->execute([$v['codigo'], $v['nombre'], $v['tipo'], $desc, $v['ubicacion'], $v['estado']]);
            $mensaje = 'Equipo agregado';
        }
        header('Location: index.php?ok=' . urlencode($mensaje));
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= $id ? 'Editar equipo' : 'Nuevo equipo' ?></title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<header class="topbar">
  <strong>Reservas de equipos</strong>
  <nav><a href="index.php" class="activo">Equipos</a> <a href="reservas.php">Reservas</a></nav>
</header>

<main class="contenedor angosto">
  <h1><?= $id ? 'Editar equipo' : 'Nuevo equipo' ?></h1>

  <?php foreach ($errores as $er): ?><div class="alerta error"><?= e($er) ?></div><?php endforeach; ?>

  <form method="post" class="formulario">
    <label>Código
      <input type="text" name="codigo" maxlength="20" required value="<?= e($v['codigo']) ?>">
    </label>

    <label>Nombre
      <input type="text" name="nombre" maxlength="100" required value="<?= e($v['nombre']) ?>">
    </label>

    <label>Tipo
      <select name="tipo" required>
        <option value="">Seleccione</option>
        <?php foreach ($TIPOS as $t): ?>
          <option value="<?= e($t) ?>" <?= $t === $v['tipo'] ? 'selected' : '' ?>><?= e($t) ?></option>
        <?php endforeach; ?>
      </select>
    </label>

    <label>Descripción (opcional)
      <input type="text" name="descripcion" maxlength="255" value="<?= e($v['descripcion']) ?>">
    </label>

    <label>Ubicación
      <input type="text" name="ubicacion" maxlength="100" required value="<?= e($v['ubicacion']) ?>">
    </label>

    <label>Estado
      <select name="estado">
        <option value="Disponible" <?= $v['estado'] === 'Disponible' ? 'selected' : '' ?>>Disponible</option>
        <option value="Mantenimiento" <?= $v['estado'] === 'Mantenimiento' ? 'selected' : '' ?>>Mantenimiento</option>
      </select>
    </label>

    <div class="botones">
      <button type="submit" class="btn btn-azul">Guardar</button>
      <a href="index.php" class="btn btn-gris">Cancelar</a>
    </div>
  </form>
</main>
</body>
</html>
