<?php

declare(strict_types=1);

namespace Elyra\Infrastructure\Web\Controller;

use Elyra\Domain\Entity\Noticia;
use Elyra\Infrastructure\Persistence\MySQL\NoticiaRepository;
use Elyra\Infrastructure\Service\AuditLogger;
use Elyra\Infrastructure\Service\RateLimiter;
use Elyra\Infrastructure\Service\SessionManager;
use Elyra\Infrastructure\Service\Validator;

class NoticiaController extends BaseController
{
    private NoticiaRepository $noticiaRepo;
    private string $storageDir;

    /** @var list<string> */
    private const ALLOWED_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    public function __construct()
    {
        $this->noticiaRepo = new NoticiaRepository();
        $this->storageDir = __DIR__ . '/../../../../public/uploads/noticias';
    }

    public function index(): void
    {
        $this->requireAuth();
        $this->denyPaciente();
        $this->requireRole('admin', 'superadmin');

        $noticias = $this->noticiaRepo->findAll();

        $this->render('noticias/index', [
            'noticias' => array_map(fn (Noticia $n) => $this->noticiaToArray($n), $noticias),
        ]);
    }

    public function crear(): void
    {
        $this->requireAuth();
        $this->denyPaciente();
        $this->requireRole('admin', 'superadmin');

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleCrear();
            return;
        }

        $this->render('noticias/crear');
    }

    private function handleCrear(): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (!is_string($ip)) {
            $ip = '127.0.0.1';
        }
        if (!RateLimiter::checkUploadAttempts($ip)) {
            $this->render('noticias/crear', ['error' => 'Demasiadas subidas. Esper&aacute; unos minutos.']);
            return;
        }
        RateLimiter::incrementUploadAttempts($ip);

        /** @var string $tituloRaw */
        $tituloRaw = $_POST['titulo'] ?? '';
        $titulo = trim($tituloRaw);
        /** @var string $contenidoRaw */
        $contenidoRaw = $_POST['contenido'] ?? '';
        $contenido = trim($contenidoRaw);

        $v = new Validator();
        $v->required('titulo', $titulo, 'Título')
          ->minLength('titulo', $titulo, 3, 'Título')
          ->maxLength('titulo', $titulo, 200, 'Título')
          ->required('contenido', $contenido, 'Contenido');

        $imagen = null;

        /** @var array{name: string, type: string, tmp_name: string, error: int, size: int}|null $archivo */
        $archivo = $_FILES['imagen'] ?? null;
        $uploadOk = $archivo !== null && $archivo['error'] === UPLOAD_ERR_OK;

        if ($uploadOk) {
            $mimeType = mime_content_type($archivo['tmp_name']);
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!in_array($mimeType, $allowedTypes, true)) {
                $v->required('imagen', null, 'La imagen debe ser JPG, PNG, WebP o GIF');
            } elseif ($archivo['size'] > 5 * 1024 * 1024) {
                $v->required('imagen', null, 'La imagen no debe superar los 5 MB');
            }
        }

        if (!$v->isValid()) {
            $this->render('noticias/crear', ['error' => $v->getFirstError()]);
            return;
        }

        if ($uploadOk) {
            if (!is_dir($this->storageDir)) {
                mkdir($this->storageDir, 0775, true);
            }

            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($archivo['name'], PATHINFO_FILENAME));
            $safeName = is_string($safeName) ? mb_substr($safeName, 0, 80) : 'imagen';
            $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                $v->required('imagen', null, 'Extensión de archivo no permitida');
                $uploadOk = false;
            }
            $filename = $safeName . '_' . time() . '.' . ($ext ?: 'jpg');
            $destPath = $this->storageDir . '/' . $filename;

            if (move_uploaded_file($archivo['tmp_name'], $destPath)) {
                $imagen = $filename;
            }
        }

        $noticia = new Noticia(
            id: null,
            titulo: Validator::sanitize($titulo),
            contenido: Validator::sanitize($contenido),
            imagen: $imagen,
            autorId: SessionManager::getUserId(),
            activo: true,
        );

        $this->noticiaRepo->save($noticia);
        AuditLogger::logCreate('noticia', $noticia->getId() !== null ? (string) $noticia->getId() : null, ['titulo' => $titulo]);
        $this->redirect('/noticias');
    }

    public function editar(): void
    {
        $this->requireAuth();
        $this->denyPaciente();
        $this->requireRole('admin', 'superadmin');

        /** @var string $idGet */
        $idGet = $_GET['id'] ?? '';
        /** @var string $idPost */
        $idPost = $_POST['id'] ?? '';
        $idRaw = $idGet ?: $idPost;
        $id = (int) $idRaw;

        if ($id <= 0) {
            $this->redirect('/noticias');
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleEditar($id);
            return;
        }

        $noticia = $this->noticiaRepo->findById($id);
        if (!$noticia) {
            $this->render('noticias/editar', ['error' => 'Noticia no encontrada.', 'noticia' => null]);
            return;
        }

        $this->render('noticias/editar', ['noticia' => $this->noticiaToArray($noticia)]);
    }

    private function handleEditar(int $id): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        if (!is_string($ip)) {
            $ip = '127.0.0.1';
        }
        if (!RateLimiter::checkUploadAttempts($ip)) {
            $noticia = $this->noticiaRepo->findById($id);
            $this->render('noticias/editar', ['error' => 'Demasiadas subidas. Esper&aacute; unos minutos.', 'noticia' => $noticia ? $this->noticiaToArray($noticia) : null]);
            return;
        }
        RateLimiter::incrementUploadAttempts($ip);

        $noticia = $this->noticiaRepo->findById($id);
        if (!$noticia) {
            $this->redirect('/noticias');
            return;
        }

        /** @var string $tituloRaw */
        $tituloRaw = $_POST['titulo'] ?? '';
        $titulo = trim($tituloRaw);
        /** @var string $contenidoRaw */
        $contenidoRaw = $_POST['contenido'] ?? '';
        $contenido = trim($contenidoRaw);

        $v = new Validator();
        $v->required('titulo', $titulo, 'Título')
          ->minLength('titulo', $titulo, 3, 'Título')
          ->maxLength('titulo', $titulo, 200, 'Título')
          ->required('contenido', $contenido, 'Contenido');

        /** @var array{name: string, type: string, tmp_name: string, error: int, size: int}|null $archivo */
        $archivo = $_FILES['imagen'] ?? null;
        $uploadOk = $archivo !== null && $archivo['error'] === UPLOAD_ERR_OK;

        if ($uploadOk) {
            $mimeType = mime_content_type($archivo['tmp_name']);
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
            if (!in_array($mimeType, $allowedTypes, true)) {
                $v->required('imagen', null, 'La imagen debe ser JPG, PNG, WebP o GIF');
            } elseif ($archivo['size'] > 5 * 1024 * 1024) {
                $v->required('imagen', null, 'La imagen no debe superar los 5 MB');
            }
        }

        if (!$v->isValid()) {
            $this->render('noticias/editar', ['error' => $v->getFirstError(), 'noticia' => $this->noticiaToArray($noticia)]);
            return;
        }

        $imagen = $noticia->getImagen();

        if ($uploadOk) {
            if (!is_dir($this->storageDir)) {
                mkdir($this->storageDir, 0775, true);
            }

            $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', pathinfo($archivo['name'], PATHINFO_FILENAME));
            $safeName = is_string($safeName) ? mb_substr($safeName, 0, 80) : 'imagen';
            $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            if (!in_array($ext, self::ALLOWED_EXTENSIONS, true)) {
                $v->required('imagen', null, 'Extensión de archivo no permitida');
                $uploadOk = false;
            }
            $filename = $safeName . '_' . time() . '.' . ($ext ?: 'jpg');
            $destPath = $this->storageDir . '/' . $filename;

            if (move_uploaded_file($archivo['tmp_name'], $destPath)) {
                if ($imagen && file_exists($this->storageDir . '/' . $imagen)) {
                    @unlink($this->storageDir . '/' . $imagen);
                }
                $imagen = $filename;
            }
        }

        $noticia->setTitulo(Validator::sanitize($titulo));
        $noticia->setContenido(Validator::sanitize($contenido));
        $noticia->setImagen($imagen);

        $this->noticiaRepo->update($noticia);
        AuditLogger::logUpdate('noticia', (string) $id);
        $this->redirect('/noticias');
    }

    public function eliminar(): void
    {
        $this->requireAuth();
        $this->denyPaciente();
        $this->requireRole('admin', 'superadmin');

        /** @var string $idRaw */
        $idRaw = $_POST['id'] ?? $_GET['id'] ?? '0';
        $id = (int) $idRaw;

        if ($id <= 0) {
            $this->redirect('/noticias');
            return;
        }

        $noticia = $this->noticiaRepo->findById($id);
        if ($noticia && $noticia->getImagen()) {
            $path = $this->storageDir . '/' . $noticia->getImagen();
            if (file_exists($path)) {
                @unlink($path);
            }
        }

        $this->noticiaRepo->delete($id);
        AuditLogger::logDelete('noticia', (string) $id);
        $this->redirect('/noticias');
    }

    public function toggle(): void
    {
        $this->requireAuth();
        $this->denyPaciente();
        $this->requireRole('admin', 'superadmin');

        /** @var string $idRaw */
        $idRaw = $_POST['id'] ?? $_GET['id'] ?? '0';
        $id = (int) $idRaw;

        $noticia = $this->noticiaRepo->findById($id);
        if (!$noticia) {
            $this->redirect('/noticias');
            return;
        }

        $noticia->setActivo(!$noticia->isActivo());
        $this->noticiaRepo->update($noticia);
        $this->redirect('/noticias');
    }

    /** @return array<string, mixed> */
    private function noticiaToArray(Noticia $n): array
    {
        $created = $n->getCreatedAt();

        return [
            'id' => $n->getId(),
            'titulo' => $n->getTitulo(),
            'contenido' => $n->getContenido(),
            'imagen' => $n->getImagen(),
            'autor_id' => $n->getAutorId(),
            'autor' => $n->getAutorNombre() ?? 'Desconocido',
            'activo' => $n->isActivo(),
            'creada' => $created ? date('d/m/Y', (int) strtotime($created)) : '',
        ];
    }
}
