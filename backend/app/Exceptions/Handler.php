<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Log;

class Handler extends ExceptionHandler
{
    protected $dontReport = [
        // List of exception types that should not be reported
    ];

    public function register()
    {
        $this->reportable(function (Throwable $e) {
            Log::error($e);
        });
    }

    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {
            $response = [
                'error' => 'Internal Server Error',
                'message' => $e->getMessage(),
            ];

            if (config('app.debug')) {
                $response['trace'] = $e->getTrace();
            }

            return response()->json($response, 500);
        }

        return parent::render($request, $e);
    }
}
