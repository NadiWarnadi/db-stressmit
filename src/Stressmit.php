<?php

namespace DbStressmit;

use PDO;
use PDOException;

class Stressmit
{
    /**
     * Otak Utama Analisis Query (Heuristic Security & Risk Scoring)
     * Bisa berjalan secara mandiri (Agnostik/All Platform).
     *
     * @param string $query Teks SQL yang diuji
     * @param array $params Parameter query (bindings) jika ada
     * @param float $forcedTimeMs Waktu simulasi jika tidak terhubung ke DB asli
     * @return array Struktur data laporan standar internasional
     */
    public static function analyze(string $query, array $params = [], float $forcedTimeMs = 0.0): array
    {
        $query = trim($query);
        $isSafe = true;
        $securityIssues = [];
        $dangerScore = 0;

        // --- 1. MODERN HEURISTIC SECURITY AUDIT ---
        
        // Pola A: Deteksi Logika Pemutus Taut (Logical Tautology / Bypass)
        // Contoh: ' OR '1'='1 atau " OR 1=1
        if (preg_match('/\'\s*OR\s+\'?[0-9a-z_]+\'?\s*=\s*\'?[0-9a-z_]+\'?|\"\s*OR\s+\"?[0-9a-z_]+\"?\s*=\s*\"?[0-9a-z_]+\"?/i', $query)) {
            $dangerScore += 45;
            $securityIssues[] = 'HIGH RISK: Terdeteksi pola manipulasi logika bypass (Logical Tautology OR 1=1).';
        }

        // Pola B: Deteksi Penyisipan Kueri Berlapis (Stacked Queries)
        // Hacker menyisipkan ';' untuk menghapus atau mengubah tabel di tengah jalan
        if (preg_match('/;\s*(DROP|DELETE|ALTER|UPDATE|TRUNCATE)/i', $query)) {
            $dangerScore += 50;
            $securityIssues[] = 'CRITICAL: Terdeteksi upaya penyisipan kueri destruktif (Stacked Queries).';
        }

        // Pola C: Deteksi Karakter Komentar Pemutus Sintaks
        if (preg_match('/(--|#|\/\*)/', $query)) {
            $dangerScore += 20;
            $securityIssues[] = 'MEDIUM RISK: Ditemukan karakter komentar SQL (--,#,/*) yang biasa dipakai memotong sintaks autentikasi.';
        }

        // Pola D: Analisis Konteks Parameter (Anti AI-Hacker Fuzzing)
        foreach ($params as $key => $value) {
            if (is_string($value)) {
                if (preg_match('/(UNION\s+SELECT|SELECT\s+.*\s+FROM)/i', $value)) {
                    $dangerScore += 45;
                    $securityIssues[] = "HIGH RISK: Parameter [{$key}] terindikasi membawa muatan injeksi (Union-Based Injection).";
                }
            }
        }

        // Penentuan Ambang Batas Keamanan Standar Internasional
        if ($dangerScore >= 40) {
            $isSafe = false;
        }

        // --- 2. PERFORMA & BENCHMARK SIMULASI ---
        // Jika tidak dijalankan pada database riil, hitung beban performa secara teoritis
        $estimatedTime = $forcedTimeMs;
        if ($estimatedTime === 0.0) {
            $estimatedTime = rand(5, 15) / 100; // Waktu dasar kueri optimal (~0.1ms)
            if (stripos($query, 'join') !== false) $estimatedTime += 12.5;
            if (stripos($query, 'like') !== false) $estimatedTime += 8.2;
        }

        return [
            'package' => 'warnadi/db-stressmit',
            'query' => $query,
            'execution_time_ms' => $estimatedTime,
            'security_report' => [
                'risk_score' => min($dangerScore, 100),
                'is_safe' => $isSafe,
                'issues' => $securityIssues
            ],
            'checked_at' => date('Y-m-d H:i:s')
        ];
    }

    /**
     * Fitur Eksekusi Nyata menggunakan Koneksi PDO Riil (Laravel, Symfony, Native DB, dll)
     */
    public static function executeAndAudit(PDO $pdo, string $query, array $params = []): array
    {
        $startTime = microtime(true);
        $success = true;
        $errorMessage = null;

        try {
            $stmt = $pdo->prepare($query);
            $stmt->execute($params);
        } catch (PDOException $e) {
            $success = false;
            $errorMessage = $e->getMessage();
        }

        $endTime = microtime(true);
        $realExecutionTimeMs = ($endTime - $startTime) * 1000;

        // Gabungkan hasil eksekusi riil dengan analisis cerdas Heuristic kita
        $analysis = self::analyze($query, $params, $realExecutionTimeMs);
        
        $analysis['status'] = $success ? 'SUCCESS' : 'SQL ERROR';
        if ($errorMessage) {
            $analysis['database_error'] = $errorMessage;
        }

        return $analysis;
    }
}