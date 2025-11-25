<?php

namespace App\Console\Commands;

use App\Models\CryptoAsset;
use App\Services\BinanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CryptoSyncCommand extends Command
{
    protected $signature = 'crypto:sync';
    protected $description = 'Sync crypto assets from Binance (24h tickers)';

    public function handle(BinanceService $binance): int
    {
        $this->info('Starting crypto sync...');
        $startTime = microtime(true);

        try {
            $tickers = $binance->get24hTickers();

            $filtered = collect($tickers)
                ->filter(fn($t) => str_ends_with($t['symbol'], 'USDT'))
                ->filter(fn($t) => (float)$t['volume'] >= 1000)
                ->map(fn(array $ticker): array => [
                    'symbol' => $ticker['symbol'],
                    'base' => str_replace('USDT', '', $ticker['symbol']),
                    'quote' => 'USDT',
                    'last_price' => (float)$ticker['lastPrice'],
                    'price_change_percent' => (float)$ticker['priceChangePercent'],
                    'volume_24h' => (float)$ticker['volume'],
                ])
                ->values();

                foreach ($filtered->toArray() as $key => $ticker) {
                    CryptoAsset::create($ticker);
                }

            // DB::table('crypto_assets')->upsert(
            //     $filtered->toArray(),
            //     ['symbol'],
            //     ['last_price', 'price_change_percent', 'volume_24h', 'updated_at']
            // );

            Cache::forget('top_gainers');

            $duration = round(microtime(true) - $startTime, 2);
            $this->info("Synced {$filtered->count()} assets in {$duration}s");

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            Log::error('Crypto sync failed', ['error' => $e->getMessage()]);
            return Command::FAILURE;
        }
    }
}
