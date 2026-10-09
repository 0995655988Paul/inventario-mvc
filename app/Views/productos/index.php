<?php
$exito = session()->getFlashdata('exito');
$error = session()->getFlashdata('error');
$info = session()->getFlashdata('info');
$totalUnidades = array_sum(array_column($productos, 'stock'));
$porReponer = count(array_filter($productos, static fn ($producto) => (int) $producto['stock'] <= 5));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alimentos | Inventario de cafetería</title>
    <link rel="stylesheet" href="<?= esc(base_url('css/inventario.css'), 'attr') ?>">
</head>
<body class="pagina-inventario">
    <?= view('partials/cabecera', ['usuario' => $usuario]) ?>

    <main class="contenedor" id="contenido">
        <p class="ruta-pagina">Cafetería <span>/</span> Inventario</p>
        <div class="encabezado-pagina">
            <div>
                <p class="etiqueta">ORGANIZA TU COCINA</p>
                <h1>Inventario de alimentos</h1>
                <p class="subtitulo">Consulta y administra la comida de la cafetería.</p>
            </div>
            <a class="boton boton-principal" href="<?= esc(site_url('productos/nuevo'), 'attr') ?>"><?= view('partials/icono', ['nombre' => 'mas']) ?> Agregar alimento</a>
        </div>
        <section class="resumen" aria-label="Resumen del inventario">
            <div class="indicador">
                <span class="indicador-icono"><?= view('partials/icono', ['nombre' => 'alimento']) ?></span>
                <div><p>Alimentos registrados</p><strong><?= count($productos) ?></strong></div>
            </div>
            <div class="indicador">
                <span class="indicador-icono indicador-azul"><?= view('partials/icono', ['nombre' => 'caja']) ?></span>
                <div><p>Unidades disponibles</p><strong><?= number_format($totalUnidades, 0, '.', ',') ?></strong></div>
            </div>
            <div class="indicador">
                <span class="indicador-icono indicador-ambar"><?= view('partials/icono', ['nombre' => 'alerta']) ?></span>
                <div><p>Por reponer <small>5 unidades o menos</small></p><strong><?= $porReponer ?></strong></div>
            </div>
        </section>
        <?php if ($exito): ?>
            <p class="alerta alerta-exito" role="status"><?= esc($exito) ?></p>
        <?php endif; ?>
        <?php if ($info): ?>
            <p class="alerta alerta-info" role="status"><?= esc($info) ?></p>
        <?php endif; ?>
        <?php if ($error): ?>
            <p class="alerta alerta-error" role="alert"><?= esc($error) ?></p>
        <?php endif; ?>

        <section class="tarjeta" aria-labelledby="titulo-productos">
            <div class="encabezado">
                <div><h2 id="titulo-productos">Alimentos de la cafetería</h2><p>Precios y existencias en un solo lugar.</p></div>
                <span class="contador"><?= count($productos) ?> <?= count($productos) === 1 ? 'alimento' : 'alimentos' ?></span>
            </div>

            <div class="tabla-contenedor">
                <table>
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Alimento</th>
                            <th scope="col" class="numero">Precio unitario</th>
                            <th scope="col" class="numero">Cantidad</th>
                            <th scope="col">Disponibilidad</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto): ?>
                            <?php
                            $stock = (int) $producto['stock'];
                            $estadoClase = $stock === 0 ? 'agotado' : ($stock <= 5 ? 'bajo' : 'disponible');
                            $estadoTexto = $stock === 0 ? 'Agotado' : ($stock <= 5 ? 'Pocas unidades' : 'Disponible');
                            ?>
                            <tr>
                                <td class="id-alimento">#<?= (int) $producto['id'] ?></td>
                                <td><div class="nombre-alimento"><span class="alimento-icono"><?= view('partials/icono', ['nombre' => 'alimento']) ?></span><strong><?= esc($producto['nombre']) ?></strong></div></td>
                                <td class="numero">$ <?= number_format((float) $producto['precio'], 2, '.', ',') ?></td>
                                <td class="numero cantidad"><?= $stock ?> <small>u.</small></td>
                                <td><span class="estado estado-<?= $estadoClase ?>"><span aria-hidden="true"></span><?= $estadoTexto ?></span></td>
                                <td>
                                    <div class="acciones">
                                        <a class="boton boton-pequeno" href="<?= esc(site_url('productos/' . (int) $producto['id'] . '/editar'), 'attr') ?>" aria-label="<?= esc('Editar ' . $producto['nombre'], 'attr') ?>"><?= view('partials/icono', ['nombre' => 'editar']) ?> Editar</a>
                                        <form method="post" action="<?= esc(site_url('productos/' . (int) $producto['id'] . '/eliminar'), 'attr') ?>"
                                              onsubmit="return confirm('¿Eliminar este alimento?');">
                                            <?= csrf_field() ?>
                                            <button class="boton boton-peligro boton-pequeno" type="submit" aria-label="<?= esc('Eliminar ' . $producto['nombre'], 'attr') ?>"><?= view('partials/icono', ['nombre' => 'eliminar']) ?> Eliminar</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php if ($productos === []): ?>
                            <tr><td class="sin-productos" colspan="6"><?= view('partials/icono', ['nombre' => 'alimento']) ?><strong>Tu inventario está listo para empezar</strong><p>Agrega tu primer alimento con su precio y cantidad.</p><a class="boton boton-principal" href="<?= esc(site_url('productos/nuevo'), 'attr') ?>">Agregar alimento</a></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
        <div class="nota-inventario">
            <?= view('partials/icono', ['nombre' => 'alimento']) ?>
            <p>Un inventario al día ayuda a planificar cada servicio.<span>Actualiza las cantidades al recibir o utilizar alimentos.</span></p>
            <img src="<?= esc(base_url('img/alimentos.svg'), 'attr') ?>" width="420" height="320" alt="">
        </div>
        <footer class="pie-pagina">UDLA · Hanaska <span>Proyecto académico · Inventario de alimentos</span></footer>
    </main>
</body>
</html>
