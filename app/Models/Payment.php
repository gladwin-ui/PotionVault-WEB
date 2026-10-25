<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payment extends Model
{
    protected $fillable = ['rental_id', 'amount', 'status', 'paid_at', 'confirmed_by', 'refunded_at', 'refunded_by', 'refund_notes'];
    protected $casts = ['amount' => 'decimal:2', 'paid_at' => 'datetime', 'refunded_at' => 'datetime'];
    public function rental(): BelongsTo { return $this->belongsTo(Rental::class); }
    public function confirmer(): BelongsTo { return $this->belongsTo(User::class, 'confirmed_by'); }
    public function refunder(): BelongsTo { return $this->belongsTo(User::class, 'refunded_by'); }
}
