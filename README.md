
# DB-STRESSMIT 🚀

<p align="center">
  <img src="https://laravel.com/img/logomark.min.svg" alt="Laravel Logo" width="50" height="50" style="vertical-align: middle; margin-right: 10px;"/>
  <img src="https://www.php.net/images/logos/php-logo.svg" alt="PHP Logo" width="70" height="50" style="vertical-align: middle;"/>
</p>

<h3 align="center">DB-STRESSMIT</h3>

<p align="center">
  <strong>An agnostic, ultra-lightweight database query benchmark, heuristic security auditor, and automated stress tester for PHP and Laravel applications.</strong>
</p>

<p align="center">
  <a href="https://packagist.org/packages/warnadi/db-stressmit"><img src="https://img.shields.io/packagist/v/warnadi/db-stressmit?style=flat-square" alt="Packagist Version"></a>
  <a href="https://packagist.org/packages/warnadi/db-stressmit"><img src="https://img.shields.io/packagist/dt/warnadi/db-stressmit?style=flat-square" alt="Total Downloads"></a>
  <a href="LICENSE"><img src="https://img.shields.io/packagist/l/warnadi/db-stressmit?style=flat-square" alt="License"></a>
  <a href="https://github.com/warnadi/db-stressmit/stargazers"><img src="https://img.shields.io/github/stars/warnadi/db-stressmit?style=flat-square" alt="GitHub Stars"></a>
</p>

---

Are you looking for a powerful **Laravel query log auditor**, **PHP SQL profiling tool**, or an automated **database stress testing package**? 

**DB-STRESSMIT** provides real-time heuristic security detection and millisecond-accurate profiling without hurting production performance. It helps developers detect SQL bottlenecks and security vulnerabilities *before* they hit production.

## 🎯 Key Features & Capabilities

* 🧠 **Heuristic SQL Injection Scanner:** Next-gen risk scoring engine designed to detect SQL Injection (SQLi) vulnerabilities, bypass logic (`OR 1=1`), and destructive stacked queries inside raw SQL statements.
* ⏱️ **PHP Database Performance Profiler:** Measures exact query execution speed down to milliseconds (`ms`) to detect database bottlenecks instantly.
* 🤖 **Laravel Auto-Pilot Integration:** Seamless query logging hook via Laravel Package Discovery. Zero configuration required!
* 🌍 **All-Platform Agnostic Support:** Works out-of-the-box on Native PHP scripts, CodeIgniter 4, Symfony ORM (Doctrine), WordPress (`$wpdb`), and terminal environments (CLI).
* 🛡️ **Production Safety Guard:** Automated sniffers are isolated strictly to local environments, ensuring 0% performance overhead in production.

---

## ⚙️ Requirements

* **PHP:** `>= 8.0` (Fully supports PHP 8.1, 8.2, 8.3+)
* **Laravel:** `^9.0 | ^10.0 | ^11.0` (Optional, for auto-pilot feature)

---

## 🚀 Installation

Install the package seamlessly via [Composer](https://getcomposer.org/):

```bash
composer require warnadi/db-stressmit

```

---

## 💻 How to Use (Usage Examples)

### 1. Laravel Auto-Pilot Query Auditor (Zero Config)

No setup required! The package automatically triggers the `StressmitServiceProvider` in the background using Laravel Package Discovery.

When your application is in `local` development mode, it acts as an automated query firewall. Any slow query (>100ms) or high-risk SQL structure will be dumped safely into your `storage/logs/laravel.log`:

```json
[DB-STRESSMIT ALERT] Terdeteksi anomali pada query Laravel Anda!
{
    "query": "SELECT * FROM users WHERE username = 'admin' OR '1'='1'",
    "risk_score": "45/100",
    "execution_time": "1.25 ms",
    "security_issues": [
        "HIGH RISK: Terdeteksi pola lokasi bypass (Logical Tautology OR 1=1)"
    ]
}

```

### 2. Global CLI Tool (For Native PHP, WordPress, & CodeIgniter)

Run the dedicated CLI binary directly from your project root terminal to audit a standalone SQL query:

```bash
php vendor/bin/db-stressmit --query="SELECT * FROM users WHERE id = 1"

```

### 3. Manual Programmatic Code Integration

Manually trigger the database security scanner anywhere inside your PHP controllers or custom scripts:

```php
<?php

use DbStressmit\Stressmit;

// Run static query heuristic analyzer
$result = Stressmit::analyze("SELECT * FROM products WHERE price > ?", [50000]);

print_r($result);

```

---

## 🔒 Performance & Production Safety

We understand that performance is everything. The automated query sniffer listener (`DB::listen`) is strictly wrapped under environment isolation checks:

```php
if (app()->environment('local')) {
    // Only runs here!
}

```

This ensures your live **production database speed remains untouched, fast, and completely secure.**

---

## 🤝 Contributing

Contributions, bug reports, and database tuning feature requests are welcome!

1. Fork the Repository.
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`).
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`).
4. Push to the Branch (`git push origin feature/AmazingFeature`).
5. Open a Pull Request.

---

## 💖 Support the Project

If you find this package useful, please consider giving it a **Star ⭐** to help other developers discover it!

Developed with ❤️ by **Warnadi Nadi** ([warnadi2006@gmail.com](mailto:warnadi2006@gmail.com)).

---

## 📄 License

This database stress-testing framework is open-sourced software licensed under the **MIT license**.

```

```