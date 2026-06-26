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
- Rate royalti menerima input format desimal (0–1) maupun persen (0–100), otomatis dinormalisasi.
- Validasi dan pesan error tampil langsung di form jika submit gagal.

### 8. Storefront Publik

- Katalog publik buku dengan detail halaman produk (UI modern, cover upload).
- Pemesanan buku dari publik dengan form terstruktur per section.
- Integrasi checkout iPaymu untuk order storefront.
- Callback iPaymu untuk update status order storefront.
- Tracking order publik berdasarkan nomor order.
- Dropdown provinsi/kota RajaOngkir saat checkout produk cetak (AJAX, fallback dummy).
- Label provinsi/kota disimpan di order, bukan hanya city ID.
- Preview estimasi total harga real-time mengikuti perubahan qty.

### 9. Penjualan Ebook dan Bundling Print + Ebook

- Tiga tipe produk: Print, Ebook, dan Print + Ebook (bundle).
- Untuk produk Print + Ebook, customer memilih format sendiri saat checkout:
    - **Print**: form pengiriman muncul, estimasi harga print.
    - **Ebook**: form akses ebook muncul, estimasi harga ebook.
- Admin dapat menetapkan harga terpisah untuk ebook (`ebook_price`, `ebook_promo_price`).
- Upload file cover (gambar) dan PDF ebook langsung dari form admin.
- Cover ditampilkan di halaman list dan detail storefront.
- Reader page internal berbasis password.
- Keamanan reader ebook:
    - Token akses sekali pakai (10 menit, otomatis invalid setelah digunakan).
    - Batas perangkat dan sesi: hanya perangkat/sesi pertama yang terdaftar yang bisa membuka ebook.
    - Watermark MS Publishing + nomor order di halaman reader.

### 10. Manajemen Role dan Akses

- Role utama: admin, editor, layouter, designer, isbn, author, owner, finance, superadmin.
- Role tambahan: customer, reader.
- Register default ke customer.
- Upgrade akun customer/reader ke author dari halaman profile (dengan syarat data tertentu).

### 11. Branding Dasar

- Konfigurasi logo di area AdminLTE, auth screen, preloader, dan storefront.

## Fitur Yang Belum Sempurna (Known Gaps)

- Halaman tracking order publik belum memakai verifikasi tambahan (contoh OTP/email/telepon) untuk membatasi keterbukaan data.
- Pengelolaan stok dan kompensasi rollback stok saat skenario callback edge-case masih perlu hardening lebih lanjut.
- Notifikasi status order store belum lengkap untuk semua transisi penting.
- Dokumentasi operasional (SOP role per modul) belum menyeluruh.
- Reader ebook: watermark yang tampil saat ini adalah watermark statis MS Publishing + nomor order (bukan watermark identitas pembeli dinamis per pixel).
- Callback iPaymu belum memiliki idempotency key yang eksplisit dan audit log per event.
- Dropdown provinsi/kota RajaOngkir fallback ke data dummy jika API key belum disetel.

## To-Do Fitur Baru (Belum Ada)

- Tambah verifikasi tracking order (OTP WA/email atau minimal validasi phone/email pembeli).
- Tambah halaman akun customer untuk melihat riwayat order dan invoice store.
- Tambah library ebook internal (list ebook yang sudah dibeli per akun).
- Tambah fitur kupon/voucher promo di storefront.
- Tambah fitur multi-payment gateway (fallback selain iPaymu).
- Tambah laporan penjualan storefront periodik (harian/mingguan/bulanan) dengan export.
- Tambah modul retur/refund order store.
- Tambah fitur reset reader session per order di halaman admin store orders.
- Tambah throttling percobaan password reader (anti brute-force).
- Tambah watermark dinamis berbasis identitas pembeli (nama/email di setiap halaman PDF).

## To-Do Penyempurnaan Fitur Yang Sudah Ada

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
    - Tombol reset reader session per order.
- Penyempurnaan quality engineering:
    - Perbaikan environment test (sqlite driver atau dedicated test DB).
    - Tambah test coverage untuk payment callback, order flow, royalty payout.
    - Tambah test keamanan endpoint reader ebook (token, device hash).

## Roadmap Sprint Mingguan

Roadmap ini disusun agar item kritikal diselesaikan lebih dulu, lalu diikuti peningkatan UX, keamanan, dan ekspansi fitur bisnis.

### Sprint 1 (Minggu 1) - Stabilitas Checkout dan Tracking ✅ SELESAI

- ✅ Integrasi dropdown provinsi/kota RajaOngkir pada checkout store.
- ✅ Simpan label kota/provinsi (bukan hanya city id).
- ✅ Form checkout modern dan terstruktur per section.
- ✅ Preview estimasi harga real-time.
- Pending: Hardening callback iPaymu (idempotency key + audit log).
- Pending: Notifikasi status order otomatis ke pembeli.

### Sprint 2 (Minggu 2) - Keamanan Akses Order dan Ebook ✅ SELESAI

- ✅ Session token sekali pakai untuk reader ebook (10 menit, single-use).
- ✅ Batas perangkat/sesi: device hash + session ID lock.
- ✅ Watermark MS Publishing + nomor order pada halaman reader.
- Pending: Verifikasi tracking order (OTP/email/telepon).
- Partial: Watermark identitas pembeli (saat ini statis, belum dinamis per pembeli).

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

## Changelog

### v1.3 — 2026-06-26: Storefront Commerce & Security Hardening

**Storefront — Catalog & Produk**

- Tipe produk `print_ebook` ditambahkan. Admin dapat menandai satu item sebagai bundle Print + Ebook dengan harga masing-masing (`ebook_price`, `ebook_promo_price`).
- Upload file cover (gambar: jpg/jpeg/png/webp) dan PDF ebook langsung dari form admin (menggantikan input URL/path manual).
- Cover ditampilkan di list dan detail storefront; fallback ke placeholder judul jika belum diunggah.
- Enum kolom `product_type` diperluas lewat migration.

**Storefront — Checkout**

- Dropdown Provinsi dan Kota/Kabupaten berbasis RajaOngkir (AJAX, fallback dummy saat API key kosong).
- Label provinsi/kota disimpan di order (`shipping_destination_province_name`, `shipping_destination_city_name`).
- Untuk produk Print + Ebook, customer memilih format (Print atau Ebook) sebelum checkout. Form menyesuaikan secara dinamis (shipping fields / ebook password fields).
- Harga berdasarkan format yang dipilih dikirim ke backend.
- Preview estimasi total real-time mengikuti perubahan qty.
- Form checkout direfaktor menjadi section terstruktur: Identitas Pembeli, Pengiriman Cetak, Akses Ebook, Catatan Tambahan.

**Storefront — Keamanan Reader Ebook**

- Akses reader kini melalui token sekali pakai (berlaku 10 menit, otomatis invalid setelah satu kali dipakai).
- Batas satu perangkat/sesi: device hash (UA + IP) dan session ID dikunci ke perangkat pertama yang mengakses.
- Watermark "MS Publishing • {nomor order}" pada halaman reader sebagai lapisan proteksi visual.
- Halaman tracking menampilkan label kota/provinsi, bukan hanya city ID.

**Admin Catalog**

- Form tambah dan quick-update item store kini mendukung `print_ebook` sebagai tipe produk.
- Field harga ebook muncul otomatis (JS toggle) saat tipe Print + Ebook dipilih.
- Validasi form menampilkan pesan error langsung di halaman jika submit gagal.
- Item store dapat dibuat tanpa harus memilih buku naskah atau legacy (item manual).

**Legacy Catalog**

- Rate royalti menerima input desimal (0–1) maupun persen (0–100), otomatis dinormalisasi ke format database.
- Pesan error validasi ditampilkan langsung di form.
- Nilai input dipertahankan saat validasi gagal (old input).

**Database Migrations**

- `2026_06_26_030000_expand_store_catalog_product_type_enum` — enum `product_type` diperluas dengan nilai `print_ebook`.
- `2026_06_26_034000_add_shipping_labels_and_reader_security_to_store_orders_table` — kolom label provinsi/kota + kolom keamanan reader (token, device hash, session ID).
- `2026_06_26_040000_add_ebook_price_and_selected_format_to_storefront_tables` — harga ebook terpisah di `store_catalog_items` dan format terpilih di `store_orders`.

**Routes Baru**

- `GET /store/shipping/cities` (`store.shipping.cities`) — endpoint AJAX kota RajaOngkir.
- `GET /store/track/{orderNumber}/reader` (`store.reader.view`) — halaman reader dengan validasi token sekali pakai.

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
