<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'flightcase_id', 'sku', 'name', 'total_qty', 'available_qty', 'status'];

    protected $casts = [
        'total_qty' => 'integer',
        'available_qty' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function flightcase()
    {
        return $this->belongsTo(Flightcase::class);
    }

    public function manifestItems()
    {
        return $this->hasMany(ManifestItem::class);
    }
}
