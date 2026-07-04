<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ManifestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'manifest_id',
        'item_id',
        'qty_requested',
        'qty_actual',
        'condition',
        'notes'
    ];

    public function manifest()
    {
        return $this->belongsTo(Manifest::class);
    }

    // Detail ini merujuk ke barang fisik yang mana?
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    // Jika barang bermasalah, detail ini punya satu log insiden (One-to-One)
    public function incidentLog()
    {
        return $this->hasOne(IncidentLog::class);
    }
}
