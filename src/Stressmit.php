<?php

namespace DbStressmit;

class Stressmit
{
    /**
     * Menguji keamanan dan performa dari sebuah SQL Query.
     *
     * @param string $query Teks SQL query yang ingin diuji.
     * @param array $options Opsi tambahan seperti jumlah baris data (rows) simulasi.
     * @return array Hasil analisis stress test dan audit keamanan.
     */
    public static function testQuery(string $query, array $options = []): array
    {
        $rowsSimulated = $options['rows'] ?? 100;
        $startTime = microtime(true);

        // --- 1. AUDIT KEAMANAN (DETEKSI SQL INJECTION) ---
        $securityIssues = [];
        $isSafe = true;

        // Cek apakah user menggabungkan variabel langsung lewat string (raw query vulnerability)
        if (preg_match('/\'\s*status\s*\'|\'\s*OR\s*\'1\'\s*=\s*\'1/i', $query) || preg_match('/WHERE\s+\w+\s*=\s*\'?\$[a-zA-O0-9_]+/i', $query)) {
            $securityIssues[] = 'CRITICAL: Terdeteksi potensi celah SQL Injection! Jangan menggabungkan variabel langsung ke dalam string query.';
            $isSafe = false;
        }

        // Cek penggunaan Prepared Statements (Standar Keamanan Internasional)
        if (strpos($query, '?') === false && strpos($query, ':') === false && preg_match('/WHERE/i', $query)) {
            $securityIssues[] = 'WARNING: Query menggunakan input dinamis tetapi tidak mendeteksi Placeholder (? atau :name). Disarankan menggunakan Prepared Statements.';
        }


        // --- 2. SIMULASI BEBAN PERFORMA (STRESS TEST) ---
        $baseDelay = rand(10, 40) / 1000; 
        
        if (stripos($query, 'join') !== false) {
            $baseDelay += 0.18; // JOIN bikin query lebih berat
        }
        if (stripos($query, 'like') !== false) {
            $baseDelay += 0.12; // LIKE %string% butuh full-table scan jika tanpa index
        }
        if (stripos($query, 'group by') !== false || stripos($query, 'order by') !== false) {
            $baseDelay += 0.07; // Sorting butuh resource tambahan
        }

        // Makin banyak data yang disimulasikan, makin lambat
        $baseDelay += ($rowsSimulated * 0.0003);

        // Jalankan jeda simulasi
        usleep((int)($baseDelay * 1000000));

        $endTime = microtime(true);
        $executionTimeMs = ($endTime - $startTime) * 1000;


        // --- 3. ANALISIS PERFORMA & REKOMENDASI ---
        $status = 'OPTIMAL';
        $recommendations = [];

        if ($executionTimeMs > 200) {
            $status = 'SLOW QUERY';
            if (stripos($query, 'join') !== false) {
                $recommendations[] = 'Optimasi JOIN: Pastikan kolom ON (Foreign Key) sudah memiliki indeks (INDEX) di database.';
            }
            if (stripos($query, '*') !== false) {
                $recommendations[] = 'Optimasi Memori: Hindari "SELECT *". Ambil kolom yang dibutuhkan saja (misal: SELECT id, name) untuk menghemat RAM server.';
            }
        }

        if (empty($recommendations)) {
            $recommendations[] = 'Performa query aman dan efisien.';
        }

        // --- 4. OUTPUT HASIL ---
        return [
            'package' => 'warnadi/db-stressmit',
            'version' => '1.1.0',
            'timestamp' => date('Y-m-d H:i:s'),
            'summary' => [
                'status' => $status,
                'is_security_passed' => $isSafe,
                'execution_time_ms' => round($executionTimeMs, 2),
                'rows_tested' => $rowsSimulated
            ],
            'security_report' => [
                'issues_found' => count($securityIssues),
                'details' => $securityIssues
            ],
            'performance_report' => [
                'suggestions' => $recommendations
            ]
        ];
    }
}