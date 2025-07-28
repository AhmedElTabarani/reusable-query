<?php

declare(strict_types=1);

namespace Eltabarani\ReusableQuery\Tests\Unit\ParameterTypes;

use Eltabarani\ReusableQuery\Tests\Models\User;
use Eltabarani\ReusableQuery\Tests\TestCase;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;

class ErrorHandlingTest extends TestCase
{
    public function testInvalidQueryTypeThrowsException(): void
    {
        $this->expectException(\TypeError::class);

        // Create an object that doesn't implement the expected interfaces
        $invalidQuery = new class {
            public function someMethod(): void
            {
                // This class doesn't implement ReusableQueryContract or Scope
            }
        };

        User::query()->useQuery($invalidQuery);
    }

    public function testNonExistentClassNameThrowsException(): void
    {
        $this->expectException(BindingResolutionException::class);
        $this->expectExceptionMessage('Target class [NonExistentClassName] does not exist.');

        User::query()->useQuery('NonExistentClassName');
    }

    public function testInvalidStringClassNameThatExistsButDoesNotImplementInterface(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid query type passed to useQuery().');

        // Using stdClass which exists but doesn't implement the required interface
        User::query()->useQuery(\stdClass::class);
    }

    public function testUseQueriesWithInvalidQueryInArray(): void
    {
        $this->expectException(\TypeError::class);

        $invalidQuery = new class {
            public function someMethod(): void
            {
                // Invalid query type
            }
        };

        User::query()->useQueries([
            function (Builder $query) {
                $query->where('valid', true);
            },
            $invalidQuery // This should trigger the exception
        ]);
    }

    public function testParametersPassedToInvalidQuery(): void
    {
        $this->expectException(\TypeError::class);

        $invalidQuery = new \stdClass();

        User::query()->useQuery($invalidQuery, ['some' => 'parameters']);
    }
}
