<?php

namespace DbStressmit;

class Stressmit
{
    /**
     * Menguji dan menyimulasikan performa dari sebuah SQL Query.
     *
     * @param string $query Teks SQL query yang ingin diuji.
     * @param array $options Opsi tambahan seperti jumlah baris data (rows) simulasi.
     * @return array Hasil analisis stress test.
     */
    public static function testQuery(string $query, array $options = []): array
    {
        $rowsSimulated = $options['rows'] ?? 100; // Default simulasi 100 baris data
        
        // 1. Mulai Benchmark Waktu
        $startTime = microtime(true);

        // 2. Simulasi Kompleksitas Query
        $baseDelay = rand(10, 50) / 1000; // Jeda dasar dalam detik (10ms - 50ms)
        
        if (stripos($query, 'join') !== false) {
            $baseDelay += 0.15; // JOIN menambah beban simulasi
        }
        
        if (stripos($query, '%') !== false || stripos($query, 'like') !== false) {
            $baseDelay += 0.08; // Pencarian LIKE wildcard juga berat
        }

        // Tambah beban berdasarkan jumlah baris data yang disimulasikan
        $baseDelay += ($rowsSimulated * 0.0005);

        // Eksekusi jeda simulasi
        usleep((int)($baseDelay * 1000000));

        // 3. Selesai Sesi Pengujian
        $endTime = microtime(true);
        $executionTimeMs = ($endTime - $startTime) * 1000;

        // 4. Analisis Standar & Rekomendasi
        $status = 'OPTIMAL';
        $recommendations = [];

        if ($executionTimeMs > 200) {
            $status = 'SLOW QUERY (WARNING)';
            if (stripos($query, 'join') !== false) {
                $recommendations[] = 'Beban tinggi terdeteksi pada operasi JOIN. Pastikan kolom Foreign Key sudah diberi INDEX.';
            }
            if (stripos($query, '*') !== false) {
                $recommendations[] = 'Hindari penggunaan "SELECT *". Sebutkan nama kolom secara spesifik untuk menghemat memory.';
            }
            if (count($recommendations) === 0) {
                $recommendations[] = 'Query memakan waktu cukup lama. Pertimbangkan untuk membatasi hasil menggunakan LIMIT.';
            }
        } else {
            $recommendations[] = 'Performa query sangat bagus dan memenuhi standar internasional (< 200ms).';
        }

        return [
            'library' => 'db-stressmit',
            'status' => $status,
            'metrics' => [
                'execution_time_ms' => round($executionTimeMs, 2),
                'rows_simulated' => $rowsSimulated,
            ],
            'analysis' => [
                'query_tested' => trim($query),
                'recommendations' => $recommendations
            ],
            'tested_at' => date('Y-m-d H:i:s')
        ];
    }
}