<?php

declare(strict_types=1);

namespace Eltabarani\ReusableQuery\Tests\Models;

use Eltabarani\ReusableQuery\Traits\HasReusableQuery;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasReusableQuery;

    protected $fillable = [
        'name',
        'email',
        'is_active',
        'role',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];
}
