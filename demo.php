<?php

// Jalankan autoloader Composer
require_once __DIR__ . '/vendor/autoload.php';

use DbStressmit\Stressmit;

echo "=== MEMULAI DB-STRESSMIT SIMULASI ===\n\n";

// Tes 1: Query yang efisien
$queryAman = "SELECT id, username FROM users WHERE id = 45 LIMIT 1";
$hasilAman = Stressmit::testQuery($queryAman, ['rows' => 1]);
print_r($hasilAman);

echo "\n--------------------------------------------------\n\n";

// Tes 2: Query berat
$queryBerat = "SELECT * FROM orders JOIN users ON orders.user_id = users.id WHERE users.status = 'active'";
$hasilBerat = Stressmit::testQuery($queryBerat, ['rows' => 5000]);
print_r($hasilBerat);