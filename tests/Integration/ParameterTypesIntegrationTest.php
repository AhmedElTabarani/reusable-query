<?php

declare(strict_types=1);

namespace Eltabarani\ReusableQuery\Tests\Integration;

use Eltabarani\ReusableQuery\Tests\Fixtures\Scopes\ActiveUsersScope;
use Eltabarani\ReusableQuery\Tests\Fixtures\Scopes\AdminUsersScope;
use Eltabarani\ReusableQuery\Tests\Models\User;
use Eltabarani\ReusableQuery\Tests\Queries\HasRoleQuery;
use Eltabarani\ReusableQuery\Tests\Queries\IsActiveQuery;
use Eltabarani\ReusableQuery\Tests\Queries\UsersByStatusQuery;
use Eltabarani\ReusableQuery\Tests\TestCase;

class ParameterTypesIntegrationTest extends TestCase
{
    public function test_reusable_query_contract_integration(): void
    {
        $user = User::query()
            ->useQuery(new IsActiveQuery)
            ->useQuery(new HasRoleQuery(['admin', 'moderator']));

        $expectedSql = 'select * from "users" where "is_active" = ? and "role" in (?, ?)';
        $this->assertEquals($expectedSql, $user->toSql());

        $bindings = $user->getBindings();
        $this->assertEquals([true, 'admin', 'moderator'], $bindings);
    }

    public function test_scope_integration(): void
    {
        $user = User::query()
            ->useQuery(new ActiveUsersScope)
            ->useQuery(new AdminUsersScope);

        $expectedSql = 'select * from "users" where "is_active" = ? and "role" = ?';
        $this->assertEquals($expectedSql, $user->toSql());

        $bindings = $user->getBindings();
        $this->assertEquals([true, 'admin'], $bindings);
    }

    public function test_closure_integration(): void
    {
        $status = 'verified';
        $user = User::query()
            ->useQuery(function ($query) use ($status) {
                $query->where('status', $status)
                    ->where('email_verified_at', '!=', null);
            });

        $expectedSql = 'select * from "users" where "status" = ? and "email_verified_at" is not null';
        $this->assertEquals($expectedSql, $user->toSql());

        $bindings = $user->getBindings();
        $this->assertEquals(['verified'], $bindings);
    }

    public function test_string_class_name_integration(): void
    {
        $user = User::query()
            ->useQuery(IsActiveQuery::class)
            ->useQuery(HasRoleQuery::class, ['roles' => ['admin']]);

        $expectedSql = 'select * from "users" where "is_active" = ? and "role" in (?)';
        $this->assertEquals($expectedSql, $user->toSql());

        $bindings = $user->getBindings();
        $this->assertEquals([true, 'admin'], $bindings);
    }

    public function test_string_class_name_with_parameters_integration(): void
    {
        $user = User::query()
            ->useQuery(UsersByStatusQuery::class, ['status' => 'pending']);

        $expectedSql = 'select * from "users" where "status" = ?';
        $this->assertEquals($expectedSql, $user->toSql());

        $bindings = $user->getBindings();
        $this->assertEquals(['pending'], $bindings);
    }

    public function test_mixed_parameter_types_integration(): void
    {
        $user = User::query()
            ->useQueries([
                new IsActiveQuery,
                new ActiveUsersScope,
                function ($query) {
                    $query->where('email_verified_at', '!=', null);
                },
                [HasRoleQuery::class, ['roles' => ['admin']]],
            ]);

        $expectedSql = 'select * from "users" where "is_active" = ? and "is_active" = ? and "email_verified_at" is not null and "role" in (?)';
        $this->assertEquals($expectedSql, $user->toSql());

        $bindings = $user->getBindings();
        $this->assertEquals([true, true, 'admin'], $bindings);
    }

    public function test_complex_query_chaining(): void
    {
        $user = User::query()
            ->useQuery(new IsActiveQuery)
            ->useQuery(function ($query) {
                $query->where('created_at', '>=', '2024-01-01');
            })
            ->useQuery(HasRoleQuery::class, ['roles' => ['admin', 'moderator']])
            ->orderBy('created_at', 'desc')
            ->limit(10);

        $expectedSql = 'select * from "users" where "is_active" = ? and "created_at" >= ? and "role" in (?, ?) order by "created_at" desc limit 10';
        $this->assertEquals($expectedSql, $user->toSql());

        $bindings = $user->getBindings();
        $this->assertEquals([true, '2024-01-01', 'admin', 'moderator'], $bindings);
    }

    public function test_use_queries_with_parameters_array(): void
    {
        $user = User::query()
            ->useQueries([
                [IsActiveQuery::class, []],
                [HasRoleQuery::class, ['roles' => ['admin', 'editor']]],
                [UsersByStatusQuery::class, ['status' => 'verified']],
            ]);

        $expectedSql = 'select * from "users" where "is_active" = ? and "role" in (?, ?) and "status" = ?';
        $this->assertEquals($expectedSql, $user->toSql());

        $bindings = $user->getBindings();
        $this->assertEquals([true, 'admin', 'editor', 'verified'], $bindings);
    }

    public function test_empty_queries_array(): void
    {
        $user = User::query()->useQueries([]);

        $expectedSql = 'select * from "users"';
        $this->assertEquals($expectedSql, $user->toSql());

        $bindings = $user->getBindings();
        $this->assertEquals([], $bindings);
    }

    public function test_query_builder_return_value(): void
    {
        $originalBuilder = User::query();
        $resultBuilder = $originalBuilder->useQuery(new IsActiveQuery);

        $this->assertSame($originalBuilder, $resultBuilder);
    }
}
