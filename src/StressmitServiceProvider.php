<?php

namespace DbStressmit;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Jembatan Otomatis Khusus Ekosistem Laravel (Mendukung Laravel 10, 11, 12, hingga 13+).
 * Wajib meng-extend ServiceProvider bawaan Laravel agar aman saat proses package:discover.
 */
class StressmitServiceProvider extends ServiceProvider
{
    /**
     * Daftarkan Core Engine ke dalam Service Container Laravel.
     * Method ini otomatis dipanggil oleh Laravel, variabel $this->app sudah tersedia dari class induk.
     */
    public function register(): void
    {
        $this->app->singleton('stressmit', function () {
            return new Stressmit();
        });
    }

    /**
     * Proses inisialisasi modul setelah semua service terdaftar.
     */
    public function boot(): void
    {
        // 1. DETEKSI LINGKUNGAN LOCAL SECARA AMAN
        // Menggunakan properti $this->app bawaan provider yang kompatibel di semua versi Laravel
        if (!$this->app->environment('local')) {
            return;
        }

        // 2. DAFTARKAN MIDDLEWARE OTOMATIS
        // Laravel 11+ mengubah arsitektur kernel, namun properti 'router' di container tetap kompatibel
        if ($this->app->bound('router')) {
            $router = $this->app->make('router');
            
            if (method_exists($router, 'pushMiddlewareToGroup')) {
                $router->pushMiddlewareToGroup('web', \DbStressmit\Middleware\LaravelStressmitMiddleware::class);
                $router->pushMiddlewareToGroup('api', \DbStressmit\Middleware\LaravelStressmitMiddleware::class);
            }
        }

        // 3. LIVE DB QUERY LISTENER (Kompatibel Laravel 10 - 13+)
        DB::listen(function ($query) {
            $sql = $query->sql;

            // Fast bypass kueri internal untuk efisiensi runtime
            if (strlen($sql) < 15 || stripos($sql, 'information_schema') !== false) {
                return;
            }

            $bindings = $query->bindings;
            $timeMs = $query->time;

            // Memanggil core engine paket Anda untuk menganalisis query
            $hasilAudit = Stressmit::analyze($sql, $bindings, $timeMs);

            // Jika query tidak aman atau performa lambat (> 100ms)
            if (!$hasilAudit['security_report']['is_safe'] || $timeMs > 100.0) {
                Log::warning('[DB-STRESSMIT ALERT] Anomali query terdeteksi pada Runtime Laravel!', [
                    'query'            => $hasilAudit['query'],
                    'risk_score'       => $hasilAudit['security_report']['risk_score'] . '/100',
                    'execution_time'   => $timeMs . ' ms',
                    'security_issues'  => $hasilAudit['security_report']['issues']
                ]);
            }
        });
    }
}
