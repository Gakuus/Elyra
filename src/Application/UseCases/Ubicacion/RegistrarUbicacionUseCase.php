<?php

declare(strict_types=1);

namespace Elyra\Application\UseCases\Ubicacion;

use Elyra\Domain\Entity\UbicacionConductor;
use Elyra\Domain\Repository\TrasladoRepositoryInterface;
use Elyra\Domain\Repository\UbicacionConductorRepositoryInterface;
use Elyra\Infrastructure\Service\LocationBroadcaster;
use Elyra\Domain\ValueObject\Coordenada;

final class RegistrarUbicacionUseCase
{
    public function __construct(
        private UbicacionConductorRepositoryInterface $ubicacionRepo,
        private TrasladoRepositoryInterface $trasladoRepo,
        private LocationBroadcaster $broadcaster,
    ) {
    }

    /**
     * @param array{conductor_id: int, latitud: float, longitud: float, heading?: int|null, velocidad?: float|null, traslado_id?: int|null} $input
     * @return array{success: bool}
     */
    public function execute(array $input): array
    {
        $conductorId = $input['conductor_id'];
        $lat = $input['latitud'];
        $lng = $input['longitud'];

        if ($lat < -90 || $lat > 90 || $lng < -180 || $lng > 180) {
            throw new \InvalidArgumentException('Coordenadas inválidas');
        }

        $trasladoId = $input['traslado_id'] ?? null;
        if ($trasladoId !== null) {
            $traslado = $this->trasladoRepo->findById($trasladoId);
            if ($traslado === null) {
                throw new \InvalidArgumentException('Traslado no encontrado');
            }
        }

        $ubicacion = new UbicacionConductor(
            id: null,
            conductorId: $conductorId,
            trasladoId: $trasladoId,
            coordenada: new Coordenada($lat, $lng),
            heading: $input['heading'] ?? null,
            velocidad: $input['velocidad'] ?? null,
            updatedAt: null,
        );

        $this->ubicacionRepo->upsert($ubicacion);

        $this->broadcaster->broadcast([
            'type' => 'position_update',
            'data' => [
                'conductor_id' => $conductorId,
                'latitud' => $lat,
                'longitud' => $lng,
                'heading' => $ubicacion->getHeading(),
                'velocidad' => $ubicacion->getVelocidad(),
                'traslado_id' => $trasladoId,
            ],
        ]);

        return ['success' => true];
    }
}
