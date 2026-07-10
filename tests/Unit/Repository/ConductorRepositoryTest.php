<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Repository;

use Elyra\Infrastructure\Persistence\MySQL\ConductorRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConductorRepository::class)]
final class ConductorRepositoryTest extends TestCase
{
    private MockObject&\PDOStatement $stmt;
    private MockObject&\PDO $pdo;
    private ConductorRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(\PDO::class);
        $this->stmt = $this->createMock(\PDOStatement::class);

        $repoClass = new \ReflectionClass(ConductorRepository::class);
        $this->repository = $repoClass->newInstanceWithoutConstructor();

        $pdoProp = $repoClass->getProperty('pdo');
        $pdoProp->setAccessible(true);
        $pdoProp->setValue($this->repository, $this->pdo);
    }

    public function testCountTotalReturnsInt(): void
    {
        $this->pdo->method('query')->willReturn($this->stmt);
        $this->stmt->method('fetchColumn')->willReturn('8');

        $result = $this->repository->countTotal();
        $this->assertSame(8, $result);
    }

    public function testCountActivosReturnsInt(): void
    {
        $this->pdo->method('query')->willReturn($this->stmt);
        $this->stmt->method('fetchColumn')->willReturn('5');

        $result = $this->repository->countActivos();
        $this->assertSame(5, $result);
    }

    public function testFindByIdReturnsNullForNonExistent(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('fetch')->willReturn(false);

        $result = $this->repository->findById(999);
        $this->assertNull($result);
    }
}
