<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StockTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'user_id',
        'type',
        'qty_change',
        'qty_before',
        'qty_after',
        'reference_type',
        'reference_id',
        'description'
    ];

    protected $casts = [
        'qty_change' => 'integer',
        'qty_before' => 'integer',
        'qty_after' => 'integer',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
