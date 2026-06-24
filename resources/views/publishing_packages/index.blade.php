@extends('adminlte::page')

@section('title', 'Paket Penerbitan')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0 text-dark">Paket Penerbitan</h1>
        <a href="{{ route('publishing-packages.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-1"></i> Tambah Paket
        </a>
    </div>
@stop

@section('content')
    <div class="card shadow-sm">
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <table class="table table-bordered table-hover">
                <thead>
                    <tr>
                        <th>Nama Paket</th>
                        <th>Fitur</th>
                        <th>Harga</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($packages as $package)
                        <tr>
                            <td>
                                <strong>{{ $package->name }}</strong>
                                @if ($package->description)
                                    <div class="small text-muted">{{ $package->description }}</div>
                                @endif
                            </td>
                            <td>
                                <span
                                    class="badge badge-{{ $package->includes_editing ? 'success' : 'secondary' }}">Editing</span>
                                <span
                                    class="badge badge-{{ $package->includes_layout ? 'info' : 'secondary' }}">Layout</span>
                                <span
                                    class="badge badge-{{ $package->includes_cover_design ? 'warning' : 'secondary' }}">Cover</span>
                            </td>
                            <td>Rp {{ number_format($package->price, 0, ',', '.') }}</td>
                            <td>
                                <a href="{{ route('publishing-packages.edit', $package) }}"
                                    class="btn btn-sm btn-outline-primary">Edit</a>
                                <form action="{{ route('publishing-packages.destroy', $package) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"
                                        onclick="return confirm('Hapus paket ini?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
