<?php

declare(strict_types=1);

namespace Eltabarani\ReusableQuery\Tests\Unit\ParameterTypes;

use Eltabarani\ReusableQuery\Tests\Fixtures\Scopes\ActiveUsersScope;
use Eltabarani\ReusableQuery\Tests\Fixtures\Scopes\AdminUsersScope;
use Eltabarani\ReusableQuery\Tests\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Scope;
use PHPUnit\Framework\TestCase;

class ScopeTest extends TestCase
{
    private User $model;

    private Builder $builder;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new User;
        $this->builder = $this->createMock(Builder::class);
    }

    public function test_scope_use_query_with_scope_instance(): void
    {
        $scope = $this->createMock(Scope::class);

        $this->builder->expects($this->once())
            ->method('getModel')
            ->willReturn($this->model);

        $scope->expects($this->once())
            ->method('apply')
            ->with($this->builder, $this->model);

        $result = $this->model->scopeUseQuery($this->builder, $scope);

        $this->assertSame($this->builder, $result);
    }

    public function test_scope_use_query_with_active_users_scope(): void
    {
        $scope = new ActiveUsersScope;

        $this->builder->expects($this->once())
            ->method('getModel')
            ->willReturn($this->model);

        $this->builder->expects($this->once())
            ->method('where')
            ->with('is_active', true)
            ->willReturnSelf();

        $result = $this->model->scopeUseQuery($this->builder, $scope);

        $this->assertSame($this->builder, $result);
    }

    public function test_scope_use_query_with_admin_users_scope(): void
    {
        $scope = new AdminUsersScope;

        $this->builder->expects($this->once())
            ->method('getModel')
            ->willReturn($this->model);

        $this->builder->expects($this->once())
            ->method('where')
            ->with('role', 'admin')
            ->willReturnSelf();

        $result = $this->model->scopeUseQuery($this->builder, $scope);

        $this->assertSame($this->builder, $result);
    }

    public function test_scope_use_query_with_multiple_scopes(): void
    {
        $scope1 = new ActiveUsersScope;
        $scope2 = new AdminUsersScope;

        $this->builder->expects($this->exactly(2))
            ->method('getModel')
            ->willReturn($this->model);

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

        $result = $this->model->scopeUseQueries($this->builder, [$scope1, $scope2]);

        $this->assertSame($this->builder, $result);
    }

    public function test_scope_use_query_does_not_modify_builder_directly(): void
    {
        $scope = $this->createMock(Scope::class);

        $this->builder->expects($this->once())
            ->method('getModel')
            ->willReturn($this->model);

        $scope->expects($this->once())
            ->method('apply')
            ->with($this->builder, $this->model);

        $result = $this->model->scopeUseQuery($this->builder, $scope);

        // Verify that the same builder instance is returned
        $this->assertSame($this->builder, $result);
    }
}
