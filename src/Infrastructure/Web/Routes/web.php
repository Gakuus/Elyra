<?php

declare(strict_types=1);

return [
    '/' => ['AuthController', 'login'],
    '/logout' => ['AuthController', 'logout'],

    '/documentos' => ['DocumentoController', 'index'],
    '/documentos/subir' => ['DocumentoController', 'subir'],
    '/documentos/editar' => ['DocumentoController', 'editar'],
    '/documentos/eliminar' => ['DocumentoController', 'eliminar'],
    '/documentos/ver' => ['DocumentoController', 'ver'],

    '/publico/doc' => ['PublicController', 'verDocumento'],
    '/publico/encuesta' => ['PublicController', 'mostrarEncuesta'],
    '/publico/encuesta/enviar' => ['PublicController', 'enviarEncuesta'],

    '/encuestas' => ['EncuestaController', 'index'],
    '/encuestas/crear' => ['EncuestaController', 'crear'],
    '/encuestas/resultados' => ['EncuestaController', 'resultados'],

    '/traslados' => ['TrasladoController', 'index'],
    '/traslados/nuevo' => ['TrasladoController', 'nuevo'],
    '/traslados/ver' => ['TrasladoController', 'ver'],
    '/traslados/actualizar-estado' => ['TrasladoController', 'actualizarEstado'],
    '/traslados/historial' => ['TrasladoController', 'historial'],

    '/conductores' => ['ConductorController', 'index'],
    '/conductores/crear' => ['ConductorController', 'crear'],

    '/rutas' => ['RutaController', 'index'],
    '/rutas/crear' => ['RutaController', 'crear'],
];
