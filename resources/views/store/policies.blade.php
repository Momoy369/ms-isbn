<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Storefront - MS ISBN</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link
        href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&family=Fraunces:opsz,wght@9..144,600;9..144,700&display=swap"
        rel="stylesheet">
    <style>
        :root {
            --bg: #f3efe4;
            --ink: #1f1a16;
            --brand: #005f73;
            --accent: #ca6702;
            --line: #d8cfbf;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: 'Manrope', sans-serif;
            color: var(--ink);
            background: radial-gradient(circle at 8% 0%, #e9d8a6 0, transparent 30%), var(--bg);
        }

        .container {
            width: min(1020px, 92vw);
            margin: 0 auto;
            padding: 2rem 0 3rem;
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: .8rem;
            flex-wrap: wrap;
            margin-bottom: .9rem;
        }

        .top img {
            max-height: 52px;
            width: auto;
            object-fit: contain;
        }

        .top a {
            text-decoration: none;
            color: var(--brand);
            font-weight: 800;
        }

        h1,
        h2 {
            font-family: 'Fraunces', serif;
        }

        h1 {
            margin: .4rem 0 .55rem;
            font-size: clamp(1.8rem, 4vw, 2.6rem);
        }

        .lead {
            margin: 0 0 1.2rem;
            color: #5f564a;
            max-width: 760px;
            line-height: 1.5;
        }

        .menu {
            display: flex;
            flex-wrap: wrap;
            gap: .5rem;
            margin-bottom: 1.1rem;
        }

        .menu a {
            text-decoration: none;
            border: 1px solid var(--line);
            border-radius: 999px;
            padding: .3rem .7rem;
            background: #fff8ea;
            color: #4f4538;
            font-weight: 700;
            font-size: .8rem;
        }

        .card {
            background: #fffdf8;
            border: 1px solid var(--line);
            border-radius: 14px;
            padding: 1rem;
            margin-bottom: .85rem;
        }

        .card h2 {
            margin: 0 0 .45rem;
            font-size: 1.3rem;
        }

        .card p {
            margin: 0;
            color: #5f564a;
            line-height: 1.5;
        }

        .list {
            margin: .55rem 0 0 1rem;
            color: #5f564a;
            line-height: 1.55;
        }

        .faq {
            display: grid;
            gap: .55rem;
        }

        .faq details {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 10px;
            padding: .7rem .8rem;
        }

        .faq summary {
            cursor: pointer;
            font-weight: 800;
        }

        .faq p {
            margin: .45rem 0 0;
            color: #5f564a;
        }

        .cta {
            display: flex;
            flex-wrap: wrap;
            gap: .55rem;
            margin-top: 1rem;
        }

        .btn {
            text-decoration: none;
            border-radius: 10px;
            padding: .68rem .95rem;
            font-weight: 800;
            display: inline-block;
        }

        .btn-primary {
            background: var(--brand);
            color: #fff;
        }

        .btn-soft {
            background: #e8ddd0;
            color: #4f4538;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="top">
            <img src="{{ asset('logowide.png') }}" alt="MS ISBN">
            <a href="{{ route('store.index') }}">&larr; Kembali ke Storefront</a>
        </div>

        <h1>Kebijakan Storefront</h1>
        <p class="lead">Dokumen ini menjadi rujukan layanan untuk pembeli buku/ebook dan calon author yang memesan
            paket penerbitan. Tujuannya agar proses transaksi, produksi, dan komunikasi berjalan transparan.</p>

        <div class="menu">
            <a href="#refund">Refund</a>
            <a href="#shipping">Pengiriman</a>
            <a href="#ebook">Akses Ebook</a>
            <a href="#copyright">Hak Cipta</a>
            <a href="#faq-customer">FAQ Customer</a>
            <a href="#faq-author">FAQ Author</a>
        </div>

        <section id="refund" class="card">
            <h2>Refund dan Komplain</h2>
            <p>Refund diajukan dari halaman detail order customer. Tim finance melakukan review berdasarkan status
                order,
                riwayat pembayaran, dan catatan kendala yang disampaikan customer.</p>
            <ul class="list">
                <li>Status refund: requested, approved, rejected.</li>
                <li>Untuk pembelian print, persetujuan refund akan memicu sinkronisasi stok sesuai aturan sistem.</li>
                <li>Catatan keputusan finance disimpan pada data order sebagai audit trail.</li>
            </ul>
        </section>

        <section id="shipping" class="card">
            <h2>Pengiriman Buku Cetak</h2>
            <p>Ongkir dihitung saat checkout berdasarkan data provinsi/kota dan kurir. Nomor resi ditampilkan setelah
                order diproses tim operasional.</p>
            <ul class="list">
                <li>Alamat, kota, dan provinsi harus valid saat checkout.</li>
                <li>Tracking order bisa dipantau dari menu lacak pesanan atau dashboard customer.</li>
                <li>Keterlambatan pengiriman ditangani melalui notifikasi status order.</li>
            </ul>
        </section>

        <section id="ebook" class="card">
            <h2>Akses Ebook dan Keamanan Reader</h2>
            <p>Ebook hanya dapat diakses oleh pembeli dengan order valid melalui library internal serta password reader
                yang dibuat saat checkout.</p>
            <ul class="list">
                <li>Reader menggunakan token akses terbatas dan proteksi sesi/perangkat.</li>
                <li>Password reader menjadi tanggung jawab akun pembeli.</li>
                <li>Watermark visual diterapkan untuk menjaga distribusi konten digital.</li>
            </ul>
        </section>

        <section id="copyright" class="card">
            <h2>Hak Cipta dan Layanan Author</h2>
            <p>Author tetap menjadi pemilik karya sesuai perjanjian yang berlaku. Paket penerbitan mendefinisikan
                lingkup
                layanan seperti editing, layout, cover, ISBN, dan dukungan publikasi.</p>
            <ul class="list">
                <li>Pilih paket sesuai kebutuhan naskah dan target rilis.</li>
                <li>Perubahan scope layanan mengikuti ketentuan paket/kontrak aktif.</li>
                <li>Komunikasi revisi dan approval dilakukan bertahap dalam workflow produksi.</li>
            </ul>
        </section>

        <section id="faq-customer" class="card">
            <h2>FAQ Customer</h2>
            <div class="faq">
                <details open>
                    <summary>Bagaimana cara melacak status order?</summary>
                    <p>Gunakan menu Lacak Pesanan di storefront atau buka dashboard customer pada bagian riwayat order.
                    </p>
                </details>
                <details>
                    <summary>Apakah ebook memiliki stok terbatas?</summary>
                    <p>Tidak. Produk ebook tidak menggunakan stok fisik, namun akses tetap mengikuti validasi order dan
                        keamanan reader.</p>
                </details>
                <details>
                    <summary>Kapan saya bisa mengajukan refund?</summary>
                    <p>Refund dapat diajukan dari detail order sesuai kondisi transaksi. Status akhirnya ditetapkan oleh
                        tim finance setelah review.</p>
                </details>
            </div>
        </section>

        <section id="faq-author" class="card">
            <h2>FAQ Author</h2>
            <div class="faq">
                <details open>
                    <summary>Apakah harus punya akun author sejak awal?</summary>
                    <p>Tidak wajib. Anda dapat memulai dari akun customer lalu mengajukan upgrade author saat proses
                        penerbitan berjalan.</p>
                </details>
                <details>
                    <summary>Bagaimana memilih paket penerbitan yang tepat?</summary>
                    <p>Mulai dari paket yang paling sesuai kebutuhan naskah, lalu konsultasikan kebutuhan tambahan
                        seperti
                        editing lanjutan, desain, atau strategi rilis.</p>
                </details>
                <details>
                    <summary>Apakah saya bisa menerbitkan print sekaligus ebook?</summary>
                    <p>Bisa. Paket dengan dukungan print dan ebook dapat dipilih untuk strategi distribusi yang lebih
                        lengkap.</p>
                </details>
            </div>
        </section>

        <div class="cta">
            <a class="btn btn-primary" href="{{ route('store.package-configurator') }}">Mulai Configurator Paket</a>
            <a class="btn btn-soft" href="{{ route('store.index') }}">Kembali ke Katalog Buku</a>
        </div>
    </div>
</body>

</html>
