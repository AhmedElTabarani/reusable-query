<?php

declare(strict_types=1);

namespace Eltabarani\ReusableQuery\Tests;

use Eltabarani\ReusableQuery\Contracts\ReusableQueryContract;
use Eltabarani\ReusableQuery\Tests\Models\User;
use Eltabarani\ReusableQuery\Traits\HasReusableQuery;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;

class HasReusableQueryTest extends TestCase
{
    public function testScopeUseQueryWithInstance(): void
    {
        $model = new User();
        $query = $this->createMock(Builder::class);
        $reusableQuery = $this->createMock(ReusableQueryContract::class);

        $reusableQuery->expects($this->once())
            ->method('useQuery')
            ->with($query)
            ->willReturn($query);

        $result = $model->scopeUseQuery($query, $reusableQuery);

        $this->assertSame($query, $result);
    }

    public function testScopeUseQueriesWithMultipleQueries(): void
    {
        $model = new User();
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
