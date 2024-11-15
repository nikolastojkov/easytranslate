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
            table: 'currency_conversions',
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
                    column: 'value',
                    total: 15,
                    places: 2
                );
                $table->decimal(
                    column: 'converted_value',
                    total: 15,
                    places: 2
                )->nullable();
                $table->decimal(
                    column: 'rate',
                    total: 10,
                    places: 6
                )->nullable();
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(table: 'currency_conversions');
    }
};
