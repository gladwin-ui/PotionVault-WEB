<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rental extends Model
{
    protected $fillable = ['user_id', 'status', 'rental_total', 'approved_at', 'approved_by', 'expires_at', 'rejection_reason'];
    protected $casts = ['rental_total' => 'decimal:2', 'approved_at' => 'datetime', 'expires_at' => 'datetime'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function items(): HasMany { return $this->hasMany(RentalItem::class); }
    public function payment() { return $this->hasOne(Payment::class); }
}
