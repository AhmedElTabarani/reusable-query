<?php

declare(strict_types=1);

namespace Eltabarani\ReusableQuery\Tests\Queries;

use Eltabarani\ReusableQuery\Contracts\ReusableQueryContract;
use Illuminate\Database\Eloquent\Builder;

class UsersByStatusQuery implements ReusableQueryContract
{
    public function __construct(private string $status = 'active')
    {
    }

    public function useQuery(Builder $query): Builder
    {
        return $query->where('status', $this->status);
    }
}
