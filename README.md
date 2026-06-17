# DB-STRESSMIT 🚀

[![Packagist Version](https://img.shields.io/packagist/v/warnadi/db-stressmit)](https://packagist.org/packages/warnadi/db-stressmit)
[![Total Downloads](https://img.shields.io/packagist/dt/warnadi/db-stressmit)](https://packagist.org/packages/warnadi/db-stressmit)
[![License](https://img.shields.io/packagist/l/warnadi/db-stressmit)](LICENSE)

**DB-STRESSMIT** is an agnostic, ultra-lightweight database query benchmark, heuristic security auditor, and automated stress tester for PHP and Laravel application development. 

Are you looking for a powerful **Laravel query log auditor**, **PHP SQL profiling tool**, or an automated **database stress testing package**? DB-STRESSMIT provides real-time heuristic security detection and millisecond-accurate profiling without hurting production performance.

---

## Key Features & Capabilities

* 🧠 **Heuristic SQL Injection Scanner:** Next-gen risk scoring engine designed to detect SQL Injection vulnerabilities, bypass logic (`OR 1=1`), and destructive stacked queries inside raw SQL statements.
* ⏱️ **PHP Database Performance Profiler:** Measures exact query execution speed down to milliseconds (`ms`) to detect application database bottlenecks.
* 🤖 **Laravel Auto-Pilot Integration:** Seamless query logging hook via Laravel Package Discovery. Zero-configuration required.
* 🌍 **All Platforms Agnostic Support:** Works out-of-the-box on Native PHP scripts, CodeIgniter 4, Symfony ORM (Doctrine), WordPress (`$wpdb`), and terminal environments.

---

## Installation

Install the package seamlessly using Composer:

```bash
composer require warnadi/db-stressmit
How to Use (Usage Examples)
1. Global CLI Tool (For Native PHP, WordPress, & CodeIgniter)
Run the dedicated CLI binary from your project root terminal to audit a standalone query:

Bash
php vendor/bin/db-stressmit --query="SELECT * FROM users WHERE id = 1"
2. Laravel Auto-Pilot Query Auditor
Zero configuration setup! The package automatically triggers the StressmitServiceProvider in the background.

When your application is in local development mode, it acts as an automated query fire wall. Any slow query (>100ms) or high-risk SQL structure will be dumped safely into your storage/logs/laravel.log:

Plaintext
[DB-STRESSMIT ALERT] Terdeteksi anomali pada query Laravel Anda!
{
    "query": "SELECT * FROM users WHERE username = 'admin' OR '1'='1'",
    "risk_score": "45/100",
    "execution_time": "1.25 ms",
    "security_issues": ["HIGH RISK: Terdeteksi pola lokasi bypass (Logical Tautology OR 1=1)"]
}
3. Manual Programmatic Code Integration
Manually trigger the database security scanner anywhere inside your PHP controller:

PHP
use DbStressmit\Stressmit;

// Run static query heuristic analyzer
$result = Stressmit::analyze("SELECT * FROM products WHERE price > ?", [50000]);
print_r($result);
Performance, Security & System Requirements
PHP Compatibility: PHP >= 8.0 (Supports PHP 8.1, 8.2, 8.3, and modern 2026 standards).

Production Safety Guard: The automated query sniffer listener (DB::listen) is strictly wrapped under environment isolation checks (app()->environment('local')) ensuring your live production database speed remains untouched, fast, and secure.

Developer & Contribution
Developed with ❤️ by Warnadi Nadi (warnadi2006@gmail.com). Contributions, bug reports, and database tuning feature requests are welcome via GitHub Issues.

License
This database stress-testing framework is open-sourced software licensed under the MIT license.