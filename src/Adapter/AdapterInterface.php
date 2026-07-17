<?php

namespace Warnadi\DbStressmit\Adapter;

interface AdapterInterface
{
    /**
     * Dapatkan koneksi database mentah (bisa pdo, mysqli, atau object framework)
     */
    public function getConnection();

    /**
     * Eksekusi query SQL dan kembalikan hasilnya (array/stdClass)
     */
    public function query(string $sql): array;

    /**
     * Waktu eksekusi query terakhir dalam milidetik
     */
    public function getLastQueryTime(): float;

    /**
     * Log semua query yang dijalankan (untuk profiling)
     */
    public function getQueryLog(): array;

    /**
     * Nama database yang sedang terhubung
     */
    public function getDatabaseName(): string;
}