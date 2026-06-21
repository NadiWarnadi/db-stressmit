<?php

/**
 * Script Compiler Otomatis untuk db-stressmit (Jalur API Resmi GitHub)
 * Mengunduh wordlist SQLi global secara aman menggunakan GitHub API Contents.
 */

$outputFile = __DIR__ . '/../src/Patterns/compiled.php';

echo "🤖 Menghubungi GitHub API untuk mengambil Payload SQLi...\n";

// Menggunakan API resmi GitHub untuk menghindari blokir cURL / Rate Limit
$sources = [
    'Swissky SQLi Generic' => 'https://github.com',
    'Swissky SQLi Auth'    => 'https://github.com',
    'SecLists SQLi'        => 'https://github.com'
];

$targetSignatures = [
    'sleep', 'benchmark', 'extractvalue', 'updatexml', 'load_file', 
    'gtid_subset', 'st_latfromgeohash', 'floor', 'rand', 'make_set',
    'delay', 'pg_sleep', 'waitfor', 'sys_eval', 'cmdshell', 'union',
    'select', 'insert', 'update', 'delete', 'drop', 'alter', 'where'
];

$collectedKeywords = [];

foreach ($sources as $name => $apiUrl) {
    echo "📥 Mengunduh data via API: $name...\n";
    
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    // WAJIB: GitHub API menuntut User-Agent dan header Accept khusus
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'User-Agent: db-stressmit-compiler-bot',
        'Accept: application/vnd.github.v3.raw' 
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && !empty($response)) {
        $lines = explode("\n", strtolower($response));
        $matchCount = 0;
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue;
            }
            
            foreach ($targetSignatures as $sig) {
                // Modifikasi regex agar pencarian kata kunci lebih fleksibel di dalam payload
                if (preg_match("/" . preg_quote($sig, '/') . "/i", $line)) {
                    $collectedKeywords[] = $sig;
                    $matchCount++;
                }
            }
        }
        echo "🔹 Berhasil mengekstrak $matchCount kecocokan dari $name.\n";
    } else {
        echo "⚠️ Gagal mengakses API $name (HTTP $httpCode). Menggunakan data lokal jika tersedia.\n";
    }
}

// --- STANDARDISASI & DE-DUPLIKASI ---
$collectedKeywords = array_unique(array_filter(array_map('trim', $collectedKeywords)));

// --- ROBUST FALLBACK GUARD ---
$fallbackPatterns = ['sleep', 'benchmark', 'extractvalue', 'updatexml', 'load_file', 'union', 'select'];
$collectedKeywords = array_values(array_unique(array_merge($collectedKeywords, $fallbackPatterns)));

// --- COMPRESSION & REGEX COMPILATION ---
$escapedKeywords = array_map('preg_quote', $collectedKeywords);
$compiledRegex = '/\b(' . implode('|', $escapedKeywords) . ')\b\s*(\(|\[|--|\#|\/\*|\s|$)/i';

// --- GENERASI ARTIFAK AMAN ---
$dir = dirname($outputFile);
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$template = "<?php\n\n";
$template .= "/**\n * TERKOMPILASI OTOMATIS OLEH GIT WORDLIST SCRAPER (API VERSION)\n * JANGAN DIEDIT MANUAL - AKAN TIMBUL OVERWRITE\n */\n\n";
$template .= "return [\n";
$template .= "    'generated_at' => '" . date('Y-m-d H:i:s') . "',\n";
$template .= "    'dynamic_regex' => '" . addslashes($compiledRegex) . "',\n";
$template .= "    'keywords' => " . var_export($collectedKeywords, true) . ",\n";
$template .= "    'keywords_count' => " . count($collectedKeywords) . "\n";
$template .= "];\n";

if (file_put_contents($outputFile, $template) !== false) {
    echo "✅ Sukses memperbarui berkas pola! Total ada " . count($collectedKeywords) . " aturan aktif.\n";
} else {
    echo "❌ Gagal menyimpan file berkas!\n";
    exit(1);
}
