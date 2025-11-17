<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Laravel\Sanctum\HasApiTokens;

class Reservation extends Model
{
    use HasApiTokens,HasUuids;
    protected $fillable = ['event_id','status','reserved_at','expires_at','reservation_token'];

    protected $casts = [
        'expires_at' => 'datetime',
        'reserved_at' => 'datetime',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public static function generateToken(): string
    {
        return Str::uuid()->toString();
    }
}
