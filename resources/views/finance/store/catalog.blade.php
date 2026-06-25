@extends('adminlte::page')

@section('title', 'Store Catalog')

@section('content_header')
    <h1 class="m-0">Storefront Catalog</h1>
@endsection

@section('content')
    @foreach (['success', 'warning', 'danger', 'info'] as $type)
        @if (session($type))
            <div class="alert alert-{{ $type }} alert-dismissible rounded shadow-sm">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session($type) }}
            </div>
        @endif
    @endforeach

    <div class="card mb-3">
        <div class="card-header"><strong>Tambah Item Store</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('finance.store.catalog.store') }}" class="row">
                @csrf
                <div class="col-md-3 form-group">
                    <label>Buku Naskah (opsional)</label>
                    <select name="book_id" class="form-control">
                        <option value="">- pilih buku -</option>
                        @foreach ($books as $book)
                            <option value="{{ $book->id }}">{{ $book->judul }} ({{ $book->penulis_1 }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Buku Legacy (opsional)</label>
                    <select name="legacy_book_id" class="form-control">
                        <option value="">- pilih legacy -</option>
                        @foreach ($legacyBooks as $book)
                            <option value="{{ $book->id }}">{{ $book->title }} ({{ $book->author_name }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Judul</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Subjudul</label>
                    <input type="text" name="subtitle" class="form-control">
                </div>
                <div class="col-md-3 form-group">
                    <label>Penulis</label>
                    <input type="text" name="author_name" class="form-control">
                </div>
                <div class="col-md-3 form-group">
                    <label>Harga List</label>
                    <input type="number" step="0.01" min="0" name="list_price" class="form-control" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Harga Promo</label>
                    <input type="number" step="0.01" min="0" name="promo_price" class="form-control">
                </div>
                <div class="col-md-3 form-group">
                    <label>Stok (opsional)</label>
                    <input type="number" min="0" name="stock" class="form-control">
                </div>
                <div class="col-md-8 form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" rows="2" class="form-control"></textarea>
                </div>
                <div class="col-md-4 form-group">
                    <label>Cover URL/Path (opsional)</label>
                    <input type="text" name="cover_image_path" class="form-control">
                </div>
                <div class="col-md-3 form-group">
                    <label>Sort Order</label>
                    <input type="number" min="0" name="sort_order" class="form-control" value="0">
                </div>
                <div class="col-md-5 form-group">
                    <label>Catatan Admin</label>
                    <input type="text" name="admin_notes" class="form-control">
                </div>
                <div class="col-md-2 form-group d-flex align-items-end">
                    <div class="w-100">
                        <input type="hidden" name="is_active" value="0">
                        <div class="custom-control custom-switch mb-1">
                            <input type="checkbox" class="custom-control-input" id="is-active" name="is_active"
                                value="1" checked>
                            <label class="custom-control-label" for="is-active">Aktif</label>
                        </div>
                    </div>
                </div>
                <div class="col-md-2 form-group d-flex align-items-end">
                    <div class="w-100">
                        <input type="hidden" name="is_featured" value="0">
                        <div class="custom-control custom-switch mb-1">
                            <input type="checkbox" class="custom-control-input" id="is-featured" name="is_featured"
                                value="1">
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
                                @if ($item->promo_price)
                                    <div class="small text-success">Promo: Rp
                                        {{ number_format($item->promo_price, 0, ',', '.') }}</div>
                                @endif
                            </td>
                            <td>{{ $item->stock === null ? 'Tidak dibatasi' : number_format($item->stock) }}</td>
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
                                    <div class="col-md-3 mb-1">
                                        <input type="number" name="stock" min="0"
                                            class="form-control form-control-sm" value="{{ $item->stock }}"
                                            placeholder="stok">
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
                                    <input type="hidden" name="subtitle" value="{{ $item->subtitle }}">
                                    <input type="hidden" name="author_name" value="{{ $item->author_name }}">
                                    <input type="hidden" name="description" value="{{ $item->description }}">
                                    <input type="hidden" name="cover_image_path" value="{{ $item->cover_image_path }}">
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
