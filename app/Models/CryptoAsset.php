<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CryptoAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'symbol',
        'base',
        'quote',
        'last_price',
        'price_change_percent',
        'volume_24h',
    ];

    protected $casts = [
        'last_price' => 'decimal:8',
        'price_change_percent' => 'decimal:2',
        'volume_24h' => 'decimal:8',
    ];

    public function history(): HasMany
    {
        return $this->hasMany(PriceHistory::class);
    }
}
