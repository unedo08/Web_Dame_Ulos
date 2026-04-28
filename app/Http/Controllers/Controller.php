<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

abstract class Controller
{
    protected function checkAuth()
    {
        if (!Auth::check()) {
            return response()->json([
                'code'    => 401,
                'message' => 'Unauthorized. Please login.',
                'data'    => null
            ], 401);
        }
        return null;
    }

    protected function ok($data = null, string $message = 'Success', int $code = 200)
    {
        return response()->json([
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }

    protected function created($data = null, string $message = 'Created successfully')
    {
        return $this->ok($data, $message, 201);
    }

    protected function notFound(string $message = 'Data not found')
    {
        return response()->json([
            'code'    => 404,
            'message' => $message,
            'data'    => null,
        ], 404);
    }

    protected function fail(string $message, int $code = 500, $data = null)
    {
        return response()->json([
            'code'    => $code,
            'message' => $message,
            'data'    => $data,
        ], $code);
    }
}
