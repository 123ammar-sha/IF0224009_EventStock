<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncidentLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'manifest_item_id',
        'type',
        'qty_affected',
        'qty_resolved',
        'qty_unresolved',
        'resolved',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function manifestItem()
    {
        return $this->belongsTo(ManifestItem::class);
    }

    public function getItemAttribute()
    {
        return $this->manifestItem?->item;
    }
}
