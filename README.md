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

### Update Terbaru (Juli 2026)

- Tool global baru: **Hitung Halaman Naskah Otomatis** (`/tools/manuscript-page-counter`) untuk semua role login.
    - Mendukung ukuran kertas A4, A5, B5, UNESCO.
    - Margin default 2 cm.
    - Mendukung mode komparasi dua ukuran dalam satu submit.
    - Tersedia preset ukuran cepat dan guard agar ukuran pembanding tidak bisa sama dengan ukuran utama.
- Otomasi biaya paket penulis berbasis halaman naskah DOCX:
    - Perhitungan A4 over limit (>125 hal) otomatis.
    - Perhitungan A5 cetak over limit (>100 hal) otomatis untuk paket yang mendukung print.
    - Komponen biaya tambahan layout/editing/print otomatis masuk ke total order dan invoice.
- Admin Naskah diperluas:
    - Form create/edit mendukung input **halaman mentah A4** wajib.
    - Upload DOCX opsional pada create/edit untuk auto-hitungan A4/A5.
    - List naskah menampilkan kolom halaman mentah, indikator over-limit, dan informasi paket/workflow yang lebih informatif.
    - Dashboard backoffice menampilkan panel Insight Naskah (agregasi A4/A5/over-limit).
- Papan Pribadi internal ditingkatkan menjadi kanban modern:
    - Drag-and-drop lebih halus, reorder antar/intra kolom, autosave tanpa reload, rollback saat gagal.
    - Tetap mempertahankan model hybrid (kartu sistem read-only + kartu manual milik user).
- UX Auth modern (MS Publishing-like):
    - Login/register/forgot/reset/verify/confirm memakai visual language yang konsisten.
    - Tambah toggle show/hide password.
    - Tambah indikator kekuatan password + checklist aturan realtime pada register/reset.

### Fitur yang Diupdate / Dikurangi

- Endpoint legacy approval buku `POST /books/{book}/approve/{type}` masih tersedia untuk kompatibilitas, namun **bukan** alur utama operasional.
- Alur utama Book Show tetap melalui Action Center orkestrasi endpoint workflow baru.

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
- Ruang File modern (Drive-like): view Table/Grid, folder chip, search/filter, drag-drop multi upload.
- Aksi file manager: rename, move antar folder role, copy link, share publik, dan pengaturan akses via modal.
- Semua file tetap terlihat lintas role (dengan indikator private/terbatas), namun preview/download mengikuti kontrol akses.
- Permission matrix per file: scope akses + role tambahan + whitelist email + whitelist domain.
- Audit log akses file (granted/denied) dengan filter aksi/hasil dan shortcut "Lihat Log" per file.

### 3. ISBN Queue dan Proses Approval

- Queue pengajuan ISBN.
- Proses submit/approve ISBN berdasarkan role berwenang.
- Kontrol readiness file sebelum submit/approval.
- Book Show kini menggunakan Action Center dengan endpoint orkestrasi utama:
    - `POST /books/{book}/workflow/execute-primary`
    - `POST /books/{book}/workflow/prepare-isbn`
- Endpoint legacy `POST /books/{book}/approve/{type}` masih tersedia sementara untuk kompatibilitas, namun tidak lagi menjadi alur utama UI.

### 4. Dashboard Operasional

- Dashboard backoffice (produksi, assignment, alert).
- Dashboard author (status buku, invoice, royalty, kontrak).
- Ringkasan finansial untuk area finance.
- Dashboard operasional gabungan untuk antrean Print, Ebook Publishing, Revisi, dan Adaptasi Cetak.
- Filter operasional multi-kriteria (channel, status, keyword, adaptasi, rentang tanggal, umur SLA).
- Alert SLA operasional dengan threshold per status (attention/critical) untuk prioritisasi antrean.
- Export CSV berdasarkan filter aktif untuk kebutuhan monitoring dan handover.
- Shortcut sidebar dinamis ke Dashboard Operasional dengan badge jumlah antrean aktif.

### 4.1 Papan Pribadi Internal (Mirip Trello)

- Papan kerja otomatis untuk role internal: `admin`, `editor`, `layouter`, `designer`, `isbn`, `owner`, `finance`, `superadmin`.
- Tidak tersedia untuk `author` dan `customer`.
- Memiliki 3 kolom kanban: `To Do`, `Scheduled`, `Done`.
- Kartu digenerate otomatis dari workload role (contoh: assignment produksi, antrean ISBN, invoice pending, review upgrade author, lead konsultasi paket).
- Setiap kartu menampilkan sumber pekerjaan dan tautan cepat ke modul asal.
- User internal tetap bisa menambah kartu manual pribadi (judul/catatan/prioritas/due date).
- Kartu manual dapat diedit, dipindah kolom, dan diarsip oleh pemilik kartu.
- Kartu otomatis bersifat read-only dari sisi user dan hanya berubah jika data di modul sumber berubah.
- Tersedia filter cepat papan pribadi berdasarkan prioritas dan due date (`overdue`, `hari ini`, `7 hari ke depan`, `tanpa due date`).
- Data tetap private per akun sesuai role user yang login.

### 12. Layout Generator dan Optimasi Kesiapan

- Filter kesiapan layout diterapkan di level query database (bukan post-processing halaman) agar pagination akurat.
- Validasi kesiapan layout menggunakan cache per buku dengan invalidasi versi saat section berubah.
- Ringkasan global kesiapan layout ditampilkan di index (total, siap, belum siap) sesuai filter aktif.
- Hardening proses generate DOCX dan template dengan fail-safe ketika template tidak tersedia atau proses build gagal.
- Optimasi indeks database untuk kesiapan layout:
    - `book_sections(book_id, section_type)`
    - `book_files(book_id, type, is_active)`

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
- Storefront juga menampilkan menu publik, section paket penerbitan, copywriting layanan, FAQ, dan shortcut lintas role yang relevan.

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
- Author dapat mengakses dashboard customer, riwayat order store, dan storefront melalui grup menu gabungan "Storefront & Customer".
- Author kini dapat mengakses Ruang File sesuai kebijakan akses file per item.

### 11. Branding Dasar

- Konfigurasi logo di area AdminLTE, auth screen, preloader, dan storefront.

### 13. Tool Produktivitas Naskah

- Hitung halaman naskah otomatis lintas role dari upload DOCX.
- Mendukung estimasi multi ukuran (A4/A5/B5/UNESCO) dengan basis margin konsisten 2 cm.
- Cocok untuk validasi cepat pra-order paket, pra-produksi, dan cross-check data naskah admin.

## Manual Book

Manual book sistem yang lebih lengkap tersedia di: [docs/manual-book-sistem.md](docs/manual-book-sistem.md)

Dokumen tersebut menjelaskan fungsi, cara pakai, dan alur tiap fitur per role secara lebih rinci.

## Status Implementasi

### Sudah Dikerjakan

- Dashboard customer, dashboard author, dan akses lintas role ke storefront/customer sudah diselaraskan.
- Redirect login/register dari storefront sudah mempertahankan konteks yang aman.
- Storefront publik sudah ditingkatkan dengan filter katalog, section paket penerbitan, FAQ, dan copywriting layanan.
- Fondasi monetisasi storefront sudah ada lewat pengelolaan promo price pada katalog admin dan filter operasional order.
- Laporan penjualan storefront sudah bisa diekspor ke CSV dengan filter tanggal dan status.
- Validasi status transition order storefront sudah dibuat lebih ketat.
- Notifikasi otomatis ke customer saat status order berubah sudah aktif.
- Voucher/promo code storefront sudah bisa dibuat, dikelola, dan dipakai saat checkout.
- Library ebook internal sudah tersedia untuk customer/reader yang sudah membeli ebook.
- Refund order store sudah bisa diajukan customer dan direview finance.
- Workflow upgrade customer/reader ke author sudah dilengkapi checklist, lampiran, review admin, notifikasi, dan ekspor.
- Ebook sudah diperlakukan sebagai produk tanpa stok fisik.
- Manual book sistem sudah tersedia di dokumen terpisah.
- Phase 1 storefront profesional sudah dimulai: CTA paket penerbitan diperjelas dan FAQ dipisah untuk customer/author.

### Masih Perlu Dikerjakan

- Verifikasi tambahan untuk tracking order publik.
- Idempotency key dan audit log callback iPaymu yang lebih lengkap.
- Watermark ebook dinamis berbasis identitas pembeli.
- Multi-payment gateway sebagai fallback selain iPaymu.
- Penguatan test coverage untuk callback, order flow, dan royalty.

## Fitur Yang Belum Sempurna (Known Gaps)

- Halaman tracking order publik belum memakai verifikasi tambahan (contoh OTP/email/telepon) untuk membatasi keterbukaan data.
- Pengelolaan stok dan kompensasi rollback stok saat skenario callback edge-case masih perlu hardening lebih lanjut.
- Notifikasi status order store belum lengkap untuk semua transisi penting.
- Dokumentasi operasional (SOP role per modul) belum menyeluruh.
- Reader ebook: watermark yang tampil saat ini adalah watermark statis MS Publishing + nomor order (bukan watermark identitas pembeli dinamis per pixel).
- Callback iPaymu belum memiliki idempotency key yang eksplisit dan audit log per event.
- Dropdown provinsi/kota RajaOngkir fallback ke data dummy jika API key belum disetel.
- Monitoring performa query (EXPLAIN plan) untuk Layout Generator belum terdokumentasi sebagai artefak rutin.

## To-Do Fitur Baru (Belum Ada)

- Tambah verifikasi tracking order (OTP WA/email atau minimal validasi phone/email pembeli).
- Tambah fitur multi-payment gateway (fallback selain iPaymu).
- Tambah laporan penjualan storefront periodik (harian/mingguan/bulanan) dengan export.
- Tambah fitur reset reader session per order di halaman admin store orders.
- Tambah throttling percobaan password reader (anti brute-force).
- Tambah watermark dinamis berbasis identitas pembeli (nama/email di setiap halaman PDF).

## To-Do Penyempurnaan Fitur Yang Sudah Ada

- Penyempurnaan role customer/reader:
    - Flow upgrade ke author dengan approval admin (opsional) dan checklist dokumen.
    - Help text atau SOP mini di area profile bila masih diperlukan.
- Penyempurnaan admin order:
    - Tombol reset reader session per order.
- Penyempurnaan quality engineering:
    - Perbaikan environment test (sqlite driver atau dedicated test DB).
    - Tambah test coverage untuk payment callback, order flow, royalty payout.
    - Tambah test keamanan endpoint reader ebook (token, device hash).

## Roadmap Storefront Profesional (Author + Customer)

Target roadmap ini adalah menjadikan storefront lebih mirip website penerbitan profesional dengan dua funnel yang berjalan paralel:

- Funnel Author: pemesanan paket penerbitan dan layanan produksi.
- Funnel Customer: pembelian buku cetak/ebook dengan pengalaman retail yang lengkap.

### Phase 1 - Quick Wins (Paling Mungkin Dikerjakan Dulu)

- Paket penerbitan bertingkat di storefront (Basic/Standard/Premium/Custom).
- Product page lebih kaya: preview isi, profil penulis, rekomendasi judul terkait.
- Checkout lebih profesional: ringkasan biaya transparan, catatan promo/voucher, invoice jelas.
- Halaman kebijakan storefront (refund, pengiriman, hak cipta, FAQ tersegmentasi author/customer).

### Phase 2 - Mid (Operasional dan Konversi)

- Configurator paket penerbitan dengan estimasi biaya otomatis.
- Dashboard author untuk tracking milestone produksi paket.
- Wishlist, notifikasi restock/pre-order, dan rekomendasi pembelian lanjutan untuk customer.
- Ticketing support untuk pertanyaan editorial, billing, dan teknis order.

### Phase 3 - Advanced (Scale dan Growth)

- Multi-payment gateway dengan fallback provider.
- Loyalty dan referral program (author-to-author, reader-to-reader).
- Marketing automation (journey email/WA) untuk lead author dan repeat buyer customer.
- Analitik funnel end-to-end (visit -> checkout -> repeat purchase).

### KPI Utama Storefront

- Conversion rate lead author -> deal paket.
- Conversion rate cart -> order paid.
- Cart abandonment rate.
- Refund rate dan waktu penyelesaian refund.
- Repeat purchase rate customer.

### Langkah Implementasi Bertahap (Start Sekarang)

Urutan kerja awal yang direkomendasikan:

1. Finalisasi halaman paket penerbitan + CTA konsultasi.
2. Penyempurnaan product detail (preview + profil penulis + related books).
3. Kebijakan storefront (refund/shipping/FAQ author-customer) dalam halaman terpisah.
4. Pengerjaan configurator paket sebagai fase lanjutan setelah konten dasar stabil.

Progress terbaru:

- ✅ Step 1 sudah diimplementasikan di landing storefront (CTA konsultasi paket lebih tegas per role).
- ✅ FAQ customer dan author sudah dipisah untuk mengurangi kebingungan alur.
- ✅ Step 2 selesai: detail halaman produk sudah diperkaya dengan preview isi, profil author, dan rekomendasi yang lebih informatif.
- ✅ Step 3 selesai: kebijakan storefront dipisah ke halaman khusus agar dokumentasi layanan lebih rapi.
- ✅ Step 4 MVP selesai: configurator paket penerbitan sudah live (kalkulasi estimasi + simpan request lead konsultasi).
- ✅ Layanan opsional configurator sekarang dinamis dari master Additional Services (tidak statis hardcoded).
- ✅ Tahap berikutnya sudah berjalan: finance/admin bisa memantau lead configurator dan update status follow-up.
- ✅ Follow-up finance sudah ditingkatkan: ada catatan internal dan tanggal next action per lead (dengan penanda due/overdue).
- ✅ Ruang File telah ditingkatkan menjadi file manager modern: table/grid, rename/move, drag-drop multi upload, dan filter folder.
- ✅ Kontrol akses Ruang File kini mendukung scope private/role/all_roles/public + permission matrix per role/email/domain.
- ✅ Audit log akses file aktif (preview/download/shared) beserta panel monitoring dan filter log per file.

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

### Sprint 3 (Minggu 3) - Dashboard Customer dan Role Flow ✅ SELESAI

- ✅ Dashboard khusus customer (ringkasan order + status pembayaran).
- ✅ Riwayat order dan invoice store per akun customer.
- ✅ Penyempurnaan flow upgrade customer/reader ke author dengan approval admin.
- ✅ Checklist dokumen/verifikasi saat pengajuan upgrade author.
- Catatan: help text/SOP mini di area profile masih bisa ditambahkan sebagai polish opsional.

### Sprint 4 (Minggu 4) - Monetisasi Storefront ✅ SELESAI

- ✅ Fondasi monetisasi storefront sudah tersedia melalui promo price di katalog admin dan filter operasional order.
- ✅ Laporan penjualan storefront bisa diekspor ke CSV dengan filter tanggal dan status.
- ✅ Penyempurnaan komponen filter/order list admin (operational dashboard filter + SLA + export + pagination per queue).
- ✅ Auto-notify customer saat status order berubah.
- ✅ Validasi status transition order yang lebih ketat di admin.
- ✅ Voucher/promo code storefront sudah bisa dibuat, dikelola, dan dipakai saat checkout.

### Sprint 7 (Minggu 7) - Orkestrasi Workspace Produksi (IN PROGRESS)

- ✅ Workspace Printing: status transition, revisi, final file, notifikasi, shipping tracking.
- ✅ Workspace Ebook Publishing: status transition, revisi, notifikasi, audit trail.
- ✅ Routing package channel (print/ebook) + fallback adaptasi cetak untuk buku paket ebook-only.
- ✅ Dashboard Operasional gabungan + filter lanjutan + export CSV + shortcut sidebar.
- ✅ Penyederhanaan widget produksi lama yang overlap dengan dashboard operasional baru.
- ✅ Penajaman SLA alert berbasis threshold per status antrean.
- Next:
    - Otomasi reminder SLA critical per status ke role terkait.
    - Penyelarasan visual indikator SLA antar halaman dashboard/workspace.

### Sprint 8 (Minggu 8) - Layout Generator Performance & Reliability (IN PROGRESS)

- ✅ Query-level readiness filter untuk menjaga akurasi pagination.
- ✅ Cache validasi kesiapan layout per buku + invalidasi eksplisit saat section berubah.
- ✅ Ringkasan global kesiapan layout di halaman index.
- ✅ Hardening generate layout/template dengan fail-safe dan error handling.
- ✅ Optimasi indeks readiness pada tabel section/file.
- Next:
    - Dokumentasi baseline EXPLAIN query untuk mode base/ready/not_ready.
    - Penambahan metrik hit-rate cache validasi layout.

### Sprint 5 (Minggu 5) - Fitur Pasca-Pembelian ✅ SELESAI

- ✅ Library ebook internal per customer (daftar ebook yang sudah dibeli).
- ✅ Modul retur/refund order store (alur request customer dan review finance).
- ✅ Sinkronisasi stok pada edge-case refund.
- ✅ Penambahan alasan status perubahan order secara terstruktur.
- ✅ Ringkasan metrik refund di dashboard finance melalui status/riwayat order.
- ✅ Navigasi storefront/finance/customer sudah diselaraskan untuk alur pasca-pembelian.
- ✅ Dokumentasi manual book sudah diperbarui untuk library ebook dan refund.

Sprint 5 ditutup sebagai baseline fitur pasca-pembelian. Fokus berikutnya diarahkan ke quality engineering, stabilisasi test environment, dan perluasan regresi untuk alur pembayaran serta reader.

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

## Lisensi

Repositori ini menggunakan lisensi GNU General Public License v3.0.

Lihat file [LICENSE](LICENSE) untuk teks lisensi lengkap.

- Implementasi multi-payment gateway penuh (provider tambahan selain iPaymu).
- Penyusunan SOP operasional lengkap per role dan per modul.
- Optimasi performa query dashboard/storefront untuk skala data besar.

## Changelog

### v1.5 - 2026-07-01: Layout Generator Optimization and SLA Refinement

**Dashboard Operasional**

- SLA operasional ditingkatkan menjadi threshold berbasis status sehingga prioritas antrean lebih presisi.
- Panel legacy production monitoring tetap tersedia namun tidak lagi mendominasi tampilan operasional utama.

**Layout Generator**

- Filter readiness dipindahkan ke query level untuk konsistensi pagination dan hasil pencarian.
- Ditambahkan cache validasi per buku dengan invalidasi eksplisit saat section ditambah, diubah, dihapus, atau diurut ulang.
- Ditambahkan ringkasan global kesiapan layout sesuai filter agar monitoring tidak terbatas pada halaman aktif.
- Proses generate DOCX/template diperkuat dengan fail-safe template path dan penanganan exception.
- Ditambahkan indeks komposit pada tabel section/file untuk mempercepat query readiness.

**Database Migrations**

- `2026_07_01_150000_add_layout_generator_readiness_indexes` — menambahkan indeks komposit untuk kebutuhan readiness filter Layout Generator.

### v1.6 - 2026-07-01: Sprint 5 Completion and Post-Purchase Finalization

**Post-Purchase Workflow**

- Library ebook customer diselesaikan sebagai akses cepat ke ebook yang sudah dibeli.
- Alur refund storefront dilengkapi dengan request dari customer dan review finance.
- Menu finance storefront dipusatkan dengan voucher, order, dan export penjualan.
- Dokumentasi manual book ditambah untuk prosedur library ebook dan refund.

**Database**

- `2026_07_01_180000_add_refund_fields_to_store_orders_table` — menambahkan field refund untuk workflow pasca-pembelian.

### v1.4 - 2026-07-01: Production Operations Workspace and Dashboard

**Operasional Produksi dan Workspace**

- Workspace Printing ditingkatkan dengan status transition guard, alur revisi, final file flow, dan tracking pengiriman.
- Workspace Ebook Publishing ditambahkan dengan alur status, revisi, dan notifikasi operasional.
- Audit trail status order ditambahkan agar perubahan status antar role dapat dilacak.
- Orkestrasi order dari paket publishing kini mempertimbangkan channel print/ebook, termasuk skenario adaptasi cetak untuk pesanan print dari paket ebook-only.

**Dashboard Operasional**

- Dashboard produksi kini memiliki panel operasi gabungan untuk antrean print, ebook, revisi, dan adaptasi cetak.
- Filter operasional ditambah: channel, status, adaptasi, keyword, tanggal mulai/akhir, umur SLA, dan baris per queue.
- Setiap queue operasional mendukung pagination sendiri agar data panjang tetap terbaca.
- Export CSV berdasarkan filter aktif tersedia melalui endpoint dashboard operasi.
- Shortcut menu dinamis "Dashboard Operasional" ditambahkan ke sidebar dengan badge beban antrean aktif.

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
