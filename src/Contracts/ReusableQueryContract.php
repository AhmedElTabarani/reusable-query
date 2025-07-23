<?php

declare(strict_types=1);

namespace Eltabarani\ReusableQuery\Contracts;

use Illuminate\Database\Eloquent\Builder;

interface ReusableQueryContract
{
    public function useQuery(Builder $query): Builder;
}
