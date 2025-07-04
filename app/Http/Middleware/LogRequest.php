<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogRequest
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $log = sprintf(
            '[%s] %s %s | User: %s | IP: %s',
            now()->toDateTimeString(),
            $request->method(),
            $request->fullUrl(),
            $user ? $user->id : '-',
            $request->ip()
        );
        Log::channel('api_activity')->info($log);
        return $next($request);
    }
}
