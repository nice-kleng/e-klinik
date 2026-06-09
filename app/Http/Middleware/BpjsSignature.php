<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class BpjsSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $signature = $request->header('X-BPJS-Signature');
        $timestamp = $request->header('X-BPJS-Timestamp');
        $consId = $request->header('X-BPJS-ConsID');

        if (!$signature || !$timestamp || !$consId) {
            Log::warning('BPJS callback missing required headers', [
                'has_signature' => !is_null($signature),
                'has_timestamp' => !is_null($timestamp),
                'has_cons_id' => !is_null($consId),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Missing required BPJS headers',
            ], 401);
        }

        $secretKey = config('bpjs.secret_key');
        $computedSignature = hash_hmac('sha256', $consId . '&' . $timestamp, $secretKey);

        if (!hash_equals($computedSignature, $signature)) {
            Log::warning('BPJS callback signature mismatch');

            return response()->json([
                'success' => false,
                'message' => 'Invalid signature',
            ], 401);
        }

        return $next($request);
    }
}
