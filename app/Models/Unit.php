<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Unit extends Model
{
    use SoftDeletes;
    protected $fillable = ['unit_code', 'name', 'description', 'magical_effect', 'rental_price', 'rarity', 'condition', 'status', 'image'];
    protected $casts = ['rental_price' => 'decimal:2'];
    public function categories(): BelongsToMany { return $this->belongsToMany(Category::class); }
    public function rentalItems() { return $this->hasMany(RentalItem::class); }
}
