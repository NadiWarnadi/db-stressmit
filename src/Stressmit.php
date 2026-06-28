<?php

namespace DbStressmit;

use PDO;
use PDOException;

class Stressmit
{
    // Struktur internal untuk caching pola dynamic di memori
    private static ?array $cachedPatterns = null;

    /**
     * Otak Utama Analisis Query - Versi Ultra Fast & Low Memory
     */
    public static function analyze(string $query, array $params = [], float $forcedTimeMs = 0.0): array
    {
        $query = trim($query);
        $queryLength = strlen($query);

        // --- OPTIMASI 1: EARLY EXIT STRATEGY ---
        // Jika kueri terlalu pendek, hampir pasti tidak mengandung payload SQLi yang valid.
        // Bypass regex untuk menghemat siklus CPU.
        if ($queryLength < 10) {
            return self::generateReport($query, true, 0, [], $forcedTimeMs);
        }

        $isSafe = true;
        $securityIssues = [];
        $dangerScore = 0;

        // --- OPTIMASI 2: BITWISE/FAST STRING MATCHING SEBELUM REGEX ---
        // Pengecekan string murah menggunakan stripos(). Jika tidak ada karakter pemicu,
        // kita bisa melewati beberapa regex sekaligus.
        $hasOr = stripos($query, 'or') !== false;
        $hasUnion = stripos($query, 'union') !== false;
        $hasComment = (strpos($query, '--') !== false || strpos($query, '#') !== false || strpos($query, '/*') !== false);

        // Pola A: Deteksi Logika Pemutus Taut (Hanya jalan jika ada kata 'OR')
        if ($hasOr && preg_match('/\'\s*OR\s+\'?[0-9a-z_]+\'?\s*=\s*\'?[0-9a-z_]+\'?|\"\s*OR\s+\"?[0-9a-z_]+\"?\s*=\s*\"?[0-9a-z_]+\"?/i', $query)) {
            $dangerScore += 45;
            $securityIssues[] = 'HIGH RISK: Terdeteksi pola manipulasi logika bypass (Logical Tautology OR 1=1).';
        }

        // Pola B: Stacked Queries
        if (strpos($query, ';') !== false && preg_match('/;\s*(DROP|DELETE|ALTER|UPDATE|TRUNCATE)/i', $query)) {
            $dangerScore += 50;
            $securityIssues[] = 'CRITICAL: Terdeteksi upaya penyisipan kueri destruktif (Stacked Queries).';
        }

        // Pola C: Komentar
        if ($hasComment) {
            $dangerScore += 20;
            $securityIssues[] = 'MEDIUM RISK: Ditemukan karakter komentar SQL (--,#,/*).';
        }

        // Pola D & E: Time-Based & Error-Based (Digabung dalam satu regex hemat demi menghemat scan token)
        if (preg_match('/(SLEEP\s*\(|WAITFOR\s+DELAY|BENCHMARK\s*\(|pg_sleep\s*\(|EXTRACTVALUE|UPDATEXML|CAST\s*\(.*AS)/i', $query)) {
            $dangerScore += 50;
            $securityIssues[] = 'CRITICAL: Terdeteksi indikasi Advanded SQLi (Time/Error-Based).';
        }

        // Pola G: Union Exploitation (Hanya jalan jika ada kata 'UNION')
        if ($hasUnion && preg_match('/UNION\s+(ALL\s+)?SELECT/i', $query)) {
            $dangerScore += 45;
            $securityIssues[] = 'HIGH RISK: Terdeteksi struktur Union-Based Injection.';
        }

        // --- OPTIMASI 3: LAZY STATIC LAODING (OSV PATTERNS) ---
        // File hanya di-load dari disk 1x saja, kueri ke-2 dan seterusnya langsung mengambil dari RAM static.
        if (self::$cachedPatterns === null) {
            $dynamicPatternsFile = __DIR__ . '/Patterns/compiled.php';
            self::$cachedPatterns = file_exists($dynamicPatternsFile) ? include $dynamicPatternsFile : false;
        }

        if (self::$cachedPatterns && isset(self::$cachedPatterns['dynamic_regex'])) {
            if (preg_match(self::$cachedPatterns['dynamic_regex'], $query)) {
                $dangerScore += 35;
                $securityIssues[] = 'HIGH RISK: Terdeteksi signature serangan berdasarkan database kerentanan OSV.';
            }
        }
        // update versionng test 0.2.1

        // Analisis Konteks Parameter (Anti AI-Hacker Fuzzing)
        if (!empty($params)) {
            foreach ($params as $key => $value) {
                if (is_string($value) && strlen($value) > 5) {
                    if (preg_match('/(UNION\s+(ALL\s+)?SELECT|OR\s+\d+=\d+|SLEEP\s*\()/i', $value)) {
                        $dangerScore += 45;
                        $securityIssues[] = "HIGH RISK: Parameter [{$key}] terindikasi membawa muatan injeksi berbahaya.";
                    }
                }
            }
        }

        if ($dangerScore >= 35) {
            $isSafe = false;
        }

        return self::generateReport($query, $isSafe, min($dangerScore, 100), $securityIssues, $forcedTimeMs);
    }

    /**
     * Helper internal untuk membungkus output kueri (Mengurangi alokasi array berulang)
     */
    private static function generateReport(string $query, bool $isSafe, int $riskScore, array $issues, float $forcedTimeMs): array
    {
        $estimatedTime = $forcedTimeMs;
        if ($estimatedTime === 0.0) {
            $estimatedTime = 0.05; // Menggunakan benchmark flat micro-cost untuk clean query
            if (stripos($query, 'join') !== false) $estimatedTime += 5.5;
        }

        return [
            'package' => 'warnadi/db-stressmit',
            'query' => $query,
            'execution_time_ms' => $estimatedTime,
            'security_report' => [
                'risk_score' => $riskScore,
                'is_safe' => $isSafe,
                'issues' => $issues
            ],
            'checked_at' => date('Y-m-d H:i:s')
        ];
    }

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

        $realExecutionTimeMs = (microtime(true) - $startTime) * 1000;
        $analysis = self::analyze($query, $params, $realExecutionTimeMs);
        
        $analysis['status'] = $success ? 'SUCCESS' : 'SQL ERROR';
        if ($errorMessage) {
            $analysis['database_error'] = $errorMessage;
        }

        return $analysis;
    }
}