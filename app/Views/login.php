<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión | Inventario de alimentos</title>
    <link rel="stylesheet" href="<?= esc(base_url('css/inventario.css'), 'attr') ?>">
</head>
<body class="pagina-acceso">
    <main class="acceso">
        <section class="acceso-presentacion" aria-labelledby="titulo-cafeteria">
            <?= view('partials/marca') ?>
            <div class="presentacion-contenido">
                <p class="etiqueta">CAFETERÍA · HANASKA</p>
                <h2 id="titulo-cafeteria">Una cocina organizada.<br>Un buen día para todos.</h2>
                <p>Gestiona los alimentos de la cafetería y ten las existencias siempre a mano.</p>
                <img class="ilustracion-acceso" src="<?= esc(base_url('img/alimentos.svg'), 'attr') ?>" alt="" width="420" height="320">
            </div>
            <p class="nota-academica">Proyecto académico · Inventario de alimentos</p>
        </section>
        <section class="acceso-formulario login" aria-labelledby="titulo-login">
            <div class="marca-hanaska"><?= view('partials/icono', ['nombre' => 'alimento']) ?><span>Hanaska</span></div>
            <p class="etiqueta">BIENVENIDO A TU ESPACIO</p>
            <h1 id="titulo-login">Iniciar sesión</h1>
            <p class="subtitulo">Ingresa para administrar el inventario de comida.</p>

            <?php if ($exito = session()->getFlashdata('exito')): ?>
                <p class="alerta alerta-exito" role="status"><?= esc($exito) ?></p>
            <?php endif; ?>
            <?php if ($error = session()->getFlashdata('error')): ?>
                <p class="alerta alerta-error" role="alert"><?= esc($error) ?></p>
            <?php endif; ?>
            <?php if ($info = session()->getFlashdata('info')): ?>
                <p class="alerta alerta-info" role="status"><?= esc($info) ?></p>
            <?php endif; ?>

            <form method="post" action="<?= esc(site_url('login'), 'attr') ?>">
                <?= csrf_field() ?>
                <div class="campo">
                    <label for="usuario">Usuario</label>
                    <input id="usuario" name="usuario" type="text" autocomplete="username"
                           maxlength="50" placeholder="Tu nombre de usuario" required autofocus>
                </div>
                <div class="campo">
                    <label for="password">Contraseña</label>
                    <input id="password" name="password" type="password" autocomplete="current-password"
                           maxlength="72" placeholder="Tu contraseña" required>
                </div>
                <button class="boton boton-principal boton-login" type="submit">Ingresar <?= view('partials/icono', ['nombre' => 'flecha']) ?></button>
            </form>
            <p class="enlace-cuenta">¿No tienes cuenta? <a href="<?= esc(site_url('registro'), 'attr') ?>">Registrar cuenta</a></p>
            <p class="nota-acceso"><?= view('partials/icono', ['nombre' => 'escudo']) ?> Acceso protegido con tu cuenta</p>
        </section>
    </main>
</body>
</html>
