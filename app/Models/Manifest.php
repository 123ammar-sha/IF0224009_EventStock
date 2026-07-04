<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manifest extends Model
{
    use HasFactory;

    protected $fillable = [
        'manifest_number',
        'event_id',
        'user_id',
        'type',
        'status',
        'destination',
        'notes',
        'outbound_manifest_id'
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function manifestItems()
    {
        return $this->hasMany(ManifestItem::class);
    }

    public function outboundManifest()
    {
        return $this->belongsTo(Manifest::class, 'outbound_manifest_id');
    }

    public function inboundManifests()
    {
        return $this->hasMany(Manifest::class, 'outbound_manifest_id');
    }
}
