<?php

namespace Tests\Unit\Repositories;

use App\Models\CurrencyConversion;
use App\Repositories\CurrencyConversionRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CurrencyConversionRepositoryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_persists_currency_conversion_data_in_the_database(): void
    {
        $repository = app(abstract: CurrencyConversionRepository::class);

        $repository->persistCurrencyConversion(
            sourceCurrency: 'USD',
            targetCurrency: 'EUR',
            value: 100,
            convertedValue: 120,
            rate: 1.2
        );

        $this->assertDatabaseCount(table: 'currency_conversions', count: 1);
    }

    /** @test */
    public function it_stores_correct_conversion_data_in_the_database(): void
    {
        $repository = app(abstract: CurrencyConversionRepository::class);

        $repository->persistCurrencyConversion(
            sourceCurrency: 'USD',
            targetCurrency: 'EUR',
            value: 100,
            convertedValue: 120,
            rate: 1.2
        );

        $conversionRequest = CurrencyConversion::first();

        $this->assertNotNull(actual: $conversionRequest);
        $this->assertEquals(expected: 'USD', actual: $conversionRequest->source_currency);
        $this->assertEquals(expected: 'EUR', actual: $conversionRequest->target_currency);
        $this->assertEquals(expected: 100, actual: $conversionRequest->value);
        $this->assertEquals(expected: 120, actual: $conversionRequest->converted_value);
        $this->assertEquals(expected: 1.2, actual: $conversionRequest->rate);
    }
}
