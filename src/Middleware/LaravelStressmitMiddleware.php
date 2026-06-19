<?php

namespace DbStressmit\Middleware;

use Closure;
use DbStressmit\Stressmit;

class LaravelStressmitMiddleware
{
    /**
     * Handle incoming request data.
     *
     * @param  mixed  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!method_exists($request, 'all')) {
            return $next($request);
        }

        $allInputs = $request->all();

        foreach ($allInputs as $key => $value) {
            if (is_string($value) && strlen($value) > 5) {
                $audit = Stressmit::analyze("SELECT * FROM dual WHERE input = '{$value}'");

                if (!$audit['security_report']['is_safe']) {
                    // Gunakan resolver dinamis untuk menghindari komplain 'Undefined function response'
                    if (function_exists('\response')) {
                        return \response()->json([
                            'error' => 'Security Blocked by DB-Stressmit',
                            'message' => 'Payload input terindikasi serangan SQL Injection.',
                            'detected_issues' => $audit['security_report']['issues'],
                            'risk_score' => $audit['security_report']['risk_score'] . '/100'
                        ], 403);
                    }
                    
                    // Fallback jika berjalan di platform hybrid
                    header('Content-Type: application/json', true, 403);
                    echo json_encode(['error' => 'Security Blocked by DB-Stressmit']);
                    exit;
                }
            }
        }

        return $next($request);
    }
}