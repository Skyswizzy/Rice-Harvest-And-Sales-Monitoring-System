<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $table = 'customers';
    protected $primaryKey = 'customer_id';
    public $timestamps = false;
    protected $fillable = ['customer_name', 'contact_number', 'address'];

    public function sales() { return $this->hasMany(Sale::class, 'customer_id', 'customer_id'); }
}
