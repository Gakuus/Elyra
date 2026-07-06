<?php

declare(strict_types=1);

require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = __DIR__ . '/../../.env';
if (file_exists($dotenv)) {
    $lines = file($dotenv, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with(trim($line), '#')) continue;
        if (str_contains($line, '=')) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

$pdo = \Elyra\Infrastructure\Persistence\MySQL\Connection::get();

echo "Ejecutando seeders...\n";

// Admin user
$stmt = $pdo->prepare("SELECT id FROM funcionario WHERE username = ?");
$stmt->execute(['admin']);
if (!$stmt->fetch()) {
    $pdo->beginTransaction();
    try {
        $pdo->exec("INSERT INTO usuario (tipo, nombre, apellido, email) VALUES ('funcionario', 'Admin', 'Elyra', 'admin@elyra.hc.edu.uy')");
        $userId = (int) $pdo->lastInsertId();

        $hash = password_hash('admin', PASSWORD_BCRYPT);
        $pdo->prepare("INSERT INTO funcionario (id, username, password_hash, rol, activo) VALUES (?, ?, ?, 'admin', TRUE)")->execute([$userId, 'admin', $hash]);

        $pdo->commit();
        echo "  ✓ Admin creado (admin/admin)\n";
    } catch (\Exception $e) {
        $pdo->rollBack();
        echo "  ✗ Error creando admin: {$e->getMessage()}\n";
    }
} else {
    echo "  - Admin ya existe\n";
}

// Categories — especialidades
$especialidades = [
    ['Cardiología', 'Documentos del área de cardiología'],
    ['Cirugía', 'Documentos del área de cirugía'],
    ['Enfermería', 'Documentos del área de enfermería'],
    ['Ginecología', 'Documentos del área de ginecología'],
    ['Imagenología', 'Documentos del área de imagenología'],
    ['Infectología', 'Documentos del área de infectología'],
    ['Nefrología', 'Documentos del área de nefrología'],
    ['Nutrición', 'Documentos del área de nutrición'],
];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM categoria WHERE tipo = 'especialidad'");
$stmt->execute();
if ((int) $stmt->fetchColumn() === 0) {
    $insert = $pdo->prepare("INSERT INTO categoria (nombre, descripcion, tipo) VALUES (?, ?, 'especialidad')");
    foreach ($especialidades as [$nombre, $desc]) {
        $insert->execute([$nombre, $desc]);
        echo "  ✓ Especialidad '{$nombre}' creada\n";
    }
} else {
    echo "  - Especialidades ya existen\n";
}

// Categories — tipos de documento
$tiposDocumento = [
    ['Protocolos', 'Protocolos médicos y procedimientos'],
    ['Formularios', 'Formularios administrativos'],
    ['Informes', 'Informes y reportes'],
    ['Legales', 'Documentación legal y consentimientos'],
    ['Manuales', 'Manuales de procedimiento'],
    ['Otros', 'Otros documentos'],
];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM categoria WHERE tipo = 'tipo_documento'");
$stmt->execute();
if ((int) $stmt->fetchColumn() === 0) {
    $insert = $pdo->prepare("INSERT INTO categoria (nombre, descripcion, tipo) VALUES (?, ?, 'tipo_documento')");
    foreach ($tiposDocumento as [$nombre, $desc]) {
        $insert->execute([$nombre, $desc]);
        echo "  ✓ Tipo '{$nombre}' creado\n";
    }
} else {
    echo "  - Tipos de documento ya existen\n";
}

// Basic routes
$rutas = [
    ['HC Hospital de Clínicas → CHPR', 'Hospital de Clínicas', 'Centro Hospitalario Pereira Rossell', 2.5],
    ['HC Hospital de Clínicas → ASSE Central', 'Hospital de Clínicas', 'ASSE Central', 3.0],
    ['HC Hospital de Clínicas → Hospital Militar', 'Hospital de Clínicas', 'Hospital Militar', 4.5],
    ['HC Hospital de Clínicas → Saint Bois', 'Hospital de Clínicas', 'Hospital Saint Bois', 8.0],
    ['HC Hospital de Clínicas → Hospital Maciel', 'Hospital de Clínicas', 'Hospital Maciel', 2.0],
];

$stmt = $pdo->prepare("SELECT COUNT(*) FROM ruta");
$stmt->execute();
$count = (int) $stmt->fetchColumn();

if ($count === 0) {
    $insert = $pdo->prepare("INSERT INTO ruta (nombre, origen, destino, distancia_km) VALUES (?, ?, ?, ?)");
    foreach ($rutas as [$nombre, $origen, $destino, $distancia]) {
        $insert->execute([$nombre, $origen, $destino, $distancia]);
        echo "  ✓ Ruta '{$nombre}' creada\n";
    }
} else {
    echo "  - Rutas ya existen ({$count} encontradas)\n";
}

echo "\nSeeders completados.\n";
