@extends('adminlte::page')

@section('title', 'Store Vouchers')

@section('content_header')
    <h1 class="m-0">Voucher Storefront</h1>
@endsection

@section('content')
    @if ($errors->any())
        <div class="alert alert-danger rounded shadow-sm">
            <div class="font-weight-bold mb-1">Gagal menyimpan voucher. Periksa input berikut:</div>
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
        <div class="card-header"><strong>Tambah Voucher</strong></div>
        <div class="card-body">
            <form method="POST" action="{{ route('finance.store.vouchers.store') }}" class="row">
                @csrf
                <div class="col-md-3 form-group">
                    <label>Kode</label>
                    <input type="text" name="code" class="form-control" value="{{ old('code') }}" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Nama Voucher</label>
                    <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Tipe Diskon</label>
                    <select name="discount_type" class="form-control" required>
                        <option value="percent" @selected(old('discount_type', 'percent') === 'percent')>Persen (%)</option>
                        <option value="fixed" @selected(old('discount_type') === 'fixed')>Nominal</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Nilai Diskon</label>
                    <input type="number" step="0.01" min="0" name="discount_value" class="form-control"
                        value="{{ old('discount_value') }}" required>
                </div>
                <div class="col-md-3 form-group">
                    <label>Minimum Subtotal</label>
                    <input type="number" step="0.01" min="0" name="minimum_subtotal" class="form-control"
                        value="{{ old('minimum_subtotal') }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Maks Diskon</label>
                    <input type="number" step="0.01" min="0" name="max_discount_amount" class="form-control"
                        value="{{ old('max_discount_amount') }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Berlaku Untuk</label>
                    <select name="applies_to" class="form-control" required>
                        <option value="all" @selected(old('applies_to', 'all') === 'all')>Semua</option>
                        <option value="print" @selected(old('applies_to') === 'print')>Print</option>
                        <option value="ebook" @selected(old('applies_to') === 'ebook')>Ebook</option>
                        <option value="print_ebook" @selected(old('applies_to') === 'print_ebook')>Print + Ebook</option>
                    </select>
                </div>
                <div class="col-md-3 form-group">
                    <label>Limit Penggunaan</label>
                    <input type="number" min="1" name="usage_limit" class="form-control"
                        value="{{ old('usage_limit') }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Tanggal Mulai</label>
                    <input type="datetime-local" name="start_at" class="form-control" value="{{ old('start_at') }}">
                </div>
                <div class="col-md-3 form-group">
                    <label>Tanggal Berakhir</label>
                    <input type="datetime-local" name="end_at" class="form-control" value="{{ old('end_at') }}">
                </div>
                <div class="col-md-9 form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" rows="2" class="form-control">{{ old('description') }}</textarea>
                </div>
                <div class="col-md-3 form-group d-flex align-items-end">
                    <div class="w-100">
                        <input type="hidden" name="is_active" value="0">
                        <div class="custom-control custom-switch mb-1">
                            <input type="checkbox" class="custom-control-input" id="voucher-active" name="is_active"
                                value="1" @checked(old('is_active', 1))>
                            <label class="custom-control-label" for="voucher-active">Aktif</label>
                        </div>
                        <button class="btn btn-primary btn-block" type="submit">Tambah Voucher</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><strong>Daftar Voucher</strong></div>
        <div class="card-body table-responsive p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="bg-light">
                    <tr>
                        <th>Kode</th>
                        <th>Detail</th>
                        <th>Aturan</th>
                        <th>Status</th>
                        <th style="min-width:320px;">Update Cepat</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vouchers as $voucher)
                        <tr>
                            <td>
                                <strong>{{ $voucher->code }}</strong>
                                <div class="small text-muted">{{ $voucher->name }}</div>
                            </td>
                            <td>
                                <div class="small text-muted">{{ $voucher->description ?: '-' }}</div>
                                <div class="small">Dipakai: {{ number_format($voucher->used_count) }}
                                    @if ($voucher->usage_limit)
                                        / {{ number_format($voucher->usage_limit) }}
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="small">Tipe: {{ strtoupper($voucher->discount_type) }}</div>
                                <div class="small">Nilai:
                                    {{ $voucher->discount_type === 'percent' ? number_format($voucher->discount_value, 0, ',', '.') . '%' : 'Rp ' . number_format($voucher->discount_value, 0, ',', '.') }}
                                </div>
                                <div class="small">Berlaku: {{ strtoupper($voucher->applies_to) }}</div>
                                <div class="small">Min: Rp
                                    {{ number_format((float) ($voucher->minimum_subtotal ?? 0), 0, ',', '.') }}</div>
                            </td>
                            <td>
                                <span
                                    class="badge badge-{{ $voucher->is_active ? 'success' : 'secondary' }}">{{ $voucher->is_active ? 'ACTIVE' : 'OFF' }}</span>
                                @if (!$voucher->isCurrentlyActive())
                                    <span class="badge badge-warning">OUT OF RULE</span>
                                @endif
                            </td>
                            <td>
                                <form method="POST" action="{{ route('finance.store.vouchers.update', $voucher) }}"
                                    class="row">
                                    @csrf
                                    @method('PUT')
                                    <div class="col-md-4 mb-1">
                                        <input type="text" name="code" class="form-control form-control-sm"
                                            value="{{ $voucher->code }}" required>
                                    </div>
                                    <div class="col-md-4 mb-1">
                                        <input type="text" name="name" class="form-control form-control-sm"
                                            value="{{ $voucher->name }}" required>
                                    </div>
                                    <div class="col-md-4 mb-1">
                                        <select name="discount_type" class="form-control form-control-sm" required>
                                            <option value="percent" @selected($voucher->discount_type === 'percent')>PERCENT</option>
                                            <option value="fixed" @selected($voucher->discount_type === 'fixed')>FIXED</option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-1">
                                        <input type="number" step="0.01" min="0" name="discount_value"
                                            class="form-control form-control-sm" value="{{ $voucher->discount_value }}"
                                            required>
                                    </div>
                                    <div class="col-md-3 mb-1">
                                        <input type="number" step="0.01" min="0" name="minimum_subtotal"
                                            class="form-control form-control-sm" value="{{ $voucher->minimum_subtotal }}"
                                            placeholder="min subtotal">
                                    </div>
                                    <div class="col-md-3 mb-1">
                                        <input type="number" step="0.01" min="0" name="max_discount_amount"
                                            class="form-control form-control-sm"
                                            value="{{ $voucher->max_discount_amount }}" placeholder="maks diskon">
                                    </div>
                                    <div class="col-md-3 mb-1">
                                        <select name="applies_to" class="form-control form-control-sm" required>
                                            <option value="all" @selected($voucher->applies_to === 'all')>ALL</option>
                                            <option value="print" @selected($voucher->applies_to === 'print')>PRINT</option>
                                            <option value="ebook" @selected($voucher->applies_to === 'ebook')>EBOOK</option>
                                            <option value="print_ebook" @selected($voucher->applies_to === 'print_ebook')>PRINT + EBOOK
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 mb-1">
                                        <input type="number" min="1" name="usage_limit"
                                            class="form-control form-control-sm" value="{{ $voucher->usage_limit }}"
                                            placeholder="limit">
                                    </div>
                                    <div class="col-md-3 mb-1">
                                        <input type="datetime-local" name="start_at" class="form-control form-control-sm"
                                            value="{{ optional($voucher->start_at)->format('Y-m-d\\TH:i') }}">
                                    </div>
                                    <div class="col-md-3 mb-1">
                                        <input type="datetime-local" name="end_at" class="form-control form-control-sm"
                                            value="{{ optional($voucher->end_at)->format('Y-m-d\\TH:i') }}">
                                    </div>
                                    <div class="col-md-6 mb-1">
                                        <input type="text" name="description" class="form-control form-control-sm"
                                            value="{{ $voucher->description }}" placeholder="deskripsi">
                                    </div>
                                    <div class="col-md-2 mb-1 d-flex align-items-center">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1"
                                            @checked($voucher->is_active)> aktif
                                    </div>
                                    <div class="col-md-12">
                                        <button class="btn btn-xs btn-primary" type="submit">Simpan</button>
                                    </div>
                                </form>
                                <form method="POST" action="{{ route('finance.store.vouchers.destroy', $voucher) }}"
                                    class="mt-1" onsubmit="return confirm('Hapus voucher ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-xs btn-outline-danger" type="submit">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-3">Belum ada voucher store.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($vouchers->hasPages())
            <div class="card-footer">{{ $vouchers->links() }}</div>
        @endif
    </div>
@endsection
