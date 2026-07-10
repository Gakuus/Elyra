<?php

declare(strict_types=1);

namespace Elyra\Tests\Unit\Repository;

use Elyra\Domain\ValueObject\EstadoTraslado;
use Elyra\Domain\ValueObject\TipoElemento;
use Elyra\Infrastructure\Persistence\MySQL\TrasladoRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(TrasladoRepository::class)]
final class TrasladoRepositoryTest extends TestCase
{
    private MockObject&\PDOStatement $stmt;
    private MockObject&\PDO $pdo;
    private TrasladoRepository $repository;

    protected function setUp(): void
    {
        $this->pdo = $this->createMock(\PDO::class);
        $this->stmt = $this->createMock(\PDOStatement::class);

        $repoClass = new \ReflectionClass(TrasladoRepository::class);
        $this->repository = $repoClass->newInstanceWithoutConstructor();

        $pdoProp = $repoClass->getProperty('pdo');
        $pdoProp->setAccessible(true);
        $pdoProp->setValue($this->repository, $this->pdo);
    }

    public function testFindByIdReturnsNullWhenNoRow(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('fetch')->willReturn(false);

        $this->assertNull($this->repository->findById(999));
    }

    public function testFindByCodigoReturnsNullWhenNoRow(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('fetch')->willReturn(false);

        $this->assertNull($this->repository->findByCodigo('TR-NONEXIST'));
    }

    public function testNextCodigoReturnsString(): void
    {
        $this->pdo->method('query')->willReturn($this->stmt);
        $this->stmt->method('fetchColumn')->willReturn('5');

        $codigo = $this->repository->nextCodigo();
        $this->assertMatchesRegularExpression('/^TR-\d{5}$/', $codigo);
    }

    public function testCountTotalReturnsInt(): void
    {
        $this->pdo->method('query')->willReturn($this->stmt);
        $this->stmt->method('fetchColumn')->willReturn('42');

        $this->assertSame(42, $this->repository->countTotal());
    }

    public function testCountByEstadoReturnsInt(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->expects($this->once())->method('execute')->with(['completado']);
        $this->stmt->method('fetchColumn')->willReturn('10');

        $this->assertSame(10, $this->repository->countByEstado('completado'));
    }

    public function testCountReturnsInt(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->expects($this->once())->method('execute')->with(['pendiente']);
        $this->stmt->method('fetchColumn')->willReturn('7');

        $this->assertSame(7, $this->repository->count('pendiente'));
    }

    public function testFindAllReturnsArray(): void
    {
        $this->pdo->method('prepare')->willReturn($this->stmt);
        $this->stmt->method('fetchAll')->willReturn([]);

        $result = $this->repository->findAll();
        $this->assertIsArray($result);
    }
}
