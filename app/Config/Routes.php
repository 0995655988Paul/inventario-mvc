<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->setAutoRoute(false);
$routes->get('/', 'Home::index', ['filter' => 'csrf']);
$routes->post('login', 'Home::autenticar', ['filter' => 'csrf']);
$routes->get('registro', 'Home::registro', ['filter' => 'csrf']);
$routes->post('registro', 'Home::registrar', ['filter' => 'csrf']);
$routes->get('salir', 'Home::salirPorGet');
$routes->post('salir', 'Home::salir', ['filter' => ['auth', 'csrf']]);

// El CRUD requiere sesión y protección CSRF.
$routes->group('productos', ['filter' => ['auth', 'csrf']], static function (RouteCollection $routes) {
    $routes->get('', 'Productos::index');
    $routes->get('nuevo', 'Productos::nuevo');
    $routes->post('guardar', 'Productos::guardar');
    $routes->get('(:num)/editar', 'Productos::editar/$1');
    $routes->post('(:num)/actualizar', 'Productos::actualizar/$1');
    $routes->post('(:num)/eliminar', 'Productos::eliminar/$1');
});
