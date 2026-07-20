---

## 🗺️ Rencana 6 Hari Menuju v1.0.0

---

### 📅 Hari 1: Testing & Refactor (Fondasi Kokoh)

**Tujuan:** Pastikan semua kode stabil, sudah ada unit test, dan performa optimal.

| No | Task | Detail |
|----|------|--------|
| 1 | **Unit Testing** | Bikin test untuk `Scanner`, `Profiler`, `RiskEngine` pakai PHPUnit |
| 2 | **Benchmark Core** | Ukur overhead auto-detection dan adapter |
| 3 | **Refactor kecil** | Bersihkan kode, tambah tipe data (`strict_types=1`), PHPStan level 5 |
| 4 | **Add `.env` support** | Baca DB config dari `.env` untuk CLI mode (pakai `symfony/dotenv`) |
| 5 | **Error handling** | Tangani koneksi gagal, query error, timeout dengan graceful |

**Output:** Semua test hijau, code coverage > 70%, tidak ada warning PHP 8.x.

---

### 📅 Hari 2: Fitur Export & Report Generator

**Tujuan:** User bisa simpan hasil stress test dalam berbagai format untuk laporan.

| No | Task | Detail |
|----|------|--------|
| 1 | **Export JSON** | Simpan hasil statistik + risk score ke file `.json` |
| 2 | **Export CSV** | Simpan tiap iterasi (time, error) ke CSV |
| 3 | **Export HTML** | Buat laporan HTML yang cantik (bisa dibuka di browser) |
| 4 | **Command option** | Tambahkan `--export=json,html` di CLI |
| 5 | **Email report** | (Opsional) Kirim laporan via email (pakai `symfony/mailer`) |

**Output:** Perintah `php bin/db-stressmit stress "SELECT ..." --export=json,html` menghasilkan file laporan.

---

### 📅 Hari 3: Query Analyzer & Index Recommendation

**Tujuan:** Tidak hanya stress, tapi juga kasih saran perbaikan query.

| No | Task | Detail |
|----|------|--------|
| 1 | **EXPLAIN analyzer** | Jalankan `EXPLAIN` untuk query dan parse hasilnya |
| 2 | **Index detector** | Deteksi apakah query sudah pakai index atau full scan |
| 3 | **Recommendation engine** | Kasih saran: "Tambahkan index pada kolom X", "Hindari SELECT *" |
| 4 | **Command `analyze`** | Perintah terpisah: `php bin/db-stressmit analyze "SELECT ..."` |
| 5 | **Slow query log** | Integrasi dengan slow query log framework (Laravel Telescope, WordPress Query Monitor) |

**Output:** Analisis mendalam, saran perbaikan query, dan deteksi missing index.

---

### 📅 Hari 4: Concurrency Real & Load Simulation

**Tujuan:** Simulasi beban lebih realistis dengan request paralel.

| No | Task | Detail |
|----|------|--------|
| 1 | **Parallel executor** | Pakai `pcntl_fork` atau `symfony/process` untuk paralel |
| 2 | **Load simulation** | Simulasikan traffic spike (misal 100 koneksi dalam 1 detik) |
| 3 | **Tps (transactions per second)** | Hitung throughput query per detik |
| 4 | **Option `--concurrency=10`** | Betul-betul jalan paralel, bukan simulasi sequential |
| 5 | **Progress bar** | Tampilkan progress real-time pakai Symfony Progress Bar |

**Output:** Hasil statistik dengan koncurrency real, throughput (TPS), distribusi waktu per thread.

---

### 📅 Hari 5: Watch Mode & Alerting

**Tujuan:** Pantau database secara terus-menerus dan beri alert kalau ada masalah.

| No | Task | Detail |
|----|------|--------|
| 1 | **Watch mode** | Perintah `php bin/db-stressmit watch "SELECT ..." -i 10` berjalan terus |
| 2 | **Threshold alert** | Jika avg query > threshold, tampilkan peringatan di terminal |
| 3 | **Repeat interval** | Jalankan tiap X detik/menit (opsi `--interval=60`) |
| 4 | **Webhook/Notification** | Kirim notifikasi ke Slack/Telegram kalau ada anomali |
| 5 | **Log history** | Simpan histori hasil watch ke file (append) |

**Output:** Monitoring database real-time, alert otomatis, historis log.

---

### 📅 Hari 6: Dokumentasi Profesional, Video Demo, & Polish

**Tujuan:** Siap dirilis ke publik sebagai v1.0.0 dengan dokumentasi lengkap.

| No | Task | Detail |
|----|------|--------|
| 1 | **README.md upgrade** | Instalasi, contoh penggunaan, semua opsi command, FAQ |
| 2 | **Demo video / GIF** | Rekam 1-2 menit demo di terminal, upload ke YouTube |
| 3 | **Website simple** | (Opsional) Buat halaman landing pakai GitHub Pages |
| 4 | **Changelog.md** | Daftar perubahan dari 0.3.0 ke 1.0.0 |
| 5 | **Contributing guide** | Panduan untuk kontributor |
| 6 | **Release v1.0.0** | Tag, rilis di GitHub, update Packagist |

**Output:** Dokumentasi setara project open source profesional (Laravel, Symfony, dll).

---

## 🛡️ Prinsip "Hati-hati" yang Harus Dipegang

| Prinsip | Penjelasan |
|---------|------------|
| **Non-breaking changes** | Semua fitur baru harus kompatibel dengan 0.3.0 |
| **Testing sebelum merge** | Setiap PR/commit harus lewat tes lokal dulu |
| **Feature flag** | Fitur baru pakai opsi CLI (tidak mengubah perilaku default) |
| **Backward compatibility** | Adapter lama tetap jalan, tidak ada perubahan interface |
| **Changelog terupdate** | Setiap hari, catat apa yang berubah |

---

## 📊 Timeline 6 Hari (Visual)

```
Hari 1: [Testing & Refactor] ████████████████████████████████
Hari 2: [Export & Report]    ████████████████████████████████
Hari 3: [Analyzer & Index]   ████████████████████████████████
Hari 4: [Concurrency Real]   ████████████████████████████████
Hari 5: [Watch & Alert]      ████████████████████████████████
Hari 6: [Docs & Polish]      ████████████████████████████████
```

---

## 🎯 Setelah 6 Hari, Kamu Punya:

- ✅ Unit test & code coverage
- ✅ Laporan HTML/CSV/JSON
- ✅ Query analyzer & index recommendation
- ✅ Concurrency real (parallel execution)
- ✅ Watch mode & alerting (Slack/Telegram)
- ✅ Dokumentasi profesional
- ✅ Versi 1.0.0 rilis

---

## 🚀 Yang Bisa Kamu Mulai Hari Ini (Hari 1)

```bash
# 1. Buat branch baru dari main
git checkout -b feature/v1.0.0

# 2. Install PHPUnit
composer require --dev phpunit/phpunit

# 3. Setup testing
mkdir tests
touch tests/ScannerTest.php
touch tests/ProfilerTest.php
touch tests/RiskEngineTest.php
```

---

