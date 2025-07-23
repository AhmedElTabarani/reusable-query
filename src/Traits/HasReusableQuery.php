<?php

declare(strict_types=1);

namespace Eltabarani\ReusableQuery\Traits;

use Eltabarani\ReusableQuery\Contracts\ReusableQueryContract;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

trait HasReusableQuery
{
    public function scopeUseQuery(Builder $query, ReusableQueryContract|string $reusableQuery): Builder
    {
        $reusableQuery = $this->resolveReusableQuery($reusableQuery);
        return $reusableQuery->useQuery($query);
    }

    public function scopeUseQueries(Builder $query, array $reusableQueries): Builder
    {
        foreach ($reusableQueries as $reusableQuery) {
            $query = $this->scopeUseQuery($query, $reusableQuery);
        }

        return $query;
    }

    private function resolveReusableQuery(ReusableQueryContract|string $reusableQuery): ReusableQueryContract
    {
        if (is_string($reusableQuery)) {
            $reusableQuery = app()->make($reusableQuery);
            if (!$reusableQuery instanceof ReusableQueryContract) {
                throw new InvalidArgumentException("The provided reusable query must implement ReusableQueryContract interface.");
            }
        }

        return $reusableQuery;
    }
}
