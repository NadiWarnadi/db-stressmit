<?php

namespace DbStressmit;

/**
 * Jembatan Otomatis Khusus Ekosistem Laravel (Mendukung Laravel 9, 10, 11, hingga 12+).
 */
class StressmitServiceProvider
{
    /**
     * @var mixed
     */
    protected $app;

    /**
     * @param mixed $app
     */
    public function __construct($app = null)
    {
        $this->app = $app;
    }

    public function register(): void
    {
        // Bind Core Engine ke dalam Service Container Laravel jika tersedia
        if (function_exists('\app') && class_exists('\Illuminate\Support\Facades\App')) {
            \app()->singleton('stressmit', function () {
                return new Stressmit();
            });
        }
    }

    public function boot(): void
    {
        // PENGAMAN UTAMA SEBELUM LOAD FRAMEWORK INTERNALS
        if (!class_exists('\Illuminate\Support\Facades\DB') || !class_exists('\Illuminate\Support\Facades\Log')) {
            return;
        }

        // DETEKSI LINGKUNGAN LOCAL SECARA EFISIEN
        $isLocal = false;
        if (function_exists('\app')) {
            $isLocal = \app()->environment('local');
        }

        if ($isLocal) {
            // 1. DAFTARKAN MIDDLEWARE OTOMATIS (Mencegah SQLi dari Request Data)
            if (function_exists('\app') && \app()->bound('router')) {
                $router = \app('router');
                // Daftarkan global middleware ke grup 'web' dan 'api' secara dinamis
                if (method_exists($router, 'pushMiddlewareToGroup')) {
                    $router->pushMiddlewareToGroup('web', \DbStressmit\Middleware\LaravelStressmitMiddleware::class);
                    $router->pushMiddlewareToGroup('api', \DbStressmit\Middleware\LaravelStressmitMiddleware::class);
                }
            }

            // 2. LIVE DB QUERY LISTENER (Mendukung Multi-versi Laravel 9-12)
            \Illuminate\Support\Facades\DB::listen(function ($query) {
                $sql = $query->sql;

                // Fast bypass kueri internal
                if (strlen($sql) < 15 || stripos($sql, 'select * from information_schema') !== false) {
                    return;
                }

                $bindings = $query->bindings;
                $timeMs = $query->time;

                $hasilAudit = Stressmit::analyze($sql, $bindings, $timeMs);

                if (!$hasilAudit['security_report']['is_safe'] || $timeMs > 100.0) {
                    $logClass = '\Illuminate\Support\Facades\Log';
                    $logClass::warning('[DB-STRESSMIT ALERT] Anomali query terdeteksi pada Runtime Laravel!', [
                        'query' => $hasilAudit['query'],
                        'risk_score' => $hasilAudit['security_report']['risk_score'] . '/100',
                        'execution_time' => $timeMs . ' ms',
                        'security_issues' => $hasilAudit['security_report']['issues']
                    ]);
                }
            });
        }
    }
}