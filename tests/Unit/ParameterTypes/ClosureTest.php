<?php

declare(strict_types=1);

namespace Eltabarani\ReusableQuery\Tests\Unit\ParameterTypes;

use Closure;
use Eltabarani\ReusableQuery\Tests\Models\User;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\TestCase;

class ClosureTest extends TestCase
{
    private User $model;
    private Builder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new User();
        $this->builder = $this->createMock(Builder::class);
    }

    public function testScopeUseQueryWithClosure(): void
    {
        $called = false;
        $closure = function (Builder $query) use (&$called) {
            $called = true;
            $query->where('test', 'value');
        };

        $this->builder->expects($this->once())
            ->method('where')
            ->with('test', 'value')
            ->willReturnSelf();

        $result = $this->model->scopeUseQuery($this->builder, $closure);

        $this->assertTrue($called);
        $this->assertSame($this->builder, $result);
    }

    public function testScopeUseQueryWithComplexClosure(): void
    {
        $closure = function (Builder $query) {
            $query->where('is_active', true)
                ->where('role', 'admin');
        };

        $this->builder->expects($this->exactly(2))
            ->method('where')
            ->willReturnCallback(function ($field, $value) {
                if ($field === 'is_active') {
                    $this->assertEquals(true, $value);
                } elseif ($field === 'role') {
                    $this->assertEquals('admin', $value);
                }
                return $this->builder;
            });

        $result = $this->model->scopeUseQuery($this->builder, $closure);

        $this->assertSame($this->builder, $result);
    }

    public function testScopeUseQueryWithParametricClosure(): void
    {
        $status = 'active';
        $role = 'admin';

        $closure = function (Builder $query) use ($status, $role) {
            $query->where('status', $status)
                ->where('role', $role);
        };

        $this->builder->expects($this->exactly(2))
            ->method('where')
            ->willReturnCallback(function ($field, $value) use ($status, $role) {
                if ($field === 'status') {
                    $this->assertEquals($status, $value);
                } elseif ($field === 'role') {
                    $this->assertEquals($role, $value);
                }
                return $this->builder;
            });

        $result = $this->model->scopeUseQuery($this->builder, $closure);

        $this->assertSame($this->builder, $result);
    }

    public function testScopeUseQueryWithMultipleClosures(): void
    {
        $closure1 = function (Builder $query) {
            $query->where('is_active', true);
        };

        $closure2 = function (Builder $query) {
            $query->where('role', 'admin');
        };

        $this->builder->expects($this->exactly(2))
            ->method('where')
            ->willReturnCallback(function ($field, $value) {
                if ($field === 'is_active') {
                    $this->assertEquals(true, $value);
                } elseif ($field === 'role') {
                    $this->assertEquals('admin', $value);
                }
                return $this->builder;
            });

        $result = $this->model->scopeUseQueries($this->builder, [$closure1, $closure2]);

        $this->assertSame($this->builder, $result);
    }

    public function testClosureReceivesCorrectBuilderInstance(): void
    {
        $receivedBuilder = null;
        $closure = function (Builder $query) use (&$receivedBuilder) {
            $receivedBuilder = $query;
        };

        $this->model->scopeUseQuery($this->builder, $closure);

        $this->assertSame($this->builder, $receivedBuilder);
    }

    public function testClosureCanModifyBuilderState(): void
    {
        $closure = function (Builder $query) {
            $query->where('custom_field', 'custom_value');
        };

        $this->builder->expects($this->once())
            ->method('where')
            ->with('custom_field', 'custom_value')
            ->willReturnSelf();

        $result = $this->model->scopeUseQuery($this->builder, $closure);

        $this->assertSame($this->builder, $result);
    }
}
