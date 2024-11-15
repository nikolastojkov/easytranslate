<?php

namespace App\Http\Controllers;

use App\Models\CurrencyConversion;
use App\Models\ExchangeRate;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class CurrencyConversionController extends Controller
{
    public function convert(Request $request): JsonResponse
    {
        $validated = $request->validate(rules: [
            'source_currency' => 'required|string|size:3',
            'target_currency' => 'required|string|size:3',
            'value' => 'required|numeric|min:0.01',
        ]);

        $sourceCurrency = strtoupper(string: $validated['source_currency']);
        $targetCurrency = strtoupper(string: $validated['target_currency']);
        $value = $validated['value'];

        $cacheKey = "rate_{$sourceCurrency}_{$targetCurrency}";
        $rate = Cache::remember(
            key: $cacheKey,
            ttl: now()->addMinutes(value: 60),
            callback: function () use ($sourceCurrency, $targetCurrency): mixed {
                return $this->fetchExchangeRate(
                    sourceCurrency: $sourceCurrency,
                    targetCurrency: $targetCurrency
                );
            }
        );

        if (!$rate) {
            return response()->json(
                data: ['error' => 'Unable to fetch exchange rate.'],
                status: 500
            );
        }

        $convertedValue = $value * $rate;
        $conversionRequest = CurrencyConversion::create(attributes: [
            'source_currency' => $sourceCurrency,
            'target_currency' => $targetCurrency,
            'value' => $value,
            'converted_value' => $convertedValue,
            'rate' => $rate,
        ]);

        return response()->json(data: [
            'success' => true,
            'data' => [
                'source_currency' => $sourceCurrency,
                'target_currency' => $targetCurrency,
                'value' => $value,
                'converted_value' => $convertedValue,
                'rate' => $rate,
            ],
            'conversion_request_id' => $conversionRequest->id,
        ]);
    }

    private function fetchExchangeRate($sourceCurrency, $targetCurrency): mixed
    {
        $exchangeRate = ExchangeRate::where(
            column: 'source_currency',
            operator: $sourceCurrency
        )->where(
                column: 'target_currency',
                operator: $targetCurrency
            )->first();

        if ($exchangeRate && $exchangeRate->fetched_at > now()->subMinutes(value: 60)) {
            return $exchangeRate->rate;
        }

        $apiKey = config(key: 'fixer.key');
        $response = Http::get(
            url: "http://data.fixer.io/api/latest",
            query: [
                'access_key' => $apiKey,
                'base' => $sourceCurrency,
                'symbols' => $targetCurrency,
            ]
        );

        if ($response->successful() && isset($response['rates'][$targetCurrency])) {
            $rate = $response['rates'][$targetCurrency];

            ExchangeRate::updateOrCreate(
                attributes: [
                    'source_currency' => $sourceCurrency,
                    'target_currency' => $targetCurrency
                ],
                values: [
                    'rate' => $rate,
                    'fetched_at' => now()
                ]
            );

            return $rate;
        }

        return null;
    }
}
