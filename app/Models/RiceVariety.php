<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RiceVariety extends Model
{
    protected $table = 'rice_varieties';
    protected $primaryKey = 'variety_id';
    public $timestamps = false;
    protected $fillable = ['variety_name', 'description'];

    public function harvests() { return $this->hasMany(Harvest::class, 'variety_id', 'variety_id'); }
}
