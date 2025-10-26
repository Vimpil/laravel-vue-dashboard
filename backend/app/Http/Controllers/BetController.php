<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Bet;

class BetController extends Controller
{
    public function store(Request $request)
    {
        Log::info('BetController@store start', [
            'headers' => $request->headers->all(),
            'body' => $request->all(),
        ]);

        $validated = $request->validate([
            'event_id' => 'required|integer',
            'outcome' => 'required|string',
            'amount' => 'required|numeric|min:1',
        ]);

        $validated['amount'] = (float)$validated['amount'];

        $user = $request->user();

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $idempotencyKey = $request->header('Idempotency-Key') ?: Str::uuid()->toString();

        $existing = Bet::where('idempotency_key', $idempotencyKey)->first();
        if ($existing) {
            return response()->json($existing, 200);
        }

        try {
            DB::beginTransaction();

            $user = User::where('id', $user->id)->lockForUpdate()->first();
            Log::info('User fetched', ['user_id' => $user?->id, 'balance' => $user?->balance]);

            if ((float)$user->balance < $validated['amount']) {
                // Log fraud attempt outside of transaction to ensure it is not rolled back
                DB::commit(); // Commit any open transaction to avoid nested transaction issues

                \App\Models\FraudLog::create([
                    'ip' => $request->ip(),
                    'user_id' => $user->id,
                    'reason' => 'Insufficient funds',
                    'payload' => json_encode([
                        'balance' => $user->balance,
                        'attempted_amount' => $validated['amount'],
                    ]),
                ]);

                Log::info('Insufficient funds', ['balance' => $user->balance, 'amount' => $validated['amount']]);
                return response()->json(['error' => 'Insufficient funds'], 400);
            }

            $user->balance = (float)$user->balance - $validated['amount'];
            Log::info('Attempting to save user with new balance', ['user_id' => $user->id, 'new_balance' => $user->balance]);
            $user->save();
            Log::info('User saved successfully.', ['user_id' => $user->id, 'balance_after_save' => $user->balance]);

            Log::info('Attempting to create bet with data', [
                'user_id' => $user->id,
                'event_id' => $validated['event_id'],
                'outcome' => $validated['outcome'],
                'amount' => $validated['amount'],
                'idempotency_key' => $idempotencyKey,
            ]);

            $bet = Bet::create([
                'user_id' => $user->id,
                'event_id' => $validated['event_id'],
                'outcome' => $validated['outcome'],
                'amount' => $validated['amount'],
                'idempotency_key' => $idempotencyKey,
                'status' => 'placed'
            ]);

            Log::info('Bet created successfully.', ['bet_id' => $bet->id]);

            DB::commit();
            Log::info('Transaction committed.');
            Log::info('Bet successfully created: ', $bet->toArray());
            return response()->json($bet, 201);

        } catch (\Throwable $e) {
            DB::rollBack();

            // ⚠️ ВНИМАНИЕ: Это временное решение для отладки.
            // Это раскрывает детали реализации и должно быть удалено в продакшене.

            Log::error('Exception class: ' . get_class($e));
            Log::error('Message: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());

            return response()->json([
                'error' => 'Internal Server Error',
                'exception_class' => get_class($e), // <-- ДОБАВЛЕНО
                'message' => $e->getMessage(),      // <-- ДОБАВЛЕНО
                // 'trace' => $e->getTrace()         // Можно добавить для полной трассировки
            ], 500);
        }
    }
}
