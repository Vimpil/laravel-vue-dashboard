<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\FraudLog;
use Illuminate\Support\Facades\Log;

class VerifySignature
{
    public function handle(Request $request, Closure $next)
    {
        $userId = $request->header('X-User-Id');
        $signature = $request->header('X-Signature');
        $timestamp = (int) $request->header('X-Timestamp'); // <-- приведение к int

        $missing = [];
        if (!$userId)    $missing[] = 'X-User-Id';
        if (!$signature) $missing[] = 'X-Signature';
        if (!$timestamp) $missing[] = 'X-Timestamp';

        if (!empty($missing)) {
            return response()->json([
                'message' => 'Missing headers',
                'missing' => $missing
            ], 400);
        }

        $user = User::find($userId);
        if (!$user || !$user->api_key) {
            return response()->json(['message' => 'Invalid user'], 401);
        }

        // Проверка таймстампа ±5 минут
        if (abs(time() - $timestamp) > 300) {
            return response()->json(['message' => 'Request expired'], 401);
        }

        $payload = $request->getContent();
        $expected = hash_hmac('sha256', $payload.$timestamp, $user->api_key);

        // Log the payload for debugging
        Log::info('Payload: ' . json_encode($payload));

        // Log detailed information for debugging signature mismatch
        Log::info('--- VerifySignature ---', [
            'payload' => $request->getContent(),
            'timestamp' => $timestamp,
            'expected' => hash_hmac('sha256', $request->getContent() . $timestamp, $user->api_key),
            'signature_received' => $signature,
        ]);

        if (!hash_equals($expected, $signature)) {
            FraudLog::create([
                'ip' => $request->ip(),
                'user_id' => $userId,
                'reason' => 'Invalid signature',
                'payload' => json_encode([
                    'expected' => $expected,
                    'received' => $signature,
                ]),
            ]);

            return response()->json([
                'message' => 'Invalid signature',
                'expected' => $expected,
                'received' => $signature,
            ], 401);
        }


        //dd('VerifySignature middleware is running');

        return $next($request);
    }
}
