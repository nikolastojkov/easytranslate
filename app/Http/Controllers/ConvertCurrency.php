<?php

namespace App\Http\Controllers;

use App\Services\CurrencyConversionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

final class ConvertCurrency extends Controller
{
    public function __construct(private CurrencyConversionService $service)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate(rules: [
            'source_currency' => 'required|string|size:3',
            'target_currency' => 'required|string|size:3',
            'value' => 'required|numeric|min:0.01',
            'state' => 'string'
        ]);

        $responseObject = $this->service->convertCurrency(
            sourceCurrency: strtoupper(string: $validated['source_currency']),
            targetCurrency: strtoupper(string: $validated['target_currency']),
            value: $validated['value']
        );

        if (isset($validated['state'])) {
            $data = $responseObject->getData(assoc: true);
            $data['state'] = $validated['state'];
            $responseObject->setData(data: $data);
        }

        return $responseObject;
    }

}
