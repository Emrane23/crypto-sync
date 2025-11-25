<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Symfony\Component\HttpFoundation\Response;

class ApiUserThrottle extends ThrottleRequests
{

    public function handle($request, Closure $next, $maxAttempts = 60, $decayMinutes = 1, $prefix = '')
    {
        $key = $prefix.$this->resolveRequestSignature($request);

        $maxAttempts = $this->resolveMaxAttempts($request, $maxAttempts);

        if ($this->limiter->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildJsonResponse($key, $maxAttempts);
        }

        $this->limiter->hit($key, $decayMinutes * 60);

        $response = $next($request);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->calculateRemainingAttempts($key, $maxAttempts)
        );
    }

    protected function buildJsonResponse($key, $maxAttempts)
    {
        $retryAfter = $this->getTimeUntilNextRetry($key);
        $remaining = $this->limiter->remaining($key, $maxAttempts);

        $response = response()->json([
            'message' => 'Too Many Attempts',
            'error' => 'Rate limit exceeded',
            'retry_after' => $retryAfter,
            'remaining' => max(0, $remaining),
        ], 429);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $remaining,
            $retryAfter
        );
    }

    protected function addHeaders(Response $response, $maxAttempts, $remainingAttempts, $retryAfter = null)
    {
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => $remainingAttempts,
        ]);

        if (! is_null($retryAfter)) {
            $response->headers->add([
                'Retry-After' => $retryAfter,
                'X-RateLimit-Reset' => now()->getTimestamp() + $retryAfter,
            ]);
        }

        return $response;
    }
}