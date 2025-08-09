<?php

declare(strict_types=1);

namespace Eltabarani\ReusableQuery\Tests\Queries;

use Eltabarani\ReusableQuery\Contracts\ReusableQueryContract;
use Illuminate\Database\Eloquent\Builder;

class IsActiveQuery implements ReusableQueryContract
{
    public function useQuery(Builder $query): Builder
    {
        $query->where('is_active', true);

        return $query;
    }
}
