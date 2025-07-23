# Laravel Reusable Query

Reusable and modular Eloquent query filters for Laravel. This package allows you to create reusable query filters that can be applied to any Eloquent model.

## Features

- **Reusable Query Filters** - Create once, use everywhere
  It's not like local scopes that are tied to a specific model
- **Separate Concerns** - Keep your query logic away from your models
  Rather than embedding query logic directly in your models, you can define reusable queries that can be applied to any model
- **Modular Design** - Compose complex queries in a modular way
  A single complex query can be written in a single class, and use it across different models or contexts
- And more... :D

## Requirements

- PHP 8.0 or higher
- Laravel 8.0 or higher

## Installation

You can install the package via Composer:

```bash
composer require eltabarani/reusable-query
```

The service provider will be automatically registered.

## Basic Usage

### Step 1: Create a Reusable Query

Create a class that implements the `ReusableQueryContract`:

```php
<?php

namespace App\Queries;

use Eltabarani\ReusableQuery\Contracts\ReusableQueryContract;
use Illuminate\Database\Eloquent\Builder;

class IsDraftQuery implements ReusableQueryContract
{
    public function useQuery(Builder $query): Builder
    {
        return $query->where('status', '=', 'draft');
    }
}
```

### Step 2: Use the Trait in Your Model

Add the `HasReusableQuery` trait to your Eloquent model:

```php
<?php

namespace App\Models;

use Eltabarani\ReusableQuery\Traits\HasReusableQuery;
use Illuminate\Database\Eloquent\Model;

class Article extends Model
{
    use HasReusableQuery;
}
```

### Step 3: Apply the Query

You can now use your reusable query in several ways:

#### Using Class Instance

```php
$draftArticles = Article::useQuery(new IsDraftQuery())->get();
```

#### Using Class Name

```php
$draftArticles = Article::useQuery(IsDraftQuery::class)->get();
```

#### Using It on Multiple Models

```php
$draftArticles = Article::useQuery(IsDraftQuery::class)->get();
$draftPurchases = Purchase::useQuery(IsDraftQuery::class)->get();
$draftProducts = Product::useQuery(IsDraftQuery::class)->get();
```

#### Using Multiple Queries

```php
$users = User::useQueries([
    ExcludeAuthUsersQuery::class,
    IsActiveQuery::class
])->get();
```

#### Passing Parameters to Queries

```php
<?php

namespace App\Queries;

use Eltabarani\ReusableQuery\Contracts\ReusableQueryContract;
use Illuminate\Database\Eloquent\Builder;

class UsersByRolesQuery implements ReusableQueryContract
{
    public function __construct(
        private array $roles
    ) {}

    public function useQuery(Builder $query): Builder
    {
        return $query->whereHas('roles', function ($q) {
            $q->whereIn('name', $this->roles);
        });
    }
}

// Usage
$authorisedUsers = User::useQuery(new UsersByRolesQuery(['admin', 'manager']))->get();
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [Ahmed El-Tabarani](https://github.com/eltabarani)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
