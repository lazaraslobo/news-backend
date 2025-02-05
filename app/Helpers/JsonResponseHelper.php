<?php

namespace App\Helpers;
use Illuminate\Http\JsonResponse;

class JsonResponseHelper
{
    public static function success(array $data = [], string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => $status,
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public static function error(string $message, int $status = 500): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], $status);
    }
}
