@extends('adminlte::page')

@section('title', 'Lead Paket Storefront')

@section('content_header')
    <h1 class="m-0">Lead Configurator Paket Penerbitan</h1>
@endsection

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible rounded shadow-sm">
            <button type="button" class="close" data-dismiss="alert">&times;</button>
            {{ session('success') }}
        </div>
    @endif

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="small-box bg-info mb-0">
                <div class="inner">
                    <h3>{{ number_format($stats['total']) }}</h3>
                    <p>Total Lead</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning mb-0">
                <div class="inner">
                    <h3>{{ number_format($stats['pending']) }}</h3>
                    <p>Pending</p>
                </div>
                <div class="icon"><i class="fas fa-hourglass-half"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-primary mb-0">
                <div class="inner">
                    <h3>{{ number_format($stats['contacted']) }}</h3>
                    <p>Contacted</p>
                </div>
                <div class="icon"><i class="fas fa-phone"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success mb-0">
                <div class="inner">
                    <h3>{{ number_format($stats['won']) }}</h3>
                    <p>Won</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <strong>Daftar Lead Configurator</strong>
            <form method="GET" class="form-inline" style="gap:.4rem;">
                <input type="text" name="q" value="{{ $keyword }}" class="form-control form-control-sm"
                    placeholder="Cari nama/phone/email/judul">
                <select name="status" class="form-control form-control-sm">
                    <option value="">Semua Status</option>
                    @foreach ($statusOptions as $option)
                        <option value="{{ $option }}" @selected($status === $option)>{{ strtoupper($option) }}</option>
                    @endforeach
                </select>
                <button class="btn btn-sm btn-outline-primary" type="submit">Filter</button>
            </form>
        </div>

        <div class="card-body p-0 table-responsive">
            <table class="table table-bordered table-hover mb-0 align-middle">
                <thead class="thead-light">
                    <tr>
                        <th>Lead</th>
                        <th>Paket & Layanan</th>
                        <th>Detail Naskah</th>
                        <th>Estimasi</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($consultations as $consultation)
                        <tr>
                            <td>
                                <strong>{{ $consultation->customer_name }}</strong>
                                <div class="small text-muted">{{ $consultation->customer_phone }}</div>
                                <div class="small text-muted">{{ $consultation->customer_email ?: '-' }}</div>
                                <div class="small text-muted">Masuk:
                                    {{ optional($consultation->created_at)->format('d M Y H:i') }}</div>
                            </td>
                            <td>
                                <div><strong>{{ $consultation->package_name }}</strong></div>
                                @php
                                    $selectedServices = collect($consultation->selected_services ?? []);
                                @endphp
                                @if ($selectedServices->isNotEmpty())
                                    <ul class="small mb-0 pl-3 mt-1">
                                        @foreach ($selectedServices as $service)
                                            <li>
                                                {{ is_array($service) ? $service['name'] ?? 'Layanan' : $service }}
                                                @if (is_array($service) && isset($service['price']))
                                                    (Rp {{ number_format((float) $service['price'], 0, ',', '.') }})
                                                @endif
                                            </li>
                                        @endforeach
                                    </ul>
                                @else
                                    <div class="small text-muted">Tanpa layanan opsional</div>
                                @endif
                            </td>
                            <td>
                                <div><strong>Judul:</strong> {{ $consultation->manuscript_title ?: '-' }}</div>
                                <div class="small"><strong>Genre:</strong> {{ $consultation->manuscript_genre ?: '-' }}
                                </div>
                                <div class="small"><strong>Halaman:</strong>
                                    {{ $consultation->estimated_page_count ?: '-' }}</div>
                                <div class="small"><strong>Target:</strong>
                                    {{ optional($consultation->target_publish_date)->format('d M Y') ?: '-' }}</div>
                                <div class="small"><strong>Budget:</strong> {{ $consultation->budget_range ?: '-' }}</div>
                                @if ($consultation->notes)
                                    <div class="small mt-1 text-muted"><strong>Catatan:</strong>
                                        {{ $consultation->notes }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="small text-muted">Base: Rp
                                    {{ number_format((float) $consultation->package_base_price, 0, ',', '.') }}</div>
                                <strong>Rp
                                    {{ number_format((float) $consultation->estimated_total, 0, ',', '.') }}</strong>
                            </td>
                            <td style="min-width:220px;">
                                <form method="POST"
                                    action="{{ route('finance.store.package-consultations.update-status', $consultation) }}"
                                    class="form-inline" style="gap:.35rem;">
                                    @csrf
                                    @method('PUT')
                                    <select name="status" class="form-control form-control-sm" required>
                                        @foreach ($statusOptions as $option)
                                            <option value="{{ $option }}" @selected($consultation->status === $option)>
                                                {{ strtoupper($option) }}</option>
                                        @endforeach
                                    </select>
                                    <button class="btn btn-sm btn-primary" type="submit">Update</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Belum ada lead configurator.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($consultations->hasPages())
            <div class="card-footer">{{ $consultations->links() }}</div>
        @endif
    </div>
@endsection
