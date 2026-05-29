<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= $titulo ?? 'Elyra' ?> — Hospital de Clínicas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php if (isset($_SESSION['user'])): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="/documentos">Elyra</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="/documentos">Documentos</a></li>
                    <li class="nav-item"><a class="nav-link" href="/encuestas">Encuestas</a></li>
                    <li class="nav-item"><a class="nav-link" href="/traslados">Ambulancias</a></li>
                    <li class="nav-item"><a class="nav-link" href="/conductores">Conductores</a></li>
                    <li class="nav-item"><a class="nav-link" href="/rutas">Rutas</a></li>
                </ul>
                <span class="navbar-text me-3"><?= $_SESSION['user']['nombre'] ?></span>
                <a href="/logout" class="btn btn-outline-light btn-sm">Cerrar sesión</a>
            </div>
        </div>
    </nav>
    <?php endif; ?>
    <main class="container mt-4">
        <?= $contenido ?? '' ?>
    </main>
</body>
</html>
