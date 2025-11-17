<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use SoftDeletes,HasUuids;
    protected $fillable = ['name','total_tickets','available_tickets'];

    protected $casts = [
        'date' => 'datetime',
    ];

    public function reservations():HasMany
    {
        return $this->hasMany(Reservation::class);
    }
}
