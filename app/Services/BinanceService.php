<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BinanceService
{
    protected string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.binance.base_url');
    }

    protected function request(string $endpoint, array $params = [])
    {
        try {
            $response = Http::retry(3, 500, throw: false)
                ->timeout(5)
                ->get($this->baseUrl . $endpoint, $params);

            if ($response->failed()) {
                Log::warning('Binance API Error', [
                    'endpoint' => $endpoint,
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);

                return ['error' => 'Upstream API error'];
            }

            return $response->json();

        } catch (\Throwable $e) {
            Log::error('Binance Request Exception', [
                'endpoint' => $endpoint,
                'message' => $e->getMessage()
            ]);

            return ['error' => 'Upstream API error'];
        }
    }

    public function get24hTickers()
    {
        return $this->request('/ticker/24hr');
    }

    public function getPrice(string $symbol)
    {
        return $this->request('/ticker/price', ['symbol' => $symbol]);
    }

    public function getExchangeInfo()
    {
        return $this->request('/exchangeInfo');
    }
}
