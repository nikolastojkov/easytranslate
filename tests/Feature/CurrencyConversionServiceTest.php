<?php

namespace Tests\Feature\Services;

use App\Services\CurrencyConversionService;
use App\Contracts\CurrencyConversionContract;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CurrencyConversionServiceTest extends TestCase
{
    protected $service;
    protected $repositoryMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->repositoryMock = $this->createMock(originalClassName: CurrencyConversionContract::class);
        $this->service = new CurrencyConversionService(repository: $this->repositoryMock);
    }

    #[Test]
    public function it_performs_successful_currency_conversion(): void
    {
        Http::fake(callback: [
            'http://data.fixer.io/api/latest*' => Http::response(body: [
                'success' => true,
                'rates' => ['EUR' => 0.85],
            ]),
        ]);

        $this->repositoryMock
            ->expects($this->once())
            ->method('persistCurrencyConversion')
            ->willReturn(1);

        $response = $this->service->convertCurrency(
            sourceCurrency: 'USD',
            targetCurrency: 'EUR',
            value: 100
        );

        $data = $response->getData(assoc: true);

        $this->assertEquals(expected: true, actual: $data['success']);
        $this->assertEquals(expected: 'USD', actual: $data['data']['source_currency']);
        $this->assertEquals(expected: 'EUR', actual: $data['data']['target_currency']);
        $this->assertEquals(expected: 100, actual: $data['data']['value']);
        $this->assertEquals(expected: 85, actual: $data['data']['converted_value']);
        $this->assertEquals(expected: 0.85, actual: $data['data']['rate']);
        $this->assertEquals(expected: 1, actual: $data['conversion_request_id']);
    }

    #[Test]
    public function it_handles_api_failure(): void
    {
        Http::fake(callback: [
            'http://data.fixer.io/api/latest*' => Http::response(body: [], status: 500),
        ]);

        $response = $this->service->convertCurrency(
            sourceCurrency: 'USD',
            targetCurrency: 'EUR',
            value: 100
        );

        $data = $response->getData(assoc: true);

        $this->assertArrayHasKey(key: 'error', array: $data);
        $this->assertEquals(expected: 'Unable to fetch exchange rate.', actual: $data['error']);
    }

    #[Test]
    public function it_uses_cache_to_retrieve_exchange_rate(): void
    {
        Cache::shouldReceive('remember')
            ->once()
            ->andReturn(1.5);

        $this->repositoryMock
            ->expects($this->once())
            ->method('persistCurrencyConversion')
            ->willReturn(2); // Simulate database persistence

        $response = $this->service->convertCurrency(
            sourceCurrency: 'USD',
            targetCurrency: 'EUR',
            value: 100
        );

        $data = $response->getData(assoc: true);

        $this->assertEquals(expected: true, actual: $data['success']);
        $this->assertEquals(expected: 150, actual: $data['data']['converted_value']);
    }
}
