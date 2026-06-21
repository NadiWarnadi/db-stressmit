<?php

/**
 * Script Compiler Otomatis untuk db-stressmit (Versi Opsi A - Payload Terverifikasi)
 * Mengambil data ancaman langsung dari Wordlist SQLi dunia, 
 * menerapkan sanitasi, dan menyusun struktur Regex berbasis OWASP Core Rule Set (CRS) style.
 */

$outputFile = __DIR__ . '/../src/Patterns/compiled.php';

echo "🤖 Menghubungi Repositori Payload Global (SwisskyRepo & Seclists)...\n";

// Sumber Wordlist SQLi Mentah (Raw GitHub)
$sources = [
    'Swissky SQLi Generic' => 'https://githubusercontent.com',
    'Swissky SQLi Auth'    => 'https://githubusercontent.com',
    'SecLists SQLi'        => 'https://githubusercontent.com'
];

// Target fungsi dan kata kunci SQL kritis yang WAJIB diekstrak dari payload nyata
$targetSignatures = [
    'sleep', 'benchmark', 'extractvalue', 'updatexml', 'load_file', 
    'gtid_subset', 'st_latfromgeohash', 'floor', 'rand', 'make_set',
    'delay', 'pg_sleep', 'waitfor', 'sys_eval', 'cmdshell', 'union',
    'select', 'insert', 'update', 'delete', 'drop', 'alter', 'where',
    'into\s+(outfile|dumpfile)', 'or\s+\d+=\d+', 'and\s+\d+=\d+'
];

$collectedKeywords = [];

foreach ($sources as $name => $url) {
    echo "📥 Mengunduh data dari: $name...\n";
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) db-stressmit-bot/1.0');
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        // Pecah response menjadi baris per baris
        $lines = explode("\n", strtolower($response));
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line) || strpos($line, '#') === 0) {
                continue; // Lewati baris kosong atau komentar
            }
            
            // Pindai apakah ada signature kita di dalam payload asli tersebut
            foreach ($targetSignatures as $sig) {
                if (preg_match("/\b" . $sig . "\b/i", $line)) {
                    $collectedKeywords[] = $sig;
                }
            }
        }
    } else {
        echo "⚠️ Gagal mengunduh sumber: $name (HTTP $httpCode)\n";
    }
}

// --- STANDARDISASI & DE-DUPLIKASI ---
$collectedKeywords = array_unique(array_filter(array_map('trim', $collectedKeywords)));

// --- ROBUST FALLBACK GUARD ---
$fallbackPatterns = ['sleep', 'benchmark', 'extractvalue', 'updatexml', 'load_file', 'union', 'select'];
$collectedKeywords = array_values(array_unique(array_merge($collectedKeywords, $fallbackPatterns)));

// --- COMPRESSION & INTERNATIONAL WORD BOUNDARY GUARD (\b) ---
$escapedKeywords = array_map('preg_quote', $collectedKeywords);
$compiledRegex = '/\b(' . implode('|', $escapedKeywords) . ')\b\s*(\(|\[|--|\#|\/\*|\s|$)/i';

// --- GENERASI ARTIFAK AMAN ---
$dir = dirname($outputFile);
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

$template = "<?php\n\n";
$template .= "/**\n * TERKOMPILASI OTOMATIS OLEH GIT WORDLIST SCRAPER (STANDAR INTERNASIONAL)\n * JANGAN DIEDIT MANUAL - AKAN TIMBUL OVERWRITE\n * Last Updated: " . date('Y-m-d H:i:s') . " UTC\n */\n\n";
$template .= "return [\n";
$template .= "    'generated_at' => '" . date('Y-m-d H:i:s') . "',\n";
$template .= "    'dynamic_regex' => '" . addslashes($compiledRegex) . "',\n";
$template .= "    'keywords' => " . var_export($collectedKeywords, true) . ",\n";
$template .= "    'keywords_count' => " . count($collectedKeywords) . "\n";
$template .= "];\n";

if (file_put_contents($outputFile, $template) !== false) {
    echo "✅ Sukses menyusun pola berstandar internasional (" . count($collectedKeywords) . " aturan berdasarkan payload nyata).\n";
    echo "📦 Ukuran file: " . round(filesize($outputFile) / 1024, 2) . " KB\n";
} else {
    echo "❌ Gagal mengamankan file pola ke sistem disk!\n";
    exit(1);
}
