<?php

namespace DbStressmit;

/**
 * Jembatan Otomatis (Auto-Pilot) khusus ekosistem Laravel.
 * Dirancang dengan sistem pengaman ketat agar tidak merusak platform non-Laravel.
 */
class StressmitServiceProvider
{
    /**
     * Menyimpan aplikasi container Laravel jika tersedia.
     * 
     * @var mixed
     */
    protected $app;

    /**
     * Constructor standar yang dibutuhkan oleh internal framework Laravel.
     * 
     * @param mixed $app
     */
    public function __construct($app = null)
    {
        $this->app = $app;
    }

    /**
     * Register komponen ke dalam aplikasi.
     */
    public function register(): void
    {
        // Fitur registrasi opsional jika dibutuhkan di masa depan
    }

    /**
     * Bootstrap data query listener secara otomatis.
     */
    public function boot(): void
    {
        // 1. PENGAMAN UTAMA ALL PLATFORMS
        if (!class_exists('\Illuminate\Support\Facades\DB') || !class_exists('\Illuminate\Support\Facades\Log')) {
            return;
        }

        // 2. DETEKSI LINGKUNGAN (Menggunakan \app() dengan backslash global)
        $isLocal = false;
        if (function_exists('\app')) {
            $isLocal = \app()->environment('local');
        }

        if ($isLocal) {
            // 3. SAKLAR OTOMATIS (LIVE QUERY LISTENER)
            \Illuminate\Support\Facades\DB::listen(function ($query) {
                $sql = $query->sql;
                $bindings = $query->bindings;
                $timeMs = $query->time;

                $hasilAudit = Stressmit::analyze($sql, $bindings, $timeMs);

                if (!$hasilAudit['security_report']['is_safe'] || $timeMs > 100.0) {
                    // Menggunakan call_user_func untuk meredam komplain Intelephense VS Code
                    if (class_exists('\Illuminate\Support\Facades\Log')) {
                        \Illuminate\Support\Facades\Log::warning('[DB-STRESSMIT ALERT] Terdeteksi anomali pada query Laravel Anda!', [
                            'query' => $hasilAudit['query'],
                            'risk_score' => $hasilAudit['security_report']['risk_score'] . '/100',
                            'execution_time' => $timeMs . ' ms',
                            'security_issues' => $hasilAudit['security_report']['issues'],
                            'checked_at' => $hasilAudit['checked_at']
                        ]);
                    }
                }
            });
        }
    }
}