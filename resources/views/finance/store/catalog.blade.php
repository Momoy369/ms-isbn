@extends('adminlte::page')

@section('title', 'Store Catalog')

@section('content_header')
    <h1 class="m-0">Storefront Catalog</h1>
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger rounded shadow-sm">
            <div class="font-weight-bold mb-1">Gagal menyimpan item store. Periksa input berikut:</div>
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @foreach (['success', 'warning', 'danger', 'info'] as $type)
        @if (session($type))
            <div class="alert alert-{{ $type }} alert-dismissible rounded shadow-sm">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session($type) }}
            </div>
        @endif
    @endforeach

    <div class="card mb-3">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Tambah Item Store</strong>
            <a href="{{ route('finance.store.vouchers.index') }}" class="btn btn-sm btn-outline-primary">Kelola Voucher</a>
        </div>
        <div class="card-body">
            <form method="POST" action="{{ route('finance.store.catalog.store') }}" class="row"
                enctype="multipart/form-data">
                @csrf
                <div class="col-md-3 form-group">
                    <label>Buku Naskah (opsional)</label>
                    <select name="book_id" class="form-control">
                        <option value="">- pilih buku -</option>
                        @foreach ($books as $book)
                            <option value="{{ $book->id }}" @selected((string) old('book_id') === (string) $book->id)>{{ $book->judul }}
                                ({{ $book->penulis_1 }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Buku Legacy (opsional)</label>
                    <select name="legacy_book_id" class="form-control">
                        <option value="">- pilih legacy -</option>
                        @foreach ($legacyBooks as $book)
                            <option value="{{ $book->id }}" @selected((string) old('legacy_book_id') === (string) $book->id)>{{ $book->title }}
                                ({{ $book->author_name }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Judul</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Subjudul</label>
                    <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle') }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Penulis</label>
                    <input type="text" name="author_name" class="form-control" value="{{ old('author_name') }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Tipe Produk</label>
                    <select name="product_type" class="form-control" required id="add-product-type">
                        <option value="print" @selected(old('product_type', 'print') === 'print')>Print</option>
                        <option value="ebook" @selected(old('product_type') === 'ebook')>Ebook</option>
                        <option value="print_ebook" @selected(old('product_type') === 'print_ebook')>Print + Ebook</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Harga Print</label>
                    <input type="number" step="0.01" min="0" name="list_price" class="form-control"
                        value="{{ old('list_price') }}" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Harga Promo Print</label>
                    <input type="number" step="0.01" min="0" name="promo_price" class="form-control"
                        value="{{ old('promo_price') }}">
                </div>
                <div class="col-md-3 form-group" id="add-ebook-price-wrap"
                    style="{{ old('product_type') === 'print_ebook' ? '' : 'display:none' }}">
                    <label>Harga Ebook <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="ebook_price" class="form-control"
                        value="{{ old('ebook_price') }}">
                    <small class="text-muted">Wajib jika tipe Print + Ebook.</small>
                </div>
                <div class="col-md-3 form-group" id="add-ebook-promo-wrap"
                    style="{{ old('product_type') === 'print_ebook' ? '' : 'display:none' }}">
                    <label>Harga Promo Ebook</label>
                    <input type="number" step="0.01" min="0" name="ebook_promo_price" class="form-control"
                        value="{{ old('ebook_promo_price') }}">
                </div>
                <div class="col-md-3 form-group" id="add-stock-wrap">
                    <label>Stok (opsional)</label>
                    <input type="number" min="0" name="stock" class="form-control" value="{{ old('stock') }}"
                        id="add-stock-input">
                    <small class="text-muted" id="add-stock-note">Kosongkan jika stok tidak dibatasi.</small>
                </div>
                <div class="col-md-8 form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" rows="2" class="form-control">{{ old('description') }}</textarea>
                </div>
                <div class="col-md-4 form-group">
                    <label>Upload Cover (opsional)</label>
                    <input type="file" name="cover_image_file" class="form-control"
                        accept="image/png,image/jpeg,image/webp">
                </div>
                <div class="col-md-4 form-group">
                    <label>Upload Naskah PDF Ebook</label>
                    <input type="file" name="ebook_pdf" class="form-control" accept="application/pdf">
                    <small class="text-muted">Wajib diisi jika Tipe Produk = Ebook atau Print + Ebook.</small>
                </div>
                <div class="col-md-3 form-group">
                    <label>Sort Order</label>
                    <input type="number" min="0" name="sort_order" class="form-control"
                        value="{{ old('sort_order', 0) }}">
                </div>
                <div class="col-md-5 form-group">
                    <label>Catatan Admin</label>
                    <input type="text" name="admin_notes" class="form-control" value="{{ old('admin_notes') }}">
                </div>
                <div class="col-md-2 form-group d-flex align-items-end">
                    <div class="w-100">
                        <input type="hidden" name="is_active" value="0">
                        <div class="custom-control custom-switch mb-1">
                            <input type="checkbox" class="custom-control-input" id="is-active" name="is_active"
                                value="1" @checked(old('is_active', 1))>
                            <label class="custom-control-label" for="is-active">Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 form-group d-flex align-items-end">
                    <div class="w-100">
                        <input type="hidden" name="is_featured" value="0">
                        <div class="custom-control custom-switch mb-1">
                            <input type="checkbox" class="custom-control-input" id="is-featured" name="is_featured"
                                value="1" @checked(old('is_featured'))>
                            <label class="custom-control-label" for="is-featured">Featured</label>
                        </div>
                        <button class="btn btn-primary btn-block" type="submit">Tambah</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Daftar Item Store</strong></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Judul</th>
                        <th>Sumber</th>
                        <th>Harga</th>
                        <th>Stok</th>
                        <th>Status</th>
                        <th style="min-width:280px;">Update Cepat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($items as $item)
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $item->title }}</div>
                                <small class="text-muted">/{{ $item->slug }}</small>
                            </td>
                            <td>{{ $item->book->judul ?? ($item->legacyBook->title ?? 'Manual') }}</td>
                            <td>
                                Rp {{ number_format($item->list_price, 0, ',', '.') }}
                                <div class="small text-muted">{{ $item->productTypeLabel() }}</div>
                                @if ($item->promo_price)
                                    <div class="small text-success">Promo Print: Rp
                                        {{ number_format($item->promo_price, 0, ',', '.') }}</div>
                                @endif
                                @if ($item->product_type === 'print_ebook')
                                    <div class="small text-info mt-1">Ebook: Rp
                                        {{ number_format($item->ebook_price ?: $item->list_price, 0, ',', '.') }}
                                    </div>
                                    @if ($item->ebook_promo_price)
                                        <div class="small text-success">Promo Ebook: Rp
                                            {{ number_format($item->ebook_promo_price, 0, ',', '.') }}</div>
                                    @endif
                                @endif
                            </td>
                            <td>
                                @if ($item->product_type === 'ebook')
                                    <span class="text-info">Tidak relevan (Ebook)</span>
                                @else
                                    {{ $item->stock === null ? 'Tidak dibatasi' : number_format($item->stock) }}
                                @endif
                            </td>
                            <td>
                                <span
                                    class="badge badge-{{ $item->is_active ? 'success' : 'secondary' }}">{{ $item->is_active ? 'ACTIVE' : 'OFF' }}</span>
                                @if ($item->is_featured)
                                    <span class="badge badge-warning">FEATURED</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('finance.store.catalog.update', $item) }}"
                                    class="row">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-md-8 mb-1">
                                        <input type="text" name="title" class="form-control form-control-sm"
                                            value="{{ $item->title }}" required>
                                    </div>
                                    <div class="col-md-4 mb-1">
                                        <input type="number" name="list_price" step="0.01" min="0"
                                            class="form-control form-control-sm" value="{{ $item->list_price }}"
                                            required>
                                    </div>
                                    <div class="col-md-4 mb-1">
                                        <input type="number" name="promo_price" step="0.01" min="0"
                                            class="form-control form-control-sm" value="{{ $item->promo_price }}"
                                            placeholder="promo">
                                    </div>
                                    <div class="col-md-4 mb-1">
                                        <select name="product_type" class="form-control form-control-sm" required>
                                            <option value="print" @selected(($item->product_type ?? 'print') === 'print')>PRINT</option>
                                            <option value="ebook" @selected(($item->product_type ?? 'print') === 'ebook')>EBOOK</option>
                                            <option value="print_ebook" @selected(($item->product_type ?? 'print') === 'print_ebook')>PRINT + EBOOK</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-1">
                                        <input type="number" name="stock" min="0"
                                            class="form-control form-control-sm" value="{{ $item->stock }}"
                                            {{ $item->product_type === 'ebook' ? 'disabled' : '' }} placeholder="stok">
                                    </div>
                                    <div class="col-md-3 mb-1">
                                        <input type="number" name="sort_order" min="0"
                                            class="form-control form-control-sm" value="{{ $item->sort_order }}">
                                    </div>
                                    <div class="col-md-2 mb-1 d-flex align-items-center">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1"
                                            @checked($item->is_active)> aktif
                                    </div>
                                    <div class="col-md-2 mb-1 d-flex align-items-center">
                                        <input type="hidden" name="is_featured" value="0">
                                        <input type="checkbox" name="is_featured" value="1"
                                            @checked($item->is_featured)> fitur
                                    </div>
                                    @if ($item->product_type === 'ebook')
                                        <input type="hidden" name="stock" value="">
                                    @endif
                                    <input type="hidden" name="subtitle" value="{{ $item->subtitle }}">
                                    <input type="hidden" name="author_name" value="{{ $item->author_name }}">
                                    <input type="hidden" name="description" value="{{ $item->description }}">
                                    <input type="hidden" name="cover_image_path" value="{{ $item->cover_image_path }}">
                                    <input type="hidden" name="ebook_read_link" value="{{ $item->ebook_read_link }}">
                                    <input type="hidden" name="ebook_price" value="{{ $item->ebook_price }}">
                                    <input type="hidden" name="ebook_promo_price"
                                        value="{{ $item->ebook_promo_price }}">
                                    <input type="hidden" name="admin_notes" value="{{ $item->admin_notes }}">
                                    <div class="col-md-12">
                                        <button class="btn btn-xs btn-primary" type="submit">Simpan</button>
                                        <a class="btn btn-xs btn-outline-secondary"
                                            href="{{ route('store.show', $item->slug) }}" target="_blank">Lihat</a>
                                    </div>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Belum ada item store.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($items->hasPages())
            <div class="card-footer">{{ $items->links() }}</div>
        @endif
    </div>
@endsection

@push('js')
    <script>
        (function() {
            const typeSelect = document.getElementById('add-product-type');
            const priceWrap = document.getElementById('add-ebook-price-wrap');
            const promoWrap = document.getElementById('add-ebook-promo-wrap');
            const stockWrap = document.getElementById('add-stock-wrap');
            const stockInput = document.getElementById('add-stock-input');
            const stockNote = document.getElementById('add-stock-note');

            function toggleEbookFields() {
                const productType = typeSelect ? typeSelect.value : 'print';
                const show = productType === 'print_ebook';
                if (priceWrap) priceWrap.style.display = show ? '' : 'none';
                if (promoWrap) promoWrap.style.display = show ? '' : 'none';

                if (stockWrap && stockInput) {
                    if (productType === 'ebook') {
                        stockInput.value = '';
                        stockInput.setAttribute('disabled', 'disabled');
                        if (stockNote) {
                            stockNote.textContent = 'Ebook tidak membutuhkan stok (penjualan tidak terbatas).';
                        }
                    } else {
                        stockInput.removeAttribute('disabled');
                        if (stockNote) {
                            stockNote.textContent = 'Kosongkan jika stok tidak dibatasi.';
                        }
                    }
                }
            }

            if (typeSelect) {
                typeSelect.addEventListener('change', toggleEbookFields);
                toggleEbookFields();
            }
        })();
    </script>
@endpush
