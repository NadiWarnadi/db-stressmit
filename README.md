# DB-STRESSMIT 🚀

An agnostic, ultra-lightweight database query benchmark, heuristic security auditor, and stress tester for PHP and Laravel.

---

## Key Features

* 🧠 **Heuristic AI-Era Security Audit:** Risk scoring engine to detect SQL Injection, logical tautology bypass (`OR 1=1`), and destructive stacked queries.
* ⏱️ **Real-time Performance Profiling:** Measures execution speed down to the millisecond (`ms`).
* 🤖 **Laravel Auto-Pilot Support:** Zero-configuration query logging via Laravel Package Discovery.
* 🌍 **All Platforms Compatible:** Native PHP, CodeIgniter 4, Symfony, WordPress, and CLI interface ready.

---

## Installation

You can install the package via Composer:

```bash
composer require warnadi/db-stressmit

How to Use
1. Global CLI (All Platforms / Native PHP / WordPress / CI4)
Run the built-in binary terminal to test a standalone query:

Bash
php vendor/bin/db-stressmit --query="SELECT * FROM users WHERE id = 1"
2. Laravel Auto-Pilot Integration
Zero setup required! Thanks to Laravel Package Discovery, the StressmitServiceProvider activates automatically.

Whenever you are in local environment, it will capture abnormal queries (slow queries or high risk queries) and write an automated audit payload inside your Laravel log file (storage/logs/laravel.log):

Plaintext
[DB-STRESSMIT ALERT] Terdeteksi anomali pada query Laravel Anda!
{
    "query": "SELECT * FROM users WHERE username = '1' OR '1'='1'",
    "risk_score": "45/100",
    "execution_time": "1.25 ms",
    "security_issues": ["HIGH RISK: Terdeteksi pola lokasi bypass (Logical Tautology OR 1=1)"]
}
3. Programmatic Usage (Manual Trigger)
If you prefer manual control inside your code:

PHP
use DbStressmit\Stressmit;

$result = Stressmit::analyze("SELECT * FROM products WHERE price > ?", [50000]);
print_r($result);

Security & Reputation Guard
This tool is strictly optimized for performance and security. The live hook listener (DB::listen) is gated behind app()->environment('local') checking to ensure your production server remains 100% fast, clean, and unburdened

License
The MIT License (MIT). Please see License File for more information.

---

### Cara Melakukan Uji Coba (Testing) Sekarang

Untuk memastikan kodinganmu berfungsi penuh sebelum didorong ke GitHub, mari kita tes menggunakan file `test-cli.php` manual di root folder.

1. Buat file baru bernama `test-cli.php` di folder `db-stressmit/`.
2. Masukkan kode uji coba ini:

```php
<?php
require_once __DIR__ . '/vendor/autoload.php';

use DbStressmit\Stressmit;

echo "--- MENGUJI QUERY AMAN ---\n";
$aman = Stressmit::analyze("SELECT * FROM users WHERE id = ?", [1]);
print_r($aman);

echo "\n--- MENGUJI QUERY INJEKSI MALICIOUS ---\n";
$bahaya = Stressmit::analyze("SELECT * FROM users WHERE username = 'admin' OR '1'='1'");
print_r($bahaya);
Jalankan di terminal PowerShell kamu:

PowerShell
php .\test-cli.php