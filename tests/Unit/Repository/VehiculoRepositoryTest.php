<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Repository;

use Elyra\Infrastructure\Persistence\MySQL\VehiculoRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(VehiculoRepository::class)]
final class VehiculoRepositoryTest extends TestCase
{
    private MockObject&\PDOStatement $stmt;
    private MockObject&\PDO $pdo;
    private VehiculoRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(\PDO::class);
        $this->stmt = $this->createMock(\PDOStatement::class);

        $repoClass = new \ReflectionClass(VehiculoRepository::class);
        $this->repository = $repoClass->newInstanceWithoutConstructor();

        $pdoProp = $repoClass->getProperty('pdo');
        $pdoProp->setAccessible(true);
        $pdoProp->setValue($this->repository, $this->pdo);
    }

    public function testCountTotal(): void
    {
        $this->pdo->method('query')->willReturn($this->stmt);
        $this->stmt->method('fetchColumn')->willReturn('3');

        $this->assertSame(3, $this->repository->countTotal());
    }

    public function testFindByPatenteReturnsNullForNonExistent(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('fetch')->willReturn(false);

        $this->assertNull($this->repository->findByPatente('NONEXIST'));
    }

    public function testFindByIdReturnsNullForNonExistent(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('fetch')->willReturn(false);

        $this->assertNull($this->repository->findById(999));
    }
}
