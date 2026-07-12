<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Ruta;

use Elyra\Domain\Entity\Ruta;
use Elyra\Domain\Repository\RutaRepositoryInterface;

final class CrearRutaUseCase
{
    public function __construct(
        private RutaRepositoryInterface $rutaRepo,
    ) {
    }

    /**
     * @param array{
     *     nombre: string,
     *     origen: string,
     *     destino: string,
     *     distanciaKm?: float,
     *     descripcion?: string,
     * } $input
     *
     * @return array{success: bool, rutaId: int}
     */
    public function execute(array $input): array
    {
        $nombre = trim($input['nombre']);
        $origen = trim($input['origen']);
        $destino = trim($input['destino']);

        if (strlen($nombre) < 2) {
            throw new \InvalidArgumentException('El nombre debe tener al menos 2 caracteres.');
        }
        if ($origen === '' || $destino === '') {
            throw new \InvalidArgumentException('Origen y destino son requeridos.');
        }

        $ruta = new Ruta(
            id: null,
            nombre: $nombre,
            origen: $origen,
            destino: $destino,
            distanciaKm: $input['distanciaKm'] ?? null,
            descripcion: $input['descripcion'] ?? null,
        );

        $saved = $this->rutaRepo->save($ruta);

        return ['success' => true, 'rutaId' => $saved->getId() ?? 0];
    }
}
