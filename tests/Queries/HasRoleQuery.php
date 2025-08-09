<?php

declare(strict_types=1);

namespace Eltabarani\ReusableQuery\Tests\Queries;

use Eltabarani\ReusableQuery\Contracts\ReusableQueryContract;
use Illuminate\Database\Eloquent\Builder;

class HasRoleQuery implements ReusableQueryContract
{
    public function __construct(private array $roles) {}

    public function useQuery(Builder $query): Builder
    {
        $query->whereIn('role', $this->roles);

        return $query;
    }
}
