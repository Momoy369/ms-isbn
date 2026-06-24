@extends('adminlte::page')

@section('title', 'Master Harga Cetak')

@section('content_header')
    <h1 class="m-0">Master Harga Cetak</h1>
@endsection

@section('content')
    @foreach (['success', 'warning', 'danger'] as $type)
        @if (session($type))
            <div class="alert alert-{{ $type }} alert-dismissible rounded">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                {{ session($type) }}
            </div>
        @endif
    @endforeach

    <div class="card mb-3">
        <div class="card-header"><strong>Tambah Harga Cetak</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('print-prices.store') }}" class="row">
                @csrf
                <div class="col-md-3 form-group">
                    <label>Nama Rule</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-2 form-group">
                    <label>Tipe Kertas</label>
                    <input type="text" name="paper_type" class="form-control" placeholder="HVS 70" required>
                </div>
                <div class="col-md-2 form-group">
                    <label>Ukuran</label>
                    <input type="text" name="paper_size" class="form-control" placeholder="A5">
                </div>
                <div class="col-md-2 form-group">
                    <label>Jenis Cetak</label>
                    <select name="print_type" class="form-control" required>
                        <option value="blackwhite">Blackwhite</option>
                        <option value="color">Color</option>
                    </select>
                </div>
                <div class="col-md-1 form-group">
                    <label>Min</label>
                    <input type="number" name="min_pages" class="form-control" min="1" value="1" required>
                </div>
                <div class="col-md-1 form-group">
                    <label>Max</label>
                    <input type="number" name="max_pages" class="form-control" min="1">
                </div>
                <div class="col-md-2 form-group">
                    <label>Base (Rp)</label>
                    <input type="number" step="0.01" name="base_price" class="form-control" value="0" required>
                </div>
                <div class="col-md-2 form-group">
                    <label>/Halaman (Rp)</label>
                    <input type="number" step="0.01" name="price_per_page" class="form-control" value="0" required>
                </div>
                <div class="col-md-2 form-group">
                    <label>Berat/copy (gr)</label>
                    <input type="number" name="weight_per_copy_gram" class="form-control" min="50" value="250"
                        required>
                </div>
                <div class="col-md-6 form-group">
                    <label>Catatan</label>
                    <input type="text" name="notes" class="form-control">
                </div>
                <div class="col-md-2 form-group d-flex align-items-end">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="active-new" name="is_active" value="1"
                            checked>
                        <label class="custom-control-label" for="active-new">Aktif</label>
                    </div>
                </div>
                <div class="col-md-2 form-group d-flex align-items-end">
                    <button class="btn btn-primary btn-block" type="submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Daftar Harga Cetak</strong></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Rule</th>
                        <th>Kertas</th>
                        <th>Rentang Hal.</th>
                        <th>Harga</th>
                        <th>Berat</th>
                        <th>Status</th>
                        <th width="360">Update</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rules as $rule)
                        <tr>
                            <td>{{ $rule->name }}</td>
                            <td>{{ $rule->paper_type }} {{ $rule->paper_size ? '(' . $rule->paper_size . ')' : '' }}</td>
                            <td>{{ $rule->min_pages }} - {{ $rule->max_pages ?? '∞' }}</td>
                            <td>
                                Base: Rp {{ number_format($rule->base_price, 0, ',', '.') }}<br>
                                /Hal: Rp {{ number_format($rule->price_per_page, 0, ',', '.') }}
                            </td>
                            <td>{{ $rule->weight_per_copy_gram }} gr</td>
                            <td>
                                <span class="badge badge-{{ $rule->is_active ? 'success' : 'secondary' }}">
                                    {{ $rule->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('print-prices.update', $rule) }}" class="mb-1">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="name" value="{{ $rule->name }}">
                                    <input type="hidden" name="paper_type" value="{{ $rule->paper_type }}">
                                    <input type="hidden" name="paper_size" value="{{ $rule->paper_size }}">
                                    <input type="hidden" name="print_type" value="{{ $rule->print_type }}">
                                    <input type="hidden" name="min_pages" value="{{ $rule->min_pages }}">
                                    <input type="hidden" name="max_pages" value="{{ $rule->max_pages }}">
                                    <input type="hidden" name="base_price" value="{{ $rule->base_price }}">
                                    <input type="hidden" name="price_per_page" value="{{ $rule->price_per_page }}">
                                    <input type="hidden" name="weight_per_copy_gram"
                                        value="{{ $rule->weight_per_copy_gram }}">
                                    <input type="hidden" name="notes" value="{{ $rule->notes }}">
                                    <input type="hidden" name="is_active" value="{{ $rule->is_active ? 0 : 1 }}">
                                    <button type="submit" class="btn btn-xs btn-outline-warning">
                                        {{ $rule->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('print-prices.destroy', $rule) }}"
                                    onsubmit="return confirm('Hapus rule ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-xs btn-outline-danger">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">Belum ada harga cetak.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
