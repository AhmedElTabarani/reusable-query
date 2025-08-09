# Laravel Reusable Query

This package provides a elegant solution for creating and managing reusable query filters within Laravel applications.

---

When you deal with Global Scopes in Laravel, you often need to add a `identifier` then the scope object like this:

```php
$activeUsers = User::withGlobalScope('is_active', new IsActiveScope())->get();
```

This syntax can become overwhelming, especially when you have multiple scopes or complex query logic.

So i decided to my own simple and elegant class, then i decided to share it with the community.
Not a big deal, but it works fine for me and i hope it will help you too.

So here is the new syntax:

```php
$activeUsers = User::useQuery(IsActiveQuery::class)->get();
```

I believe this syntax is much cleaner and easier to read.

## Overview

The Laravel Reusable Query package simplifies the application of reusable query filters across Eloquent models.
It compatible with Scopes, Closures, and our own elegant syntax for defining reusable queries.
This package is designed to simplify query management, enhance code readability, and be compatible with all.

## Key Features

- **Simple Syntax**: Apply queries with clear, concise syntax.
- **Model Compatibility**: Works with all Eloquent models.
- **Flexible Parameters**: Supports class names, closures, and Eloquent scopes.
- **Reusable Logic**: Define query logic once, use across multiple models.

## Installation

To integrate the Reusable Query package into your Laravel project, execute the following command via Composer:

```bash
composer require eltabarani/reusable-query
```

## Quick Start Guide

Follow these steps to quickly implement and utilize Reusable Query in your application:

### 1. Create a Query Class

Define your reusable query logic by creating a class that implements the `ReusableQueryContract` interface. For example, to filter active records:

```php
<?php

namespace App\Queries;

use Eltabarani\ReusableQuery\Contracts\ReusableQueryContract;
use Illuminate\Database\Eloquent\Builder;

class IsActiveQuery implements ReusableQueryContract
{
    public function useQuery(Builder $query): Builder
    {
        return $query->where('status', '=', 'active');
    }
}
```

### 2. Integrate the Trait into Your Model

Incorporate the `HasReusableQuery` trait into your Eloquent models to use the methods:

```php
<?php

namespace App\Models;

use Eltabarani\ReusableQuery\Traits\HasReusableQuery;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasReusableQuery;
}
```

### 3. Apply Your Reusable Query

Once the trait is added, you can apply your defined query to any model.

This demonstrates the universal compatibility of the package:

```php
// Apply the IsActiveQuery to retrieve draft users
$draftUsers = User::useQuery(IsActiveQuery::class)->get();

// Apply the same query to other models, such as products, articles, or orders
$activeProducts = Product::useQuery(IsActiveQuery::class)->get();
$activeArticles = Article::useQuery(IsActiveQuery::class)->get();
$activeOrders = Order::useQuery(IsActiveQuery::class)->get();
```

## Advanced Usage

### Composing Multiple Queries

Reusable Query allows for clean and composable application of multiple query filters. This approach enhances readability and maintainability.

```php
// Composable queries
$users = User::useQueries([
    IsActiveQuery::class,
    EmailVerifiedQuery::class,
    RecentUsersQuery::class
])->get();

// or chaining methods
$users = User::useQuery(IsActiveQuery::class)
    ->useQuery(EmailVerifiedQuery::class)
    ->useQuery(RecentUsersQuery::class)
    ->get();
```

### Universal Compatibility Across Query Types

Reusable Query extends its utility by supporting various query input types, offering greater flexibility than global scopes.

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

### Robust Parameter Support

Reusable Query is support for parameters.

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

## Requirements

- PHP version 8.0 or higher
- Laravel framework version 8.0 or higher

## Testing

```bash
composer test
```

> fun fact: all tests are AI generated ... don't tell anyone!

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Ahmed El-Tabarani](https://github.com/eltabarani)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
