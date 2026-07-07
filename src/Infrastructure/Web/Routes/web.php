<?php

declare(strict_types=1);

return [
    ['method' => 'GET',  'pattern' => '/',                    'controller' => 'PublicController',    'action' => 'home'],
    ['method' => 'GET',  'pattern' => '/login',               'controller' => 'AuthController',      'action' => 'login'],
    ['method' => 'POST', 'pattern' => '/login',               'controller' => 'AuthController',      'action' => 'doLogin'],
    ['method' => 'GET',  'pattern' => '/registro',            'controller' => 'AuthController',      'action' => 'registro'],
    ['method' => 'POST', 'pattern' => '/registro',            'controller' => 'AuthController',      'action' => 'doRegistro'],
    ['method' => 'GET',  'pattern' => '/logout',              'controller' => 'AuthController',      'action' => 'logout'],
    ['method' => 'GET',  'pattern' => '/dashboard',           'controller' => 'DashboardController', 'action' => 'index'],

    ['method' => 'GET',  'pattern' => '/documentos',          'controller' => 'DocumentoController',  'action' => 'index'],
    ['method' => 'GET',  'pattern' => '/documentos/subir',    'controller' => 'DocumentoController',  'action' => 'subir'],
    ['method' => 'POST', 'pattern' => '/documentos/subir',    'controller' => 'DocumentoController',  'action' => 'subir'],
    ['method' => 'GET',  'pattern' => '/documentos/editar', 'controller' => 'DocumentoController', 'action' => 'editar'],
    ['method' => 'POST', 'pattern' => '/documentos/editar', 'controller' => 'DocumentoController', 'action' => 'editar'],
    ['method' => 'GET',  'pattern' => '/documentos/eliminar', 'controller' => 'DocumentoController', 'action' => 'eliminar'],
    ['method' => 'GET',  'pattern' => '/documentos/ver', 'controller' => 'DocumentoController',  'action' => 'ver'],
    ['method' => 'GET',  'pattern' => '/documentos/archivo', 'controller' => 'DocumentoController',  'action' => 'archivo'],

    ['method' => 'GET',  'pattern' => '/publico/doc',     'controller' => 'PublicController', 'action' => 'verDocumento'],
    ['method' => 'GET',  'pattern' => '/publico/archivo', 'controller' => 'PublicController', 'action' => 'archivo'],
    ['method' => 'GET',  'pattern' => '/publico/mis-documentos', 'controller' => 'PublicController', 'action' => 'misDocumentos'],
    ['method' => 'GET',  'pattern' => '/publico/encuesta', 'controller' => 'PublicController', 'action' => 'mostrarEncuesta'],
    ['method' => 'POST', 'pattern' => '/publico/encuesta',   'controller' => 'PublicController', 'action' => 'enviarEncuesta'],

    ['method' => 'GET',  'pattern' => '/encuestas',              'controller' => 'EncuestaController',  'action' => 'index'],
    ['method' => 'GET',  'pattern' => '/encuestas/crear',        'controller' => 'EncuestaController',  'action' => 'crear'],
    ['method' => 'POST', 'pattern' => '/encuestas/crear',        'controller' => 'EncuestaController',  'action' => 'guardar'],
    ['method' => 'GET',  'pattern' => '/encuestas/resultados', 'controller' => 'EncuestaController', 'action' => 'resultados'],

    ['method' => 'GET',  'pattern' => '/traslados',                 'controller' => 'TrasladoController',  'action' => 'index'],
    ['method' => 'GET',  'pattern' => '/traslados/nuevo',           'controller' => 'TrasladoController',  'action' => 'nuevo'],
    ['method' => 'POST', 'pattern' => '/traslados/nuevo',           'controller' => 'TrasladoController',  'action' => 'guardar'],
    ['method' => 'GET',  'pattern' => '/traslados/ver',       'controller' => 'TrasladoController',  'action' => 'ver'],
    ['method' => 'POST', 'pattern' => '/traslados/actualizar-estado', 'controller' => 'TrasladoController', 'action' => 'actualizarEstado'],
    ['method' => 'GET',  'pattern' => '/traslados/historial',      'controller' => 'TrasladoController',  'action' => 'historial'],

    ['method' => 'GET',  'pattern' => '/conductores',           'controller' => 'ConductorController', 'action' => 'index'],
    ['method' => 'GET',  'pattern' => '/conductores/crear',     'controller' => 'ConductorController', 'action' => 'crear'],
    ['method' => 'POST', 'pattern' => '/conductores/crear',     'controller' => 'ConductorController', 'action' => 'guardar'],

    ['method' => 'GET',  'pattern' => '/rutas',                'controller' => 'RutaController', 'action' => 'index'],
    ['method' => 'GET',  'pattern' => '/rutas/crear',          'controller' => 'RutaController', 'action' => 'crear'],
    ['method' => 'POST', 'pattern' => '/rutas/crear',          'controller' => 'RutaController', 'action' => 'guardar'],
];
