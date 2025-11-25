<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class NotifySharpMovementJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Collection $sharpMovements
    ) {}

    public function handle(): void
    {
        if ($this->sharpMovements->isNotEmpty()) {
            $message = $this->sharpMovements
                ->map(fn($asset) => "{$asset->symbol} (+{$asset->price_change_percent}%)")
                ->implode(', ');

            Log::warning("Sharp price movements: " . $message);
        }
    }
}
