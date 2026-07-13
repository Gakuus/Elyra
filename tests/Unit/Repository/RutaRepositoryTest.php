<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Repository;

use Elyra\Domain\Entity\Ruta;
use Elyra\Infrastructure\Persistence\MySQL\RutaRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(RutaRepository::class)]
final class RutaRepositoryTest extends TestCase
{
    private MockObject&\PDOStatement $stmt;
    private MockObject&\PDO $pdo;
    private RutaRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(\PDO::class);
        $this->stmt = $this->createMock(\PDOStatement::class);

        $repoClass = new \ReflectionClass(RutaRepository::class);
        $this->repository = $repoClass->newInstanceWithoutConstructor();

        $pdoProp = $repoClass->getProperty('pdo');
        $pdoProp->setValue($this->repository, $this->pdo);
    }

    public function testCountTotal(): void
    {
        $this->pdo->method('query')->willReturn($this->stmt);
        $this->stmt->method('fetchColumn')->willReturn('5');

        $this->assertSame(5, $this->repository->countTotal());
    }

    public function testFindByIdReturnsNullForNonExistent(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('fetch')->willReturn(false);

        $this->assertNull($this->repository->findById(999));
    }

    public function testFindAllReturnsArray(): void
    {
        $this->pdo->method('query')->willReturn($this->stmt);
        $this->stmt->method('fetchAll')->willReturn([]);

        // @phpstan-ignore-next-line staticMethod.alreadyNarrowedType
        $this->assertIsArray($this->repository->findAll());
    }
}
