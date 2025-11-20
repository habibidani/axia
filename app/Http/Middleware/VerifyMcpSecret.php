<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyMcpSecret
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('services.mcp.shared_secret');

        if (!$secret) {
            \Log::error('MCP shared secret not configured');
            return response()->json([
                'error' => 'Service configuration error',
            ], 500);
        }

        // Check for secret in header
        $providedSecret = $request->header('X-MCP-Secret');
        
        if (!$providedSecret) {
            \Log::warning('MCP request missing X-MCP-Secret header', [
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);
            
            return response()->json([
                'error' => 'Authentication required',
            ], 401);
        }

        // Verify secret (use hash_equals to prevent timing attacks)
        if (!hash_equals($secret, $providedSecret)) {
            \Log::warning('MCP request with invalid secret', [
                'ip' => $request->ip(),
                'path' => $request->path(),
            ]);
            
            return response()->json([
                'error' => 'Invalid authentication credentials',
            ], 403);
        }

        // Optional: Verify HMAC signature for additional security
        if ($signature = $request->header('X-MCP-Signature')) {
            $body = $request->getContent();
            $expectedSignature = hash_hmac('sha256', $body, $secret);
            
            if (!hash_equals($expectedSignature, $signature)) {
                \Log::warning('MCP request with invalid signature', [
                    'ip' => $request->ip(),
                    'path' => $request->path(),
                ]);
                
                return response()->json([
                    'error' => 'Invalid request signature',
                ], 403);
            }
        }

        return $next($request);
    }
}
