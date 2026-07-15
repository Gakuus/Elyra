<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <base href="<?= rtrim((string)(parse_url($_ENV['APP_URL'] ?? '', PHP_URL_PATH) ?: ''), '/') ?>/">
    <title>Sin conexión — Elyra</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="css/web20.css" rel="stylesheet">
    <style>
        .offline-page { min-height:100vh; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#E9EAED,#D8DDE3); padding:20px; }
        .offline-box { background:#FFF; border:2px solid #3B5998; padding:40px; text-align:center; max-width:440px; width:100%; box-shadow:0 8px 30px rgba(0,0,0,0.2); }
        .offline-icon { font-size:64px; color:#3B5998; margin-bottom:12px; }
        .offline-box h1 { font-size:20px; font-weight:bold; color:#333; margin:0 0 8px; }
        .offline-box p { font-size:13px; color:#777; margin:0 0 20px; }
        .offline-retro { font-family:'Courier New',monospace; font-size:11px; color:#999; border-top:1px dashed #CCC; padding-top:16px; margin-top:16px; }
        .offline-retro span { color:#3B5998; }
    </style>
</head>
<body>
    <div class="offline-page">
        <div class="offline-box">
            <div class="offline-icon"><i class="bi bi-wifi-off"></i></div>
            <h1>Sin conexión</h1>
            <p>No hay conexión a Internet. Revisá tu red e intentá de nuevo.</p>
            <button class="btn btn-primary" onclick="location.reload()"><i class="bi bi-arrow-clockwise me-1"></i>Reintentar</button>
            <div class="offline-retro">
                <span>&#95;</span> Elyra v1.0 <span>&#95;</span><br>
                Hospital de Clínicas
            </div>
        </div>
    </div>
</body>
</html>
