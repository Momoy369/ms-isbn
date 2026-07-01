# Release v2026.07.02.1

Tanggal rilis: 2026-07-02
Tag: v2026.07.02.1

## Highlight

- Perbaikan Insight Naskah pada dashboard admin (fallback otomatis ke `jumlah_halaman` bila `manuscript_a4_pages` kosong).
- Penambahan halaman penginstalan modern di `/install` dengan checklist environment.
- Penyempurnaan middleware lisensi agar route installer tetap bisa diakses untuk onboarding.
- Pembaruan dokumentasi README dan manual book sesuai perubahan terbaru.

## Perubahan Teknis

### 1) Dashboard Insight Naskah

- Mengubah kalkulasi insight agar menggunakan data efektif A4 dengan fallback ke `jumlah_halaman`.
- Menyesuaikan metrik:
    - tracked books
    - unknown books
    - total/avg/max A4
    - jumlah A4 > 125
    - jumlah A5 cetak > 100
- Menyesuaikan tabel top naskah agar menampilkan nilai efektif A4/A5.

File terdampak:

- `app/Http/Controllers/DashboardController.php`
- `resources/views/dashboard.blade.php`

### 2) Installer Modern

- Menambahkan controller installer dengan pengecekan requirement runtime.
- Menambahkan tampilan installer modern.
- Menambahkan route publik `/install`.

File terdampak:

- `app/Http/Controllers/InstallController.php`
- `resources/views/install/index.blade.php`
- `routes/web.php`

### 3) Akses Installer Saat Lisensi Invalid

- Menambahkan pengecualian route `install.*` pada validasi lisensi agar proses instalasi/recovery tetap berjalan.

File terdampak:

- `app/Http/Middleware/EnsureLicenseIsValid.php`

### 4) Dokumentasi

File terdampak:

- `README.md`
- `docs/manual-book-sistem.md`

## Validasi

- Lint/syntax check: no errors pada file yang diubah.
- Route check: route `/install` terdaftar.
- Smoke test insight dashboard:
    - tracked_books: 2
    - unknown_books: 1
    - sum_a4_pages: 500
    - avg_a4_pages: 250

## Breaking Changes

- Tidak ada breaking change API publik.

## Catatan Deploy

1. Pull branch `main` terbaru.
2. Jalankan migration bila ada perubahan skema di branch Anda.
3. Clear cache aplikasi:
    - `php artisan optimize:clear`
4. Build asset bila diperlukan:
    - `npm run build`

## Referensi

- Tag: `v2026.07.02.1`
- Commit rilis: `fb8c243`
