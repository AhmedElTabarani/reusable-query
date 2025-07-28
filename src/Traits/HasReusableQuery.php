<?php

declare(strict_types=1);

namespace Eltabarani\ReusableQuery\Traits;

use Closure;
use Eltabarani\ReusableQuery\Contracts\ReusableQueryContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Scope;
use InvalidArgumentException;

trait HasReusableQuery
{
    public function scopeUseQuery(Builder $builder, ReusableQueryContract|Scope|Closure|string $queryClass, array $parameters = []): Builder
    {
        $resolved = is_string($queryClass)
            ? app()->makeWith($queryClass, $parameters)
            : $queryClass;

        if ($resolved instanceof Closure) {
            $resolved($builder);
            return $builder;
        }

        if ($resolved instanceof Scope) {
            $resolved->apply($builder, $builder->getModel());
            return $builder;
        }

        if ($resolved instanceof ReusableQueryContract) {
            return $resolved->useQuery($builder);
        }

        throw new InvalidArgumentException('Invalid query type passed to useQuery().');
    }

    public function scopeUseQueries(Builder $builder, array $queries): Builder
    {
        foreach ($queries as $query) {
            if (is_array($query) && isset($query[0])) {
                $builder = $this->scopeUseQuery($builder, $query[0], $query[1] ?? []);
            } else {
                $builder = $this->scopeUseQuery($builder, $query);
            }
        }

        return $builder;
    }
}
