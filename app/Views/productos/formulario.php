<?php
$esEdicion = $producto !== null;
$titulo = $esEdicion ? 'Editar alimento' : 'Nuevo alimento';
$accion = $esEdicion
    ? 'productos/' . (int) $producto['id'] . '/actualizar'
    : 'productos/guardar';
$error = session()->getFlashdata('error');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($titulo) ?> | Inventario de cafetería</title>
    <link rel="stylesheet" href="<?= esc(base_url('css/inventario.css'), 'attr') ?>">
</head>
<body class="pagina-inventario">
    <?= view('partials/cabecera', ['usuario' => $usuario]) ?>

    <main class="contenedor formulario-pagina" id="contenido">
        <a class="enlace-volver" href="<?= esc(site_url('productos'), 'attr') ?>"><?= view('partials/icono', ['nombre' => 'atras']) ?> Volver al inventario</a>
        <div class="encabezado-pagina"><div><p class="etiqueta">ALIMENTOS DE LA CAFETERÍA</p><h1 id="titulo-formulario"><?= esc($titulo) ?></h1><p class="subtitulo"><?= $esEdicion ? 'Actualiza el precio o las existencias de este alimento.' : 'Registra un alimento para llevar el control de sus existencias.' ?></p></div></div>
        <section class="tarjeta" aria-labelledby="titulo-formulario">
            <div class="encabezado"><div><h2>Datos del alimento</h2><p>Todos los campos son obligatorios.</p></div><span class="formulario-icono"><?= view('partials/icono', ['nombre' => 'alimento']) ?></span></div>

            <?php if ($error): ?>
                <p class="alerta alerta-error" role="alert"><?= esc($error) ?></p>
            <?php endif; ?>
            <?php if ($errores !== []): ?>
                <div class="alerta alerta-error" role="alert">
                    <ul>
                        <?php foreach ($errores as $mensaje): ?>
                            <li><?= esc($mensaje) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form class="formulario-alimento" method="post" action="<?= esc(site_url($accion), 'attr') ?>">
                <?= csrf_field() ?>
                <div class="campo">
                    <label for="nombre">Nombre del alimento</label>
                    <input id="nombre" name="nombre" type="text" required minlength="1" maxlength="100"
                           value="<?= esc((string) ($datos['nombre'] ?? ''), 'attr') ?>"
                           placeholder="Ej. Yogur natural, arroz o jugo de naranja"
                           aria-invalid="<?= isset($errores['nombre']) ? 'true' : 'false' ?>" autofocus>
                </div>
                <div class="campos-dobles">
                    <div class="campo">
                        <label for="precio">Precio unitario (USD)</label>
                        <input id="precio" name="precio" type="number" required min="0" max="99999999.99"
                               step="0.01" inputmode="decimal"
                               value="<?= esc((string) ($datos['precio'] ?? ''), 'attr') ?>"
                               placeholder="Ej. 2.50" aria-describedby="precio-ayuda"
                               aria-invalid="<?= isset($errores['precio']) ? 'true' : 'false' ?>">
                        <small class="ayuda" id="precio-ayuda">Precio por una unidad. Máximo 2 decimales.</small>
                    </div>
                    <div class="campo">
                        <label for="stock">Cantidad disponible</label>
                        <input id="stock" name="stock" type="number" required min="0" max="999999999"
                               step="1" inputmode="numeric"
                               value="<?= esc((string) ($datos['stock'] ?? '0'), 'attr') ?>"
                               aria-describedby="stock-ayuda" aria-invalid="<?= isset($errores['stock']) ? 'true' : 'false' ?>">
                        <small class="ayuda" id="stock-ayuda">Número de unidades, sin decimales.</small>
                    </div>
                </div>
                <p class="nota-formulario"><?= view('partials/icono', ['nombre' => 'alerta']) ?> Con 5 unidades o menos, el alimento se marcará como «Por reponer» en el resumen.</p>
                <div class="acciones acciones-formulario">
                    <button class="boton boton-principal" type="submit"><?= view('partials/icono', ['nombre' => 'check']) ?> <?= $esEdicion ? 'Guardar cambios' : 'Guardar alimento' ?></button>
                    <a class="boton" href="<?= esc(site_url('productos'), 'attr') ?>">Cancelar</a>
                </div>
            </form>
        </section>
    </main>
</body>
</html>
