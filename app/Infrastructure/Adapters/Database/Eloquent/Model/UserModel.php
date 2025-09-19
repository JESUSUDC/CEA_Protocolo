<?php
declare(strict_types=1);

namespace Infrastructure\Adapters\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    protected $table = 'users';

    protected $primaryKey = 'id';

    public $incrementing = false; // domain id string

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'role',
        'email',
        'username',
        'password_hash',
        'active',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'active' => 'boolean',
    ];
}
