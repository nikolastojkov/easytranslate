<?php

namespace App\Http\Controllers;

use App\Models\CurrencyConversion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class ConvertCurrency extends Controller
{
    public function __invoke(Request $request): JsonResponse
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
            ttl: now()->addSeconds(value: 10),
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
        $apiKey = env(key: 'FIXER_API_KEY');
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

            return $rate;
        }

        return null;
    }
}
