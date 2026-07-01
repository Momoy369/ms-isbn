# Manual Book MS ISBN Publishing System

Dokumen ini menjelaskan fungsi, kegunaan, dan cara penggunaan setiap fitur utama pada sistem MS ISBN Publishing System.

## Cara Membaca Dokumen

Setiap fitur pada manual ini idealnya dibaca dengan pola berikut:

- Tujuan: fungsi utama fitur.
- Prasyarat: kondisi yang harus terpenuhi sebelum fitur dipakai.
- Langkah: urutan penggunaan fitur.
- Hasil: output atau perubahan yang diharapkan.
- Catatan: hal yang perlu diwaspadai atau error umum.

Dokumen ini tetap mempertahankan ringkasan fitur per modul agar mudah dipakai sebagai panduan operasional harian.

## Perubahan Terbaru Sistem (Juli 2026)

### Fitur Ditambahkan

- Tool lintas role: **Hitung Halaman Naskah Otomatis** dengan upload DOCX.
- Opsi ukuran kertas pada tool: A4, A5, B5, UNESCO (margin tetap 2 cm).
- Mode komparasi dua ukuran dalam satu submit.
- Preset cepat ukuran kertas pada tool hitung halaman.
- Checklist aturan password realtime + indikator kekuatan password pada register/reset.

### Fitur Diupdate

- Halaman auth diperbarui dengan UI modern konsisten (login/register/forgot/reset/verify/confirm).
- Modul order paket author kini otomatis menghitung biaya tambahan dari halaman A4/A5 (sesuai rule paket).
- Modul admin naskah (create/edit/list/dashboard) kini menampilkan dan memproses metrik halaman mentah naskah lebih lengkap.
- Papan pribadi internal diperhalus (drag-drop, reorder, autosave, no reload).

### Fitur Legacy (Masih Ada untuk Kompatibilitas)

- Endpoint lama approval `POST /books/{book}/approve/{type}` tetap tersedia tetapi tidak menjadi alur utama Action Center.

## 1. Ringkasan Sistem

MS ISBN Publishing System adalah platform penerbitan end-to-end untuk mengelola naskah, workflow produksi, ISBN, invoice, royalti, storefront publik, dan akses customer/author.

### Tujuan Utama

- Menyatukan proses penerbitan dalam satu sistem.
- Menjaga workflow per role agar transparan dan terukur.
- Menyediakan storefront publik untuk buku jadi dan paket penerbitan.
- Memberikan dashboard khusus untuk admin, author, customer, dan tim produksi.

## 2. Hak Akses Role

### Admin / Superadmin

- Mengelola semua data sistem.
- Memantau dashboard operasional, finance, dan moderasi upgrade author.
- Mengelola storefront catalog, order store, dan paket penerbitan.

### Editor / Layouter / Designer

- Mengelola assignment produksi sesuai role.
- Mengunggah file kerja dan menyelesaikan tugas pada workspace produksi.
- Memantau aktivitas dan revisi.

### ISBN / Finance / Owner

- Mengelola approval ISBN, invoice, pembayaran, royalty, dan laporan finansial.
- Memantau order storefront dan aktivitas komersial.

### Author

- Memantau dashboard author.
- Mengakses dashboard customer, storefront, dan riwayat order/invoice store.
- Mengajukan claim buku, invoice, royalti, dan upgrade profile.

### Customer / Reader

- Melihat dashboard customer.
- Melacak order dan invoice store.

## 3. Alur Umum Penggunaan

1. Login sesuai role.
2. Buka dashboard utama role.
3. Gunakan menu sidebar atau shortcut untuk berpindah ke modul yang dibutuhkan.
4. Lakukan aksi sesuai tugas: produksi, approval, pembayaran, order store, atau konsultasi paket.
5. Gunakan notifikasi sistem untuk memantau perubahan status.

## 3.1 Quick Start per Role

### Admin / Superadmin

- Masuk ke dashboard internal.
- Cek dashboard operasional, review upgrade author, storefront catalog, dan order store.

- Masuk ke dashboard author.
- Gunakan grup menu "Storefront & Customer" untuk pindah ke dashboard customer, riwayat order, atau storefront.

### Customer / Reader

- Masuk ke dashboard customer.

### Tim Produksi

- Buka dashboard operasional atau workspace masing-masing role.
- Periksa assignment, revisi, dan file final.

### Finance / ISBN / Owner

- Buka modul finance, invoice, royalty, dan ISBN queue.
- Lakukan approval sesuai kewenangan.
- Gunakan laporan/CSV untuk kontrol administrasi.

## 4. Manual Fitur Per Modul

### 4.1 Dashboard Produksi / Operasional

#### Kegunaan

- Memantau progress buku dari naskah masuk sampai selesai.
- Mengetahui assignment aktif, terlambat, dan status review.
- Mengontrol antrean kerja operasional.

#### Cara Pakai

1. Buka dashboard produksi dari sidebar role operasional.
2. Lihat kartu ringkasan untuk mengetahui status keseluruhan.
3. Gunakan tabel assignment atau timeline untuk memeriksa progres tiap buku.
4. Cek alert untuk assignment yang melewati deadline.

#### Prasyarat

- User login dengan role yang berwenang.
- Data buku dan assignment sudah tersedia.

#### Hasil

- Status produksi terlihat dalam satu panel.
- Antrian dan risiko keterlambatan dapat diprioritaskan.

#### Catatan

- Jika angka antrian tidak sesuai, cek filter atau status assignment terakhir.

#### Output yang Dihasilkan

- Ringkasan workflow.
- Daftar assignment aktif dan terlambat.
- Riwayat aktivitas produksi.

### 4.2 Assignment Management

#### Kegunaan

- Menetapkan tugas ke editor, layouter, dan designer.
- Menyimpan riwayat penugasan dan penyelesaian tugas.

#### Cara Pakai

1. Buka halaman assignment.
2. Pilih buku dan role.
3. Isi nama PIC, deadline, dan catatan.
4. Simpan assignment.
5. PIC menyelesaikan tugas dari workspace masing-masing.

### 4.3 Workspace Produksi File

#### Kegunaan

- Mengunggah file kerja per role.
- Mengelola file final dan revisi.

#### Cara Pakai

1. Masuk ke workspace sesuai role.
2. Pilih buku yang sedang dikerjakan.
3. Upload file kerja atau file final sesuai tahap.
4. Gunakan catatan untuk menjelaskan revisi atau perubahan.

### 4.3.1 Ruang File (File Manager Modern)

#### Kegunaan

- Menjadi pusat penyimpanan file lintas role dengan pola folder yang konsisten.
- Menampilkan semua file (cross-role visibility) untuk kebutuhan koordinasi tim.
- Menjaga keamanan akses baca menggunakan scope + whitelist.

#### Fitur Utama

- Tampilan Table/Grid.
- Pencarian dan filter folder.
- Drag-drop multi upload.
- Aksi file: rename, move, share link, atur akses.
- Shortcut `Lihat Log` per file untuk audit cepat.

#### Aturan Akses

- `private`: hanya owner file + superadmin + user yang ada di whitelist.
- `role`: role file terkait + whitelist.
- `all_roles`: semua role internal.
- `public`: bisa diakses publik via link share.

Whitelist tambahan per file:

- Role tambahan.
- Email spesifik.
- Domain email (contoh: kampus.ac.id).

#### Cara Pakai Singkat

1. Buka menu Ruang File.
2. Pilih mode view (Table/Grid) sesuai kebutuhan.
3. Upload file (bisa multi file) dan pilih scope akses.
4. Jika perlu, klik `Akses` untuk atur whitelist role/email/domain.
5. Gunakan `Lihat Log` untuk memeriksa riwayat akses file tertentu.

#### Catatan

- Semua file tetap terlihat agar kolaborasi lintas tim lancar.
- Jika user tidak berhak baca, sistem menampilkan peringatan akses private/terbatas.
- Preview/download akan ditolak jika tidak lolos aturan akses.

### 4.3.2 Audit Log Akses File

#### Kegunaan

- Merekam siapa mengakses file, kapan, dan apakah diizinkan.
- Membantu investigasi akses gagal (forbidden/private/expired).

#### Data yang Tercatat

- waktu akses
- aksi (`preview`, `download`, `shared-preview`, dll.)
- user/email/role
- hasil (`granted`/`denied`)
- scope akses saat itu
- IP address
- catatan sistem (mis. `forbidden_by_scope`, `expired`, `file_not_found`)

#### Cara Pakai

1. Buka panel `Audit Log Akses File` di halaman Ruang File.
2. Filter berdasarkan aksi dan hasil.
3. Untuk audit spesifik, klik `Lihat Log` pada file yang ingin diperiksa.

### 4.4 ISBN Queue dan Approval

#### Kegunaan

- Menentukan buku mana yang sudah siap diajukan ISBN.
- Mencegah submit jika checklist belum lengkap.

#### Cara Pakai

1. Buka queue ISBN.
2. Periksa status readiness file dan section.
3. Submit buku ke ISBN jika semua syarat terpenuhi.
4. Approve atau reject dari role berwenang.

#### Pembaruan Alur Book Show (Action Center)

- Action Center di Book Show kini menjalankan alur utama lewat orkestrasi endpoint terpusat:
    - `POST /books/{book}/workflow/execute-primary`
    - `POST /books/{book}/workflow/prepare-isbn`
- Keputusan aksi utama ditentukan otomatis berdasarkan status workflow aktif (next/audit/submit/verify/author approval).
- Jika tombol utama nonaktif, alasan blocker ditampilkan langsung pada panel yang sama.

#### Catatan Transisi

- Endpoint lama `POST /books/{book}/approve/{type}` masih dipertahankan untuk kompatibilitas.
- Gunakan endpoint tersebut hanya untuk kebutuhan legacy; operasional harian mengikuti Action Center.

### 4.5 Layout Generator

#### Kegunaan

- Menyusun layout buku secara sistematis.
- Mengecek kesiapan layout sebelum generate file DOCX/template.

#### Cara Pakai

1. Buka halaman Layout Generator.
2. Tambah atau ubah section buku.
3. Cek indikator readiness.
4. Generate template atau dokumen layout.

#### Catatan

- Kesiapan layout dihitung di level query agar pagination tetap akurat.
- Cache validasi dipakai untuk mempercepat halaman.

### 4.6 Dashboard Operasional Gabungan

#### Kegunaan

- Menggabungkan antrean print, ebook, revisi, dan adaptasi cetak.
- Memudahkan monitoring SLA.

#### Cara Pakai

1. Buka dashboard operasional.
2. Atur filter channel, status, keyword, atau rentang tanggal.
3. Gunakan export CSV untuk handover atau laporan.
4. Pantau label SLA untuk menentukan prioritas kerja.

### 4.7 Storefront Publik

#### Kegunaan

- Menjadi etalase publik untuk buku jadi.
- Menjadi kanal pembelian print, ebook, dan print + ebook.

#### Cara Pakai

1. Buka halaman storefront publik.
2. Gunakan filter katalog:
    - tipe produk
    - sort
    - harga minimum/maksimum
    - unggulan
    - promo
3. Buka detail produk untuk melihat harga, format, dan rekomendasi.
4. Checkout dengan data pembeli, pengiriman, dan password ebook jika diperlukan.

#### Prasyarat

- Item store harus aktif.
- Untuk produk ebook, file PDF ebook harus tersedia.
- Untuk produk print, data ongkir dan alamat pembeli perlu diisi.

#### Hasil

- Customer melihat katalog yang dapat difilter.
- Customer dapat checkout sesuai format yang dipilih.

#### Catatan

- Ebook tidak menggunakan stok.
- Produk print + ebook menampilkan pilihan format saat checkout.

#### Voucher Promo Storefront

- Customer dapat memasukkan kode voucher pada form checkout.
- Sistem akan memvalidasi masa aktif, batas pemakaian, jenis produk, dan minimal subtotal.
- Diskon diterapkan ke subtotal produk sebelum total akhir dikirim ke gateway pembayaran.

#### Fitur Tambahan Storefront

- Menu publik yang relevan.
- Copywriting yang menjelaskan layanan dan alur kerja.
- Section paket penerbitan.
- FAQ singkat.
- Badge format pada produk print + ebook.

### Library Ebook Internal

#### Kegunaan

- Menampilkan daftar ebook yang sudah dibeli customer/reader.
- Menjadi pintu masuk cepat untuk membuka reader ebook tanpa mencari order satu per satu.

#### Cara Pakai

1. Buka menu Library Ebook dari dashboard customer atau sidebar.
2. Lihat daftar ebook yang statusnya sudah aktif.
3. Masukkan password baca pada baris ebook yang ingin dibuka.
4. Sistem akan mengarahkan ke halaman reader jika password benar.

#### Catatan

- Daftar ini hanya menampilkan ebook yang sudah dibayar dan memiliki akses baca.
- Password baca tetap diperlukan untuk menjaga keamanan akses.

### Refund Order Store

#### Kegunaan

- Memberi customer jalan untuk mengajukan refund setelah order dibayar.
- Memberi finance alur review untuk menyetujui atau menolak permintaan refund.

#### Cara Pakai

1. Buka detail order di dashboard customer.
2. Isi alasan refund dan kirim permintaan.
3. Finance menerima notifikasi dan meninjau permintaan pada halaman order store.
4. Finance menyetujui atau menolak refund beserta catatan.

#### Status Refund

- `requested`: customer mengajukan refund.
- `approved`: finance menyetujui refund dan order dibatalkan.
- `rejected`: finance menolak refund.

#### Catatan

- Refund yang disetujui akan mengembalikan stok print jika order memang berupa pembelian print.
- Fitur ini adalah alur refund operasional internal, bukan integrasi otomatis ke payment gateway.

### 4.8 Papan Pribadi Internal (Board Kerja Otomatis)

#### Kegunaan

- Menampilkan workload aktual per role dalam bentuk board kanban sederhana.
- Mengurangi pekerjaan yang terlewat karena tugas diambil langsung dari modul operasional.
- Menyediakan prioritisasi cepat berbasis due date dan urgensi status.
- Tetap memberi ruang catatan/planning manual personal untuk kebutuhan non-transaksional.

#### Siapa yang Bisa Mengakses

- Role internal: `admin`, `editor`, `layouter`, `designer`, `isbn`, `owner`, `finance`, `superadmin`.
- Role `author` dan `customer` tidak memiliki akses fitur ini.

#### Cara Pakai

1. Buka menu `Papan Pribadi` dari sidebar.
2. Sistem otomatis memuat kartu sesuai role user dari sumber data kerja terkait:
    - Assignment produksi (editor/layouter/designer).
    - Antrean ISBN (role ISBN dan role pengawas internal).
    - Invoice pending dan lead konsultasi paket (finance dan role pengawas internal).
    - Review upgrade author (admin/isbn/superadmin).
3. Jika perlu, tambahkan kartu manual pribadi (judul/catatan/prioritas/due date) untuk agenda pribadi.
4. Gunakan filter cepat untuk menyaring semua kartu berdasarkan prioritas dan due date (`Overdue`, `Hari Ini`, `7 Hari ke Depan`, `Tanpa Due Date`).
5. Untuk kartu manual, Anda bisa edit, pindah kolom, atau arsip sesuai kebutuhan.
6. Klik tombol sumber pada kartu otomatis untuk membuka modul asal dan memproses pekerjaan.

#### Catatan Penting

- Board bersifat hybrid: kartu sistem read-only, kartu manual bisa dikelola oleh pemiliknya.
- Kartu bersifat private per akun sesuai role login, bukan board kolaboratif antar user.
- Perubahan status kartu mengikuti perubahan data pada modul asal, bukan diubah langsung dari board.

### 4.9 Storefront Order Tracking

#### Kegunaan

- Melacak status order berdasarkan nomor order.
- Melihat detail payment reference, ongkir, dan status pengiriman.

#### Cara Pakai

1. Buka menu Lacak Pesanan.
2. Masukkan nomor order.
3. Buka detail hasil tracking.
4. Jika status belum lunas, lanjutkan pembayaran dari gateway yang tersedia.

### 4.10 Customer Dashboard

#### Kegunaan

- Menampilkan ringkasan order customer.
- Menunjukkan status pembayaran dan pengeluaran.
- Menampilkan status pengajuan upgrade author.

#### Cara Pakai

1. Login sebagai customer atau reader.
2. Buka Dashboard Customer.
3. Gunakan tombol cepat untuk kembali ke storefront atau melacak pesanan.
4. Buka riwayat order untuk detail invoice dan status.

#### Prasyarat

- Login dengan role customer, reader, atau author yang diizinkan akses customer dashboard.

#### Hasil

- Ringkasan order dan aktivitas pembayaran terlihat di satu tempat.

#### Catatan

- Jika login dari storefront sebagai staff, sistem tetap mengarahkan ke panel internal.

### 4.11 Customer Order & Invoice Store

#### Kegunaan

- Menyimpan riwayat order store per akun customer.
- Menyediakan invoice, referensi pembayaran, dan status order.

#### Cara Pakai

1. Buka menu Riwayat Order dan Invoice Store.
2. Gunakan filter status atau kata kunci.
3. Buka detail order untuk melihat invoice dan pengiriman.
4. Lanjutkan pembayaran jika order masih pending/confirmed.

### 4.12 Author Dashboard

#### Kegunaan

- Menjadi pusat monitoring untuk author.
- Menampilkan statistik buku, invoice, royalti, dan workflow author.

#### Cara Pakai

1. Login sebagai author.
2. Buka Dashboard Author.
3. Gunakan shortcut untuk order paket/cetak, claim buku, invoice, dan royalti.
4. Gunakan group sidebar "Storefront & Customer" untuk berpindah ke dashboard customer atau storefront.

#### Prasyarat

- Role akun harus author.

#### Hasil

- Author dapat memantau produksi, transaksi, dan akses pelanggan dalam satu alur.

#### Catatan

- Jika dashboard customer 403, pastikan role author tetap memiliki hak akses customer.

### 4.13 Upgrade Customer/Reader ke Author

#### Kegunaan

- Mengubah akun customer/reader menjadi author melalui approval admin.
- Menyimpan checklist data profile dan dokumen pendukung.

#### Cara Pakai

1. Buka Profile.
2. Lengkapi data yang diwajibkan.
3. Centang pengajuan upgrade author.
4. Unggah dokumen pendukung jika diperlukan.
5. Admin meninjau request pada halaman review.

#### Status Umum

- Pending: menunggu review.
- Approved: disetujui, role berubah ke author.
- Rejected: ditolak dengan catatan reviewer.

### 4.14 Review Upgrade Author (Admin)

#### Kegunaan

- Mengelola permintaan upgrade author.
- Menyaring request berdasarkan status, meninjau lampiran, dan mengekspor CSV.

#### Cara Pakai

1. Buka halaman Review Upgrade Author.
2. Filter berdasarkan status jika perlu.
3. Preview atau unduh lampiran request.
4. Approve atau reject request dengan catatan.
5. Gunakan export CSV untuk laporan/arsip.

#### Prasyarat

- User harus memiliki role admin/isbn/superadmin.
- Request upgrade harus berstatus pending untuk diproses.

#### Hasil

- Status request berubah menjadi approved atau rejected.
- User menerima notifikasi keputusan.

#### Catatan

- Jika lampiran tidak tersedia, cek storage public dan path file pada request.

### 4.15 Paket Penerbitan

#### Kegunaan

- Menyediakan paket layanan penerbitan untuk penulis.
- Menampilkan fitur paket seperti editing, layout, cover, print, dan ebook.

#### Cara Pakai

1. Buka section Paket Penerbitan di storefront.
2. Lihat paket yang tersedia dan manfaatnya.
3. Gunakan tombol konsultasi untuk menindaklanjuti kebutuhan penerbitan.
4. Admin dapat menambah/mengubah paket dari modul paket penerbitan.

#### Prasyarat

- Paket sudah dibuat di modul admin.

#### Hasil

- Pengunjung dapat memahami layanan yang tersedia sebelum menghubungi tim.

#### Catatan

- Paket yang tampil di storefront dipilih dari paket yang aktif/tersedia di sistem.

### 4.16 Finance, Invoice, dan Pembayaran Author

#### Kegunaan

- Mengelola invoice author dan pembayaran.
- Memproses checkout pembayaran via iPaymu.

#### Cara Pakai

1. Buka invoice author atau finance invoice.
2. Periksa status tagihan.
3. Upload bukti bayar jika diperlukan.
4. Gunakan callback/payment gateway untuk pembaruan status otomatis.

### 4.16.1 Otomasi Biaya Paket Berdasarkan Halaman Naskah

#### Kegunaan

- Menghitung biaya tambahan paket author secara otomatis dari naskah DOCX.
- Menjamin total order dan invoice konsisten dengan jumlah halaman aktual.

#### Rule yang Digunakan

- Basis evaluasi A4 (margin 2 cm):
    - Jika >125 halaman, halaman lebih dihitung biaya tambahan per halaman.
- Jika paket termasuk cetak, evaluasi tambahan A5:
    - Jika >100 halaman A5, halaman lebih dikenai biaya cetak tambahan per halaman.
- Komponen tambahan bisa mencakup layout, editing, dan print sesuai paket.

#### Hasil

- Nilai biaya tambahan tersimpan pada order dan book.
- Nilai tersebut ikut ke breakdown dan total invoice.

### 4.17 Royalty System

#### Kegunaan

- Mengelola hak royalti author.
- Menangani payout request, approval, dan pembayaran.

#### Cara Pakai

1. Buka halaman royalti.
2. Periksa saldo available payout.
3. Lengkapi data bank.
4. Ajukan payout jika saldo mencukupi.
5. Finance/owner memproses approve, pay, atau reject.

### 4.18 Legacy Catalog dan External Sales

#### Kegunaan

- Menyimpan katalog legacy.
- Mencatat penjualan eksternal agar royalti tetap terhitung.

#### Cara Pakai

1. Buka modul legacy catalog atau external sales.
2. Isi data buku, format, nilai penjualan, dan rate royalti.
3. Simpan untuk kebutuhan laporan dan royalti.

### 4.19 Branding dan Navigasi

#### Kegunaan

- Menjaga tampilan sistem konsisten.
- Memudahkan perpindahan antar area store, customer, author, dan admin.

#### Cara Pakai

- Gunakan menu sidebar sesuai role.
- Gunakan badge atau grup menu gabungan untuk context yang relevan.
- Di storefront, gunakan menu publik untuk katalog, paket, cara kerja, dan FAQ.

#### Hasil

- Pengguna berpindah antar area sistem tanpa kebingungan konteks role.

#### Catatan

- Author memiliki grup menu gabungan untuk storefront dan customer agar tidak terjebak di satu dashboard saja.

### 4.20 Tool Hitung Halaman Naskah Otomatis (Semua Role Login)

#### Kegunaan

- Menghitung jumlah halaman naskah dari file DOCX secara cepat dan konsisten.
- Dipakai untuk validasi operasional sebelum order, sebelum input data admin, atau cross-check tim.

#### Akses

- Menu: **Hitung Halaman Naskah**.
- Role: semua user yang sudah login (internal, author, customer, reader).

#### Cara Pakai

1. Buka menu Hitung Halaman Naskah.
2. Pilih ukuran kertas utama (A4/A5/B5/UNESCO).
3. (Opsional) pilih ukuran pembanding.
4. Upload file DOCX.
5. Klik hitung, lalu baca hasil halaman utama + hasil komparasi (jika diisi).

#### Catatan

- Margin default: 2 cm.
- Ukuran pembanding tidak bisa sama dengan ukuran utama.
- File tidak disimpan sebagai data buku dari tool ini (hanya kalkulasi cepat).

### 4.21 Admin Naskah: Halaman Mentah dan Auto-Hitungan DOCX

#### Kegunaan

- Menjaga kualitas data naskah pada create/edit/list/dashboard admin.

#### Perubahan Operasional

- Form create/edit buku sekarang mewajibkan input halaman mentah A4.
- Upload DOCX opsional di create/edit untuk menghitung A4/A5 otomatis.
- Daftar naskah menampilkan metrik halaman mentah + indikator ambang batas.
- Dashboard backoffice menampilkan insight agregat naskah (A4/A5/over-limit).

### 4.22 Auth Modern + Keamanan Input Password

#### Kegunaan

- Membuat proses login/registrasi/reset lebih jelas, modern, dan aman secara UX.

#### Yang Berubah

- Semua halaman auth menggunakan tampilan visual konsisten MS Publishing.
- Tersedia toggle tampilkan/sembunyikan password.
- Register/reset dilengkapi indikator kekuatan password dan checklist aturan realtime:
    - minimal 8 karakter
    - huruf besar
    - huruf kecil
    - angka
    - simbol

## 5. Cara Menggunakan Storefront Berdasarkan Role

### Guest

- Lihat katalog publik.
- Gunakan menu login/register jika ingin membeli atau melacak order.
- Baca section paket penerbitan untuk konsultasi layanan.

### Customer / Reader

- Login ke customer dashboard.
- Lihat riwayat order dan invoice store.
- Kembali ke storefront kapan saja dari menu atau tombol cepat.

### Author

- Login ke author dashboard.
- Gunakan grup sidebar "Storefront & Customer".
- Pindah ke storefront, dashboard customer, atau riwayat order tanpa keluar dari akun.

### Admin / Staff

- Masuk ke dashboard internal.
- Kelola operasional, author upgrade, store catalog, dan laporan.
- Jika login dari flow storefront, sistem tetap menampilkan notice bahwa Anda diarahkan ke panel internal.

## 6. Error Handling dan Tips Operasional

- Jika login dari storefront tapi diarahkan ke panel internal, periksa role akun.
- Jika ebook tidak bisa dibuka, cek status pembayaran, token, dan pembatasan sesi/perangkat.
- Jika order print melebihi stok, sistem akan menolak checkout.
- Jika customer tidak melihat menu storefront, pastikan role customer/reader/author sudah benar.

## 7. Ringkasan Cepat Per Fitur

- Produksi: kelola naskah, assignment, file, dan workflow.
- ISBN: submit dan approval naskah siap ISBN.
- Layout: cek readiness dan generate dokumen.
- Storefront: jual buku jadi dan promosikan paket penerbitan.
- Customer: lihat order, invoice, dan tracking.
- Author: pantau dashboard author dan akses customer/storefront.
- Finance: invoice, pembayaran, dan royalti.
- Admin: review, kontrol, dan audit seluruh alur.

## 8. Checklist Operasional Harian

### Admin / Finance

- Periksa notifikasi baru.
- Review request upgrade author.
- Cek order store yang pending atau menunggu tindakan.
- Pastikan laporan dan invoice bergerak sesuai status.

### Author

- Periksa dashboard author dan dashboard customer.
- Tinjau riwayat order store dan royalti.
- Gunakan storefront untuk melihat katalog dan paket penerbitan.

### Tim Produksi

- Cek assignment aktif.
- Unggah file kerja sesuai role.
- Pastikan revisi dan final file tercatat.

### Customer / Reader

- Cek dashboard customer.
- Lanjutkan pembayaran order yang belum lunas.
- Gunakan tracking untuk memantau status order.

## 9. Penutup

Manual book ini dirancang agar tiap role dapat langsung memahami fungsi fitur, cara pakai, dan output yang dihasilkan. Jika ada modul baru, dokumen ini sebaiknya diperbarui bersamaan dengan perubahan fitur.
