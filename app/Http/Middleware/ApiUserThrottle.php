<?php

namespace App\Http\Middleware;

use Illuminate\Routing\Middleware\ThrottleRequests;

class ApiUserThrottle extends ThrottleRequests
{
    protected function buildResponse($key, $maxAttempts)
    {
        $response = response()->json([
            'error' => 'Rate limit exceeded'
        ], 429);

        return $this->addHeaders(
            $response,
            $maxAttempts,
            $this->limiter()->remaining($key, $maxAttempts),
            $this->getTimeUntilNextRetry($key)
        );
    }

}
