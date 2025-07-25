<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Purchase extends Model
{
    use HasFactory;
    protected $guarded = [];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
    public function part()
    {
        return $this->belongsTo(Part::class);
    }
    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }
}
