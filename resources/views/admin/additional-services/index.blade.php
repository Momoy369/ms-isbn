@extends('adminlte::page')

@section('title', 'Layanan Tambahan')

@section('content_header')
    <h1 class="m-0">Master Layanan Tambahan</h1>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible rounded">
            <button class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header"><strong>Tambah Layanan</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('additional-services.store') }}" class="row">
                @csrf
                <div class="col-md-3 form-group">
                    <label>Nama</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="col-md-2 form-group">
                    <label>Tipe</label>
                    <select name="service_type" class="form-control" required>
                        <option value="editing">Editing</option>
                        <option value="layout">Layout</option>
                        <option value="cover_design">Desain Cover</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div class="col-md-2 form-group">
                    <label>Harga</label>
                    <input type="number" step="0.01" name="price" class="form-control" min="0" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Deskripsi</label>
                    <input type="text" name="description" class="form-control">
                </div>
                <div class="col-md-1 form-group d-flex align-items-end">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" class="custom-control-input" id="service-active" name="is_active"
                            value="1" checked>
                        <label class="custom-control-label" for="service-active">Aktif</label>
                    </div>
                </div>
                <div class="col-md-1 form-group d-flex align-items-end">
                    <button class="btn btn-primary btn-block" type="submit">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Daftar Layanan</strong></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Nama</th>
                        <th>Tipe</th>
                        <th>Harga</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($services as $service)
                        <tr>
                            <td>{{ $service->name }}</td>
                            <td>{{ strtoupper($service->service_type) }}</td>
                            <td>Rp {{ number_format($service->price, 0, ',', '.') }}</td>
                            <td><span
                                    class="badge badge-{{ $service->is_active ? 'success' : 'secondary' }}">{{ $service->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-3">Belum ada layanan tambahan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
