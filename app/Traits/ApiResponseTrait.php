<?php

namespace App\Traits;

trait ApiResponseTrait
{
    public function successResponse($data = null, $message = "Success!", $code = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'message' => $message,
        ], $code);
    }

    public function errorResponse($message = "Error sistem, hubungi admin WA : +6283818213645", $error = "response timeout!", $code = 400)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error' => $error,
        ], $code);
    }
}
