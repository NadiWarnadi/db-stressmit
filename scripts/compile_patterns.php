<?php

/**
 * Script Compiler Otomatis untuk db-stressmit (Versi Standar Internasional)
 * Mengambil data ancaman secara agresif, menerapkan sanitasi ketat, 
 * dan menyusun struktur Regex berbasis OWASP Core Rule Set (CRS) style.
 */

$outputFile = __DIR__ . '/../src/Patterns/compiled.php';

echo "🤖 Menghubungi OSV.dev Threat Intelligence API...\n";

// Core Signature bawaan sebagai fondasi utama (Mengikuti tren OWASP Top 10 & SQLi Cheat Sheets)
$targetSignatures = [
    'sleep', 'benchmark', 'extractvalue', 'updatexml', 'load_file', 
    'gtid_subset', 'st_latfromgeohash', 'floor', 'rand', 'make_set',
    'delay', 'pg_sleep', 'waitfor', 'sys_eval', 'cmdshell', 'union',
    'into\s+(outfile|dumpfile)', 'user_id', 'admin_password'
];

$url = 'https://api.osv.dev/v1/query';

// List paket-paket kritis di ekosistem PHP yang paling sering mendapati laporan SQLi
// Ini memperluas jangkauan tangkapan dibanding hanya mengecek core PHP saja
$criticalPackages = [
    ['name' => 'laravel/framework', 'ecosystem' => 'Packagist'],
    ['name' => 'yiisoft/yii2', 'ecosystem' => 'Packagist'],
    ['name' => 'topthink/think', 'ecosystem' => 'Packagist'], // Sering jadi target di Asia
    ['name' => 'doctrine/orm', 'ecosystem' => 'Packagist'],
    ['name' => 'php', 'ecosystem' => 'OSS-Fuzz']
];

$collectedKeywords = [];

foreach ($criticalPackages as $package) {
    $payload = json_encode([
        'package' => [
            'name' => $package['name'],
            'ecosystem' => $package['ecosystem']
        ]
    ]);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if (!empty($data['vulns'])) {
            foreach ($data['vulns'] as $vuln) {
                $details = $vuln['details'] ?? '';
                $summary = $vuln['summary'] ?? '';
                $fullText = strtolower($details . ' ' . $summary);

                // Validasi Internasional: Hanya ambil jika laporan terverifikasi sebagai SQL Injection
                if (strpos($fullText, 'sql') !== false && (strpos($fullText, 'injection') !== false || strpos($fullText, 'sqli') !== false)) {
                    foreach ($targetSignatures as $sig) {
                        // Jika kata kunci eksploitasi di-mention dalam laporan bug/CVE
                        if (preg_match("/\b" . $sig . "\b/i", $fullText)) {
                            $collectedKeywords[] = $sig;
                        }
                    }
                }
            }
        }
    }
}

// --- STANDARDISASI & DE-DUPLIKASI ---
$collectedKeywords = array_unique(array_filter(array_map('trim', $collectedKeywords)));

// --- ROBUST FALLBACK GUARD ---
// Memastikan mesin tidak lumpuh jika API down atau rate-limited
$fallbackPatterns = ['sleep', 'benchmark', 'extractvalue', 'updatexml', 'load_file', 'union'];
$collectedKeywords = array_values(array_unique(array_merge($collectedKeywords, $fallbackPatterns)));

// --- COMPRESSION & INTERNATIONAL WORD BOUNDARY GUARD (\b) ---
// Membuat pola regex yang super ketat: mencegah false positive pada kata seperti 'sleep_logs' atau 'asleep'
$escapedKeywords = array_map('preg_quote', $collectedKeywords);

// Regex hasil akhir menggunakan pola OWASP CRS: \b(keyword1|keyword2)\b|\b(keyword1|keyword2)\s*\(
// Ini akan mendeteksi fungsi seperti sleep(5) ATAU keyword mandiri seperti UNION yang diikuti whitespace/komentar
$compiledRegex = '/\b(' . implode('|', $escapedKeywords) . ')\b\s*(\(|\[|--|\#|\/\*|\s|$)/i';

// --- GENERASI ARTIFAK AMAN ---
$dir = dirname($outputFile);
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$template = "<?php\n\n";
$template .= "/**\n * TERKOMPILASI OTOMATIS OLEH GIT SCRAPER (STANDAR INTERNASIONAL)\n * JANGAN DIEDIT MANUAL - AKAN TIMBUL OVERWRITE\n * Last Updated: " . date('Y-m-d H:i:s') . " UTC\n */\n\n";
$template .= "return [\n";
$template .= "    'generated_at' => '" . date('Y-m-d H:i:s') . "',\n";
$template .= "    'dynamic_regex' => '" . addslashes($compiledRegex) . "',\n";
$template .= "    'keywords' => " . var_export($collectedKeywords, true) . ",\n";
$template .= "    'keywords_count' => " . count($collectedKeywords) . "\n";
$template .= "];\n";

if (file_put_contents($outputFile, $template) !== false) {
    echo "✅ Sukses menyusun pola berstandar internasional (" . count($collectedKeywords) . " aturan).\n";
    echo "📦 Ukuran file: " . round(filesize($outputFile) / 1024, 2) . " KB (Sangat Ringan & Cepat)\n";
} else {
    echo "❌ Gagal mengamankan file pola ke sistem disk!\n";
    exit(1);
}