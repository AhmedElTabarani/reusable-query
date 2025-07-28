<?php

declare(strict_types=1);

namespace Eltabarani\ReusableQuery\Tests\Fixtures\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class AdminUsersScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('role', 'admin');
    }
}
