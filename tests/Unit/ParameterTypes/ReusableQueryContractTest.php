<?php

declare(strict_types=1);

namespace Eltabarani\ReusableQuery\Tests\Unit\ParameterTypes;

use Eltabarani\ReusableQuery\Contracts\ReusableQueryContract;
use Eltabarani\ReusableQuery\Tests\Models\User;
use Eltabarani\ReusableQuery\Tests\Queries\HasRoleQuery;
use Eltabarani\ReusableQuery\Tests\Queries\IsActiveQuery;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\TestCase;

class ReusableQueryContractTest extends TestCase
{
    private User $model;
    private Builder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new User();
        $this->builder = $this->createMock(Builder::class);
    }

    public function testScopeUseQueryWithReusableQueryContractInstance(): void
    {
        $reusableQuery = $this->createMock(ReusableQueryContract::class);

        $reusableQuery->expects($this->once())
            ->method('useQuery')
            ->with($this->builder)
            ->willReturn($this->builder);

        $result = $this->model->scopeUseQuery($this->builder, $reusableQuery);

        $this->assertSame($this->builder, $result);
    }

    public function testScopeUseQueryWithIsActiveQueryInstance(): void
    {
        $query = new IsActiveQuery();

        // Mock the builder to verify the correct method is called
        $this->builder->expects($this->once())
            ->method('where')
            ->with('is_active', true)
            ->willReturnSelf();

        $result = $this->model->scopeUseQuery($this->builder, $query);

        $this->assertSame($this->builder, $result);
    }

    public function testScopeUseQueryWithHasRoleQueryInstance(): void
    {
        $roles = ['admin', 'moderator'];
        $query = new HasRoleQuery($roles);

        // We'll test this functionality works by calling useQuery and expecting it to return the builder
        // The actual query building is tested in integration tests
        $result = $this->model->scopeUseQuery($this->builder, $query);

        $this->assertSame($this->builder, $result);
    }

    public function testScopeUseQueryWithMultipleReusableQueryInstances(): void
    {
        $query1 = $this->createMock(ReusableQueryContract::class);
        $query2 = $this->createMock(ReusableQueryContract::class);

        $query1->expects($this->once())
            ->method('useQuery')
            ->with($this->builder)
            ->willReturn($this->builder);

        $query2->expects($this->once())
            ->method('useQuery')
            ->with($this->builder)
            ->willReturn($this->builder);

        $result = $this->model->scopeUseQueries($this->builder, [$query1, $query2]);

        $this->assertSame($this->builder, $result);
    }

    public function testScopeUseQueryReturnsBuilderFromReusableQuery(): void
    {
        $expectedBuilder = $this->createMock(Builder::class);
        $reusableQuery = $this->createMock(ReusableQueryContract::class);

        $reusableQuery->expects($this->once())
            ->method('useQuery')
            ->with($this->builder)
            ->willReturn($expectedBuilder);

        $result = $this->model->scopeUseQuery($this->builder, $reusableQuery);

        $this->assertSame($expectedBuilder, $result);
    }
}
