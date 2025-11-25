<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('crypto_assets', function (Blueprint $table) {
            $table->id();
            $table->string('symbol')->unique();
            $table->string('base');
            $table->string('quote');
            $table->decimal('last_price', 18, 8)->nullable();
            $table->decimal('price_change_percent', 8, 4)->nullable();
            $table->decimal('volume_24h', 24, 8)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('crypto_assets');
    }
};
