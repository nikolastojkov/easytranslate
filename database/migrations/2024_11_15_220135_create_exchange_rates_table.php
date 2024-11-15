<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(
            table: 'exchange_rates',
            callback: function (Blueprint $table): void {
                $table->id();

                $table->string(
                    column: 'source_currency',
                    length: 3
                );
                $table->string(
                    column: 'target_currency',
                    length: 3
                );
                $table->decimal(
                    column: 'rate',
                    total: 10,
                    places: 6
                );
                $table->timestamp(column: 'fetched_at')->nullable();
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(table: 'exchange_rates');
    }
};
