<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear cuenta | Inventario de alimentos</title>
    <link rel="stylesheet" href="<?= esc(base_url('css/inventario.css'), 'attr') ?>">
</head>
<body class="pagina-acceso">
    <main class="acceso acceso-registro">
        <section class="acceso-presentacion" aria-labelledby="titulo-cafeteria">
            <?= view('partials/marca') ?>
            <div class="presentacion-contenido">
                <p class="etiqueta">CAFETERÍA · HANASKA</p>
                <h2 id="titulo-cafeteria">Todo listo para<br>empezar el día.</h2>
                <p>Crea tu cuenta y organiza los alimentos, sus precios y las unidades disponibles.</p>
                <img class="ilustracion-acceso" src="<?= esc(base_url('img/alimentos.svg'), 'attr') ?>" alt="" width="420" height="320">
            </div>
            <p class="nota-academica">Proyecto académico · Inventario de alimentos</p>
        </section>
        <section class="acceso-formulario registro" aria-labelledby="titulo-registro">
            <a class="enlace-volver" href="<?= esc(site_url('/'), 'attr') ?>"><?= view('partials/icono', ['nombre' => 'atras']) ?> Volver al login</a>
            <p class="etiqueta">TU NUEVA CUENTA</p>
            <h1 id="titulo-registro">Crear cuenta</h1>
            <p class="subtitulo">Completa tus datos. Todos los campos son obligatorios.</p>

            <?php if ($errores !== []): ?>
                <div class="alerta alerta-error" role="alert">
                    <strong>Revisa los datos del registro.</strong>
                    <?php if (isset($errores['registro'])): ?>
                        <p><?= esc($errores['registro']) ?></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?= esc(site_url('registro'), 'attr') ?>">
                <?= csrf_field() ?>
                <div class="campo">
                    <label for="usuario">Usuario</label>
                    <input id="usuario" name="usuario" type="text" autocomplete="username"
                           minlength="3" maxlength="30" placeholder="Ej. paul.cocina" required autofocus
                           value="<?= esc($datos['usuario'] ?? '', 'attr') ?>"
                           aria-invalid="<?= isset($errores['usuario']) ? 'true' : 'false' ?>"
                           aria-describedby="usuario-ayuda<?= isset($errores['usuario']) ? ' usuario-error' : '' ?>">
                    <small class="ayuda" id="usuario-ayuda">De 3 a 30 caracteres: letras, números, punto, guion o guion bajo.</small>
                    <?php if (isset($errores['usuario'])): ?>
                        <p class="error-campo" id="usuario-error"><?= esc($errores['usuario']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="campo">
                    <label for="correo">Correo electrónico</label>
                    <input id="correo" name="correo" type="email" autocomplete="email"
                           maxlength="254" placeholder="nombre@correo.com" required value="<?= esc($datos['correo'] ?? '', 'attr') ?>"
                           aria-invalid="<?= isset($errores['correo']) ? 'true' : 'false' ?>"
                           <?= isset($errores['correo']) ? 'aria-describedby="correo-error"' : '' ?>>
                    <?php if (isset($errores['correo'])): ?>
                        <p class="error-campo" id="correo-error"><?= esc($errores['correo']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="campo">
                    <label for="telefono">Teléfono</label>
                    <input id="telefono" name="telefono" type="tel" autocomplete="tel"
                           maxlength="32" placeholder="Ej. +593 99 123 4567" required value="<?= esc($datos['telefono'] ?? '', 'attr') ?>"
                           aria-invalid="<?= isset($errores['telefono']) ? 'true' : 'false' ?>"
                           aria-describedby="telefono-ayuda<?= isset($errores['telefono']) ? ' telefono-error' : '' ?>">
                    <small class="ayuda" id="telefono-ayuda">De 7 a 15 dígitos. Puedes incluir + y código de país.</small>
                    <?php if (isset($errores['telefono'])): ?>
                        <p class="error-campo" id="telefono-error"><?= esc($errores['telefono']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="campo">
                    <label for="password">Contraseña</label>
                    <input id="password" name="password" type="password" autocomplete="new-password"
                           minlength="8" maxlength="72" placeholder="Crea una contraseña" required
                           aria-invalid="<?= isset($errores['password']) ? 'true' : 'false' ?>"
                           aria-describedby="password-ayuda<?= isset($errores['password']) ? ' password-error' : '' ?>">
                    <small class="ayuda" id="password-ayuda">Mínimo 8 caracteres con mayúscula, minúscula, número y símbolo. Máximo 72 bytes.</small>
                    <?php if (isset($errores['password'])): ?>
                        <p class="error-campo" id="password-error"><?= esc($errores['password']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="campo">
                    <label for="confirmar_password">Confirmar contraseña</label>
                    <input id="confirmar_password" name="confirmar_password" type="password" autocomplete="new-password"
                           minlength="8" maxlength="72" placeholder="Escríbela otra vez" required
                           aria-invalid="<?= isset($errores['confirmar_password']) ? 'true' : 'false' ?>"
                           <?= isset($errores['confirmar_password']) ? 'aria-describedby="confirmar-password-error"' : '' ?>>
                    <?php if (isset($errores['confirmar_password'])): ?>
                        <p class="error-campo" id="confirmar-password-error"><?= esc($errores['confirmar_password']) ?></p>
                    <?php endif; ?>
                </div>
                <button class="boton boton-principal boton-login" type="submit">Registrar cuenta <?= view('partials/icono', ['nombre' => 'flecha']) ?></button>
            </form>
            <p class="enlace-cuenta">¿Ya tienes cuenta? <a href="<?= esc(site_url('/'), 'attr') ?>">Iniciar sesión</a></p>
        </section>
    </main>
</body>
</html>
