<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

/** Table: users (PK user_id). Only created_at exists, so Eloquent timestamps are off. */
class User extends Authenticatable
{
    protected $table = 'users';
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $fillable = ['full_name', 'username', 'email', 'password', 'role'];
    protected $hidden = ['password'];

    protected function casts(): array
    {
        return ['password' => 'hashed', 'created_at' => 'datetime'];
    }

    // The schema has no remember_token column.
    public function getRememberTokenName()
    {
        return '';
    }

    public function isAdmin(): bool
    {
        return $this->role === 'Admin';
    }

    public function harvests() { return $this->hasMany(Harvest::class, 'user_id', 'user_id'); }
    public function sales()    { return $this->hasMany(Sale::class, 'user_id', 'user_id'); }
    public function expenses() { return $this->hasMany(Expense::class, 'user_id', 'user_id'); }
}
