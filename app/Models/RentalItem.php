<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RentalItem extends Model
{
    protected $fillable = ['rental_id', 'unit_id', 'rental_price_snapshot', 'borrowed_at', 'due_at', 'returned_at', 'return_condition', 'return_notes', 'fine_amount', 'fine_payment_status', 'fine_paid_at', 'fine_confirmed_by'];
    protected $casts = ['rental_price_snapshot' => 'decimal:2', 'fine_amount' => 'decimal:2', 'borrowed_at' => 'datetime', 'due_at' => 'datetime', 'returned_at' => 'datetime', 'fine_paid_at' => 'datetime'];
    public function rental(): BelongsTo { return $this->belongsTo(Rental::class); }
    public function unit(): BelongsTo { return $this->belongsTo(Unit::class); }
}
