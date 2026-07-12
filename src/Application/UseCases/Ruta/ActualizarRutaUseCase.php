<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Ruta;

use Elyra\Domain\Entity\Ruta;
use Elyra\Domain\Repository\RutaRepositoryInterface;

final class ActualizarRutaUseCase
{
    public function __construct(
        private RutaRepositoryInterface $rutaRepo,
    ) {
    }

    /**
     * @param array{
     *     id: int,
     *     nombre?: string,
     *     origen?: string,
     *     destino?: string,
     *     distanciaKm?: float,
     *     descripcion?: string,
     * } $input
     */
    public function execute(array $input): void
    {
        $ruta = $this->rutaRepo->findById($input['id']);
        if ($ruta === null) {
            throw new \DomainException('Ruta no encontrada.');
        }

        $updated = new Ruta(
            id: $ruta->getId(),
            nombre: $input['nombre'] ?? $ruta->getNombre(),
            origen: $input['origen'] ?? $ruta->getOrigen(),
            destino: $input['destino'] ?? $ruta->getDestino(),
            distanciaKm: $input['distanciaKm'] ?? $ruta->getDistanciaKm(),
            descripcion: $input['descripcion'] ?? $ruta->getDescripcion(),
            createdAt: $ruta->getCreatedAt(),
        );

        $this->rutaRepo->update($updated);
    }
}
