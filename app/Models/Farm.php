<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Farm extends Model
{
    protected $table = 'farms';
    protected $primaryKey = 'farm_id';
    public $timestamps = false;
    protected $fillable = ['farm_name', 'location', 'area_hectares'];

    public function harvests() { return $this->hasMany(Harvest::class, 'farm_id', 'farm_id'); }
    public function expenses() { return $this->hasMany(Expense::class, 'farm_id', 'farm_id'); }
}
