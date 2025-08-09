<?php

declare(strict_types=1);

namespace Eltabarani\ReusableQuery\Tests;

use Eltabarani\ReusableQuery\Tests\Models\User;
use Eltabarani\ReusableQuery\Tests\Queries\HasRoleQuery;
use Eltabarani\ReusableQuery\Tests\Queries\IsActiveQuery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class ReusableQueryIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpDatabase();
        $this->seedTestData();
    }

    protected function setUpDatabase(): void
    {
        Schema::create('users', function ($table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->boolean('is_active')->default(true);
            $table->string('role')->default('user');
            $table->timestamps();
        });
    }

    protected function seedTestData(): void
    {
        User::create([
            'name' => 'Active Admin',
            'email' => 'admin@example.com',
            'is_active' => true,
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Active User',
            'email' => 'user@example.com',
            'is_active' => true,
            'role' => 'user',
        ]);

        User::create([
            'name' => 'Inactive Admin',
            'email' => 'inactive.admin@example.com',
            'is_active' => false,
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Inactive User',
            'email' => 'inactive.user@example.com',
            'is_active' => false,
            'role' => 'user',
        ]);

        User::create([
            'name' => 'Active Manager',
            'email' => 'manager@example.com',
            'is_active' => true,
            'role' => 'manager',
        ]);
    }

    public function test_can_use_is_active_query_with_instance(): void
    {
        $activeUsers = User::useQuery(new IsActiveQuery)->get();

        $this->assertCount(3, $activeUsers);
        $this->assertTrue($activeUsers->every(fn ($user) => $user->is_active));
    }

    public function test_can_use_is_active_query_with_class_name(): void
    {
        $activeUsers = User::useQuery(IsActiveQuery::class)->get();

        $this->assertCount(3, $activeUsers);
        $this->assertTrue($activeUsers->every(fn ($user) => $user->is_active));
    }

    public function test_can_use_has_role_query_with_parameters(): void
    {
        $adminUsers = User::useQuery(new HasRoleQuery(['admin']))->get();

        $this->assertCount(2, $adminUsers);
        $this->assertTrue($adminUsers->every(fn ($user) => $user->role === 'admin'));
    }

    public function test_can_use_multiple_queries_together(): void
    {
        $activeAdmins = User::useQueries([
            IsActiveQuery::class,
            new HasRoleQuery(['admin']),
        ])->get();

        $this->assertCount(1, $activeAdmins);
        $activeAdmin = $activeAdmins->first();
        $this->assertTrue($activeAdmin->is_active);
        $this->assertEquals('admin', $activeAdmin->role);
        $this->assertEquals('Active Admin', $activeAdmin->name);
    }

    public function test_can_use_multiple_roles_in_has_role_query(): void
    {
        $adminAndManagerUsers = User::useQuery(
            new HasRoleQuery(['admin', 'manager'])
        )->get();

        $this->assertCount(3, $adminAndManagerUsers);
        $this->assertTrue($adminAndManagerUsers->every(
            fn ($user) => in_array($user->role, ['admin', 'manager'])
        ));
    }

    public function test_can_chain_with_regular_eloquent_methods(): void
    {
        $activeUsersOrderedByName = User::useQuery(IsActiveQuery::class)
            ->orderBy('name')
            ->get();

        $this->assertCount(3, $activeUsersOrderedByName);
        $this->assertEquals('Active Admin', $activeUsersOrderedByName->first()->name);
        // Alphabetically: Active Admin, Active Manager, Active User
        $names = $activeUsersOrderedByName->pluck('name')->toArray();
        $this->assertEquals(['Active Admin', 'Active Manager', 'Active User'], $names);
    }

    public function test_query_returns_empty_collection_when_no_matches(): void
    {
        $nonExistentRoleUsers = User::useQuery(
            new HasRoleQuery(['non-existent-role'])
        )->get();

        $this->assertCount(0, $nonExistentRoleUsers);
        $this->assertTrue($nonExistentRoleUsers->isEmpty());
    }

    public function test_can_use_queries_with_first_method(): void
    {
        $firstActiveUser = User::useQuery(IsActiveQuery::class)
            ->orderBy('name')
            ->first();

        $this->assertNotNull($firstActiveUser);
        $this->assertTrue($firstActiveUser->is_active);
        $this->assertEquals('Active Admin', $firstActiveUser->name);
    }

    public function test_can_use_queries_with_where_clauses(): void
    {
        $activeUserWithSpecificEmail = User::useQuery(IsActiveQuery::class)
            ->where('email', 'user@example.com')
            ->first();

        $this->assertNotNull($activeUserWithSpecificEmail);
        $this->assertTrue($activeUserWithSpecificEmail->is_active);
        $this->assertEquals('user@example.com', $activeUserWithSpecificEmail->email);
    }
}
