@extends('adminlte::page')

@section('title', 'Katalog Buku Legacy')

@section('content_header')
    <h1 class="m-0">Katalog Buku Legacy</h1>
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
        <div class="card-header"><strong>Tambah Buku Legacy</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('legacy-books.store') }}" class="row">
                @csrf
                <div class="col-md-4 form-group">
                    <label>Judul</label>
                    <input type="text" name="title" class="form-control" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Subjudul</label>
                    <input type="text" name="subtitle" class="form-control">
                </div>
                <div class="col-md-3 form-group">
                    <label>Nama Penulis</label>
                    <input type="text" name="author_name" class="form-control" required>
                </div>
                <div class="col-md-2 form-group">
                    <label>Author Sistem</label>
                    <select name="author_user_id" class="form-control">
                        <option value="">- Opsional -</option>
                        @foreach ($authors as $author)
                            <option value="{{ $author->id }}">{{ $author->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 form-group">
                    <label>ISBN</label>
                    <input type="text" name="isbn" class="form-control">
                </div>
                <div class="col-md-2 form-group">
                    <label>Tahun</label>
                    <input type="number" name="published_year" class="form-control" min="1900" max="2100">
                </div>
                <div class="col-md-2 form-group">
                    <label>Harga</label>
                    <input type="number" name="list_price" step="0.01" min="0" class="form-control">
                </div>
                <div class="col-md-2 form-group">
                    <label>Rate Royalti</label>
                    <input type="number" name="royalty_rate" step="0.0001" min="0" max="1"
                        class="form-control" placeholder="0.2000">
                </div>
                <div class="col-md-2 form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="archived">Archived</option>
                    </select>
                </div>
                <div class="col-md-6 form-group">
                    <label>Catatan</label>
                    <input type="text" name="notes" class="form-control">
                </div>
                <div class="col-md-2 form-group d-flex align-items-end">
                    <div class="w-100">
                        <input type="hidden" name="royalty_enabled" value="0">
                        <div class="custom-control custom-switch mb-2">
                            <input type="checkbox" class="custom-control-input" id="legacy-royalty-enabled"
                                name="royalty_enabled" value="1">
                            <label class="custom-control-label" for="legacy-royalty-enabled">Aktif Royalti</label>
                        </div>
                        <button class="btn btn-primary btn-block" type="submit">Simpan</button>
                    </div>
                </div>
                <div class="col-md-2 form-group d-flex align-items-end">
                    <div class="w-100">
                        <input type="hidden" name="distribution_online" value="0">
                        <input type="hidden" name="distribution_ebook" value="0">
                        <input type="hidden" name="distribution_marketplace" value="0">
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="legacy-online"
                                name="distribution_online" value="1">
                            <label class="custom-control-label" for="legacy-online">Online</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="legacy-ebook" name="distribution_ebook"
                                value="1">
                            <label class="custom-control-label" for="legacy-ebook">Ebook</label>
                        </div>
                        <div class="custom-control custom-checkbox">
                            <input type="checkbox" class="custom-control-input" id="legacy-marketplace"
                                name="distribution_marketplace" value="1">
                            <label class="custom-control-label" for="legacy-marketplace">Marketplace</label>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Daftar Katalog Buku Legacy</strong></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Judul</th>
                        <th>Penulis</th>
                        <th>ISBN</th>
                        <th>Harga</th>
                        <th>Royalti</th>
                        <th>Distribusi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($books as $book)
                        <tr>
                            <td>
                                <div class="font-weight-bold">{{ $book->title }}</div>
                                <small class="text-muted">{{ $book->subtitle ?: '-' }}</small>
                            </td>
                            <td>
                                {{ $book->author_name }}
                                @if ($book->author)
                                    <div class="small text-muted">Akun: {{ $book->author->name }}</div>
                                @endif
                            </td>
                            <td>{{ $book->isbn ?: '-' }}</td>
                            <td>Rp {{ number_format($book->list_price, 0, ',', '.') }}</td>
                            <td>
                                @if ($book->royalty_enabled)
                                    <span
                                        class="badge badge-success">{{ number_format($book->royaltyRate() * 100, 2) }}%</span>
                                @else
                                    <span class="badge badge-secondary">Off</span>
                                @endif
                            </td>
                            <td>
                                <small>
                                    {{ $book->distribution_online ? 'Online' : '-' }} |
                                    {{ $book->distribution_ebook ? 'Ebook' : '-' }} |
                                    {{ $book->distribution_marketplace ? 'Marketplace' : '-' }}
                                </small>
                            </td>
                            <td><span class="badge badge-light border">{{ strtoupper($book->status) }}</span></td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Belum ada katalog legacy.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($books->hasPages())
            <div class="card-footer">{{ $books->links() }}</div>
        @endif
    </div>
@endsection
