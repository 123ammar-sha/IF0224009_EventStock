<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Flightcase extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'code'];

    // Relasi LAMA: item yang memiliki flightcase_id (masih dipertahankan untuk kompatibilitas)
    public function items()
    {
        return $this->hasMany(Item::class);
    }

    // Relasi BARU: item yang terdaftar di flightcase_item pivot
    public function bundledItems()
    {
        return $this->belongsToMany(Item::class, 'flightcase_item')
            ->withPivot('qty')
            ->withTimestamps();
    }
}
