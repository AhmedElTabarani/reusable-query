<?php

declare(strict_types=1);

namespace Eltabarani\ReusableQuery\Tests;

use Eltabarani\ReusableQuery\Contracts\ReusableQueryContract;
use Eltabarani\ReusableQuery\Tests\Models\User;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\TestCase;

class HasReusableQueryTest extends TestCase
{
    public function test_scope_use_query_with_instance(): void
    {
        $model = new User;
        $query = $this->createMock(Builder::class);
        $reusableQuery = $this->createMock(ReusableQueryContract::class);

        $reusableQuery->expects($this->once())
            ->method('useQuery')
            ->with($query)
            ->willReturn($query);

        $result = $model->scopeUseQuery($query, $reusableQuery);

        $this->assertSame($query, $result);
    }

    public function test_scope_use_queries_with_multiple_queries(): void
    {
        $model = new User;
        $query = $this->createMock(Builder::class);

        $query1 = $this->createMock(ReusableQueryContract::class);
        $query2 = $this->createMock(ReusableQueryContract::class);

        $query1->expects($this->once())
            ->method('useQuery')
            ->with($query)
            ->willReturn($query);

        $query2->expects($this->once())
            ->method('useQuery')
            ->with($query)
            ->willReturn($query);

        $result = $model->scopeUseQueries($query, [$query1, $query2]);

        $this->assertSame($query, $result);
    }
}
