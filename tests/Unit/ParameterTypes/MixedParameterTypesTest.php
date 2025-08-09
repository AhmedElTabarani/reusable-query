<?php

declare(strict_types=1);

namespace Eltabarani\ReusableQuery\Tests\Unit\ParameterTypes;

use Eltabarani\ReusableQuery\Tests\Fixtures\Scopes\ActiveUsersScope;
use Eltabarani\ReusableQuery\Tests\Models\User;
use Eltabarani\ReusableQuery\Tests\Queries\HasRoleQuery;
use Eltabarani\ReusableQuery\Tests\Queries\IsActiveQuery;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\TestCase;

class MixedParameterTypesTest extends TestCase
{
    private User $model;

    private Builder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new User;
        $this->builder = $this->createMock(Builder::class);
    }

    public function test_scope_use_queries_with_mixed_parameter_types(): void
    {
        // Create different parameter types
        $reusableQuery = new IsActiveQuery;
        $scope = new ActiveUsersScope;
        $closure = function (Builder $query) {
            $query->where('email_verified_at', '!=', null);
        };

        $queries = [$reusableQuery, $scope, $closure];

        // Set up expectations for the where calls - IsActiveQuery (1), ActiveUsersScope (1), closure (1) = 3 total
        $this->builder->expects($this->exactly(3))
            ->method('where')
            ->willReturnCallback(function ($field, $operator = null, $value = null) {
                if ($field === 'is_active') {
                    $this->assertEquals(true, $operator ?? $value);
                } elseif ($field === 'email_verified_at') {
                    $this->assertEquals('!=', $operator);
                    $this->assertNull($value);
                }

                return $this->builder;
            });

        // Set up expectations for Scope
        $this->builder->expects($this->once())
            ->method('getModel')
            ->willReturn($this->model);

        $result = $this->model->scopeUseQueries($this->builder, $queries);

        $this->assertSame($this->builder, $result);
    }

    public function test_scope_use_queries_with_array_parameters(): void
    {
        $queries = [
            new IsActiveQuery,
            [new HasRoleQuery(['admin', 'moderator']), []],
            function (Builder $query) {
                $query->where('custom_field', 'custom_value');
            },
        ];

        // Set up expectations - IsActiveQuery calls where, HasRoleQuery calls whereIn (won't be tested), closure calls where
        $this->builder->expects($this->exactly(2))
            ->method('where')
            ->willReturnCallback(function ($field, $value) {
                if ($field === 'is_active') {
                    $this->assertEquals(true, $value);
                } elseif ($field === 'custom_field') {
                    $this->assertEquals('custom_value', $value);
                }

                return $this->builder;
            });

        $result = $this->model->scopeUseQueries($this->builder, $queries);

        $this->assertSame($this->builder, $result);
    }

    public function test_scope_use_queries_with_empty_array(): void
    {
        $queries = [];

        $result = $this->model->scopeUseQueries($this->builder, $queries);

        $this->assertSame($this->builder, $result);
    }

    public function test_scope_use_query_execution_order(): void
    {
        $executionOrder = [];

        $closure1 = function (Builder $query) use (&$executionOrder) {
            $executionOrder[] = 'closure1';
        };

        $closure2 = function (Builder $query) use (&$executionOrder) {
            $executionOrder[] = 'closure2';
        };

        $closure3 = function (Builder $query) use (&$executionOrder) {
            $executionOrder[] = 'closure3';
        };

        $queries = [$closure1, $closure2, $closure3];

        $this->model->scopeUseQueries($this->builder, $queries);

        $this->assertEquals(['closure1', 'closure2', 'closure3'], $executionOrder);
    }

    public function test_scope_use_query_returns_builder_after_each_operation(): void
    {
        $queries = [
            new IsActiveQuery,
            function (Builder $query) {
                $query->where('role', 'admin');
            },
        ];

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

        $result = $this->model->scopeUseQueries($this->builder, $queries);

        $this->assertSame($this->builder, $result);
    }

    public function test_scope_use_query_preserves_builder_state(): void
    {
        $initialState = ['conditions' => 'initial'];
        $this->builder->state = $initialState;

        $query = function (Builder $builder) {
            // This closure doesn't modify any trackable state
        };

        $result = $this->model->scopeUseQuery($this->builder, $query);

        $this->assertSame($this->builder, $result);
        $this->assertEquals($initialState, $result->state);
    }
}
