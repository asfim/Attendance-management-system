<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\ActivityLog;
use Symfony\Component\HttpFoundation\Response;

class LogActivity
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Log after the response is sent (or during the request lifecycle)
        if ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('DELETE') || $request->isMethod('PATCH')) {
            $activity = "{$request->method()} Request to {$request->path()}";
            
            // Clean up request data to avoid logging sensitive information like passwords
            $input = $request->except(['password', 'password_confirmation', '_token']);
            $description = json_encode([
                'input' => $input,
                'status_code' => $response->getStatusCode()
            ]);

            ActivityLog::create([
                'user_id' => Auth::id(),
                'activity' => $activity,
                'description' => $description,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent()
            ]);
        }

        return $response;
    }
}
