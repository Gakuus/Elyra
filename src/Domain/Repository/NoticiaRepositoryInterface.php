<?php

declare(strict_types=1);

namespace Elyra\Domain\Repository;

use Elyra\Domain\Entity\Noticia;

interface NoticiaRepositoryInterface
{
    public function findById(int $id): ?Noticia;
    /** @return Noticia[] */
    public function findAll(): array;
    /** @return Noticia[] */
    public function findLatest(int $limit = 3): array;
    /** @return Noticia[] */
    public function findThisWeek(): array;
    public function save(Noticia $noticia): Noticia;
    public function update(Noticia $noticia): void;
    public function delete(int $id): void;
}
