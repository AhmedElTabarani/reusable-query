# Changelog

All notable changes to `eltabarani/reusable-query` will be documented in this file.

## [1.1.0] - 2025-07-28

- Now the `useQuery` compatibility with Laravel's Eloquent Scopes and Closures

  ```php
  // Class names for clean, reusable query definitions
  User::useQuery(IsActiveQuery::class);

  // Instances with parameters, enabling dynamic query construction
  User::useQuery(new UsersByRoleQuery('admin'));

  // Closures, providing an optional inline query definition similar to global scopes
  User::useQuery(fn($q) => $q->where('created_at', '>=', now()->subWeek()));

  // Eloquent Scopes, compatibility with existing Laravel features
  User::useQuery(new ActiveScope());
  ```

- Reusable Query is support for parameters.

  ```php
  class UsersByRoleQuery implements ReusableQueryContract
  {
      public function __construct(private string $role) {}

      public function useQuery(Builder $query): Builder
      {
          return $query->where('role', $this->role);
      }
  }

  // Apply queries with dynamic parameters
  $admins = User::useQuery(UsersByRoleQuery::class, ['role' => 'admin'])->get();
  $editors = User::useQuery(UsersByRoleQuery::class, ['role' => 'editor'])->get();
  ```
- Added support for dynamic parameters in reusable queries
- Improved README for clarity and examples
- Add Tests for new features and compatibility (thanks to AI :))

## [1.0.0] - 2025-07-23

- Initial stable release :)
- Introduced `ReusableQueryContract` for defining reusable queries
- Added `useQuery` method for reusable query definitions
- Added `useQueries` method for applying multiple reusable queries
- Apply the same query to other models, such as products, articles, or orders
  ```php
  $activeProducts = Product::useQuery(IsActiveQuery::class)->get();
  $activeArticles = Article::useQuery(IsActiveQuery::class)->get();
  $activeOrders = Order::useQuery(IsActiveQuery::class)->get();
  ```
- Composable queries

  ```php
  $users = User::useQueries([
      IsActiveQuery::class,
      EmailVerifiedQuery::class,
      RecentUsersQuery::class
  ])->get();
  ```

  or chaining methods

  ```php
  $users = User::useQuery(IsActiveQuery::class)
      ->useQuery(EmailVerifiedQuery::class)
      ->useQuery(RecentUsersQuery::class)
      ->get();
  ```
