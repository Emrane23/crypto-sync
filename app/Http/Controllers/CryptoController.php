<?php

namespace App\Http\Controllers;

use App\Http\Resources\CryptoAssetResource;
use App\Http\Resources\CryptoPriceHistoryResource;
use App\Jobs\NotifySharpMovementJob;
use App\Models\CryptoAsset;
use Carbon\Carbon;

use App\Models\PriceHistory;
use App\Services\BinanceService;
use Illuminate\Support\Facades\Cache;

class CryptoController extends Controller
{
    public function __construct(public BinanceService $binance) {

    }
    public function topGainers()
    {
        $assets = Cache::remember('top_gainers', 300, function () {
            return CryptoAsset::orderByDesc('price_change_percent')
                ->take(10)
                ->get();
        });

        $sharpMovements = $assets->filter(fn($asset) => $asset->price_change_percent >= 20);

        if ($sharpMovements->isNotEmpty()) {
            NotifySharpMovementJob::dispatch($sharpMovements);
        }

        return CryptoAssetResource::collection($assets);
    }

    public function history(string $symbol)
    {
        $asset = CryptoAsset::where('symbol', $symbol)->firstOrFail();

        $now = Carbon::now()->startOfHour();

        PriceHistory::firstOrCreate(
            [
                'crypto_asset_id' => $asset->id,
                'created_at' => $now,
            ],
            [
                'price' => $this->binance->getPrice($symbol)['price'],
            ]
        );

        $history = PriceHistory::where('crypto_asset_id', $asset->id)
            ->where('created_at', '>=', now()->subDay())
            ->orderBy('created_at', 'ASC')
            ->get();

        return response()->json([
            'symbol' => $asset->symbol,
            'history' => CryptoPriceHistoryResource::collection($history),
        ]);
    }
}
