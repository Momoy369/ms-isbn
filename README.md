# MS ISBN Publishing System

Sistem ini adalah platform manajemen penerbitan end-to-end berbasis Laravel, mulai dari naskah masuk, alur produksi, pengelolaan ISBN, keuangan, royalty author, hingga storefront penjualan buku fisik dan ebook.

## Tujuan Sistem

- Menstandarkan proses penerbitan agar transparan dan terukur.
- Memisahkan peran operasional (editor, layouter, designer, ISBN, finance, author, admin).
- Menyediakan dashboard operasional, approval, dan monitoring status.
- Menangani penjualan internal (storefront) yang terhubung ke pembayaran iPaymu.
- Menyediakan fondasi monetisasi author melalui invoice dan royalty.

## Teknologi Utama

- Backend: Laravel (PHP)
- Frontend: Blade + AdminLTE + Vite/Tailwind (bagian tertentu)
- Database: MySQL/MariaDB (umum), migration Laravel
- Integrasi eksternal:
    - iPaymu (checkout/callback pembayaran)
    - RajaOngkir (estimasi ongkir)

## Fitur Yang Sudah Tersedia

### 1. Manajemen Workflow Produksi Naskah

- Input dan pengelolaan data buku/naskah.
- Workflow status produksi bertahap (termasuk fase persetujuan dan finalisasi).
- Assignment lintas role (editor, layouter, designer, ISBN) dan riwayat assignment.
- Timeline produksi dan monitoring keterlambatan.

### 2. Dokumen dan File Produksi

- Upload file kerja per role.
- Generate dokumen pendukung (mis. halaman judul, surat, lampiran) sesuai modul yang aktif.
- Workspace file role + share file internal.
- Akses file final untuk author berdasarkan aturan status dan pembayaran.

### 3. ISBN Queue dan Proses Approval

- Queue pengajuan ISBN.
- Proses submit/approve ISBN berdasarkan role berwenang.
- Kontrol readiness file sebelum submit/approval.

### 4. Dashboard Operasional

- Dashboard backoffice (produksi, assignment, alert).
- Dashboard author (status buku, invoice, royalty, kontrak).
- Ringkasan finansial untuk area finance.

### 5. Keuangan, Invoice, dan Pembayaran Author

- Pembuatan dan pengelolaan invoice author.
- Alur pembayaran dengan iPaymu untuk invoice author.
- Callback pembayaran untuk update status invoice otomatis.

### 6. Royalty System

- Ledger royalty author bulanan.
- Perhitungan net royalty dan alokasi ke payout request.
- Maker-checker payout flow (approve, pay, reject).
- Manajemen kontrak/agreemen royalty author.

### 7. Legacy Catalog dan External Sales

- Input katalog buku legacy terpisah dari naskah aktif.
- Input external sales yang dapat dikaitkan ke buku aktif atau legacy.

### 8. Storefront Publik

- Katalog publik buku dengan detail halaman produk.
- Pemesanan buku dari publik.
- Integrasi checkout iPaymu untuk order storefront.
- Callback iPaymu untuk update status order storefront.
- Tracking order publik berdasarkan nomor order.

### 9. Penjualan Ebook Dasar

- Dukungan tipe produk print/ebook pada katalog store.
- Ebook tanpa pengiriman fisik.
- Reader page internal berbasis password untuk membuka ebook (setelah pembayaran berhasil).

### 10. Manajemen Role dan Akses

- Role utama: admin, editor, layouter, designer, isbn, author, owner, finance, superadmin.
- Role tambahan: customer, reader.
- Register default ke customer.
- Upgrade akun customer/reader ke author dari halaman profile (dengan syarat data tertentu).

### 11. Branding Dasar

- Konfigurasi logo di area AdminLTE, auth screen, preloader, dan storefront.

## Fitur Yang Belum Sempurna (Known Gaps)

- Checkout store untuk ongkir masih memakai input destination city id manual (belum UX dropdown provinsi/kota penuh).
- Halaman tracking order publik belum memakai verifikasi tambahan (contoh OTP/email/telepon) untuk membatasi keterbukaan data.
- Reader ebook saat ini masih berbasis link embed; belum ada proteksi anti-share yang kuat.
- Pengelolaan stok dan kompensasi rollback stok saat skenario callback edge-case masih perlu hardening lebih lanjut.
- Notifikasi status order store belum lengkap untuk semua transisi penting.
- Dokumentasi operasional (SOP role per modul) belum menyeluruh.

## To-Do Fitur Baru (Belum Ada)

- Tambah verifikasi tracking order (OTP WA/email atau minimal validasi phone/email pembeli).
- Tambah halaman akun customer untuk melihat riwayat order dan invoice store.
- Tambah library ebook internal (list ebook yang sudah dibeli per akun).
- Tambah fitur kupon/voucher promo di storefront.
- Tambah fitur multi-payment gateway (fallback selain iPaymu).
- Tambah laporan penjualan storefront periodik (harian/mingguan/bulanan) dengan export.
- Tambah modul retur/refund order store.

## To-Do Penyempurnaan Fitur Yang Sudah Ada

- UX checkout shipping:
    - Integrasi dropdown provinsi/kota RajaOngkir.
    - Simpan dan tampilkan label kota/provinsi, bukan city id saja.
- Keamanan reader ebook:
    - Session token sekali pakai.
    - Watermark identitas pembeli.
    - Batas perangkat/sesi.
- Hardening callback iPaymu:
    - Idempotency key yang konsisten.
    - Audit log callback lebih detail.
    - Retry-safe update status.
- Penyempurnaan role customer/reader:
    - Dashboard khusus customer.
    - Flow upgrade ke author dengan approval admin (opsional) dan checklist dokumen.
- Penyempurnaan admin order:
    - Validasi status transition yang lebih ketat.
    - Auto-notify pembeli saat status berubah (paid, packed, shipped, completed).
- Penyempurnaan quality engineering:
    - Perbaikan environment test (sqlite driver atau dedicated test DB).
    - Tambah test coverage untuk payment callback, order flow, royalty payout.

## Roadmap Sprint Mingguan

Roadmap ini disusun agar item kritikal diselesaikan lebih dulu, lalu diikuti peningkatan UX, keamanan, dan ekspansi fitur bisnis.

### Sprint 1 (Minggu 1) - Stabilitas Checkout dan Tracking

- Prioritas tinggi:
    - Integrasi dropdown provinsi/kota RajaOngkir pada checkout store.
    - Simpan label kota/provinsi (bukan hanya city id).
    - Hardening callback iPaymu: idempotency key + retry-safe update status.
- Prioritas menengah:
    - Tambahkan audit log callback iPaymu yang lebih detail.
    - Notifikasi status order dasar (paid, cancelled) ke pembeli.
- Prioritas rendah:
    - Rapikan copywriting/validasi form checkout.

### Sprint 2 (Minggu 2) - Keamanan Akses Order dan Ebook

- Prioritas tinggi:
    - Verifikasi tracking order (minimal validasi phone/email pembeli).
    - Session token sekali pakai untuk halaman reader ebook.
- Prioritas menengah:
    - Watermark identitas pembeli pada reader ebook.
    - Batas perangkat/sesi untuk akses ebook.
- Prioritas rendah:
    - Penyempurnaan tampilan halaman tracking dan reader.

### Sprint 3 (Minggu 3) - Dashboard Customer dan Role Flow

- Prioritas tinggi:
    - Dashboard khusus customer (ringkasan order + status pembayaran).
    - Riwayat order dan invoice store per akun customer.
- Prioritas menengah:
    - Penyempurnaan flow upgrade customer/reader ke author dengan approval admin.
    - Checklist dokumen/verifikasi saat pengajuan upgrade author.
- Prioritas rendah:
    - Penambahan help text/SOP mini di area profile.

### Sprint 4 (Minggu 4) - Monetisasi Storefront

- Prioritas tinggi:
    - Implementasi kupon/voucher promo di storefront.
    - Laporan penjualan periodik (harian/mingguan/bulanan) + export.
- Prioritas menengah:
    - Auto-notify pembeli untuk status packed, shipped, completed.
    - Validasi status transition order yang lebih ketat di admin.
- Prioritas rendah:
    - Penyempurnaan komponen filter/order list admin.

### Sprint 5 (Minggu 5) - Fitur Pasca-Pembelian

- Prioritas tinggi:
    - Library ebook internal per customer (daftar ebook yang sudah dibeli).
    - Modul retur/refund order store (alur request dan approval).
- Prioritas menengah:
    - Sinkronisasi stok pada edge-case callback/refund.
    - Penambahan alasan status perubahan order secara terstruktur.
- Prioritas rendah:
    - Ringkasan metrik retur/refund di dashboard finance.

### Sprint 6 (Minggu 6) - Quality Engineering dan Skalabilitas

- Prioritas tinggi:
    - Stabilkan environment testing (sqlite driver atau test DB terdedikasi).
    - Tambah test coverage: payment callback, order flow, royalty payout.
- Prioritas menengah:
    - Tambah test keamanan endpoint tracking dan reader.
    - Tambah regression test untuk role customer/reader/author.
- Prioritas rendah:
    - Persiapan multi-payment gateway (desain interface + adapter contract).

### Backlog (Setelah Sprint 6)

- Implementasi multi-payment gateway penuh (provider tambahan selain iPaymu).
- Penyusunan SOP operasional lengkap per role dan per modul.
- Optimasi performa query dashboard/storefront untuk skala data besar.

## Instalasi Singkat

1. Install dependency:

```bash
composer install
npm install
```

2. Setup environment:

```bash
cp .env.example .env
php artisan key:generate
```

3. Konfigurasi database di .env lalu jalankan:

```bash
php artisan migrate
```

4. Jalankan aplikasi:

```bash
php artisan serve
npm run dev
```

## Catatan Pengembangan

- Beberapa fitur bergantung pada konfigurasi service eksternal di .env (iPaymu, RajaOngkir).
- Untuk test otomatis penuh, pastikan environment test DB siap dan extension database sesuai sudah aktif.

## Lisensi

Proyek ini mengikuti lisensi yang ditetapkan oleh pemilik repository.
