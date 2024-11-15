<?php

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Route;

Route::fallback(action: function (): JsonResponse {
    return response()->json(
        data: ['message' => 'Web routes are disabled'],
        status: 404
    );
});
