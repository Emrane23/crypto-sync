<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CryptoAssetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'symbol' => $this->symbol,
            'price' => $this->last_price,
            'change_24h' => $this->price_change_percent,
            'volume_24h' => $this->volume_24h,
        ];
    }
}
