@extends('adminlte::page')

@section('title', 'Detail Invoice ' . $invoice->invoice_number)

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h1 class="m-0">Detail Invoice</h1>
        <a href="{{ route('author.invoices.index') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
    </div>
@endsection

@section('content')
    @foreach (['success', 'warning', 'info', 'danger'] as $type)
        @if (session($type))
            <div class="alert alert-{{ $type }} alert-dismissible rounded">
                <button class="close" data-dismiss="alert">&times;</button>
                {{ session($type) }}
            </div>
        @endif
    @endforeach

    <div class="row">
        {{-- Kartu invoice --}}
        <div class="col-lg-8">
            <div class="card shadow-sm" style="border-radius:14px;">
                <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="mb-0 font-weight-bold">{{ $invoice->invoice_number }}</h4>
                        <div class="small text-muted">Diterbitkan: {{ $invoice->created_at->format('d F Y') }}</div>
                    </div>
                    <span class="badge badge-{{ $invoice->getStatusBadgeColor() }} px-3 py-2" style="font-size:.9rem;">
                        {{ $invoice->getStatusLabel() }}
                    </span>
                </div>

                <div class="card-body">
                    {{-- Detail buku --}}
                    <div class="p-3 rounded bg-light border mb-4">
                        <div class="small text-muted mb-1">Naskah</div>
                        <div class="font-weight-bold">{{ $invoice->book->judul ?? '-' }}</div>
                        <div class="small text-muted">No. Naskah: {{ $invoice->book->nomor_naskah ?? '-' }}</div>
                        @if ($invoice->book->isbn)
                            <div class="small text-muted">ISBN: {{ $invoice->book->isbn }}</div>
                        @endif
                    </div>

                    {{-- Rincian --}}
                    <table class="table table-bordered mb-4">
                        <thead class="bg-light">
                            <tr>
                                <th>Uraian</th>
                                <th class="text-right">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <div class="font-weight-bold">{{ $invoice->description }}</div>
                                    <div class="small text-muted">
                                        Jenis: {{ $invoice->getTypeLabel() }}
                                        @if ($invoice->type === 'revision')
                                            &nbsp;&bull;&nbsp; Revisi ke-{{ $invoice->revision_count }}
                                            (Tahap: {{ ucfirst($invoice->revision_stage ?? '-') }})
                                        @endif
                                    </div>
                                    @if ($invoice->notes)
                                        <div class="small text-info mt-1">{{ $invoice->notes }}</div>
                                    @endif
                                </td>
                                <td class="text-right font-weight-bold align-middle">
                                    Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr class="bg-light">
                                <td class="text-right font-weight-bold">Total</td>
                                <td class="text-right font-weight-bold text-primary" style="font-size:1.1rem;">
                                    Rp {{ number_format($invoice->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        </tfoot>
                    </table>

                    {{-- Jatuh tempo --}}
                    @if ($invoice->due_date && $invoice->isPending())
                        <div class="alert alert-{{ $invoice->due_date->isPast() ? 'danger' : 'warning' }} py-2">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            Jatuh tempo: <strong>{{ $invoice->due_date->format('d F Y') }}</strong>
                            @if ($invoice->due_date->isPast())
                                &nbsp;<span class="badge badge-danger">Terlambat</span>
                            @else
                                ({{ $invoice->due_date->diffForHumans() }})
                            @endif
                        </div>
                    @endif

                    {{-- Info paket (untuk invoice tipe package) --}}
                    @if ($invoice->type === 'package' && $invoice->book->publishingPackage)
                        @php $pkg = $invoice->book->publishingPackage; @endphp
                        <div class="p-3 rounded border mb-3">
                            <div class="font-weight-bold mb-2">Detail Paket: {{ $pkg->name }}</div>
                            @foreach (['includes_editing' => 'Editing', 'includes_layout' => 'Layout', 'includes_cover_design' => 'Desain Sampul', 'includes_author_certificate' => 'Sertifikat Penulis', 'includes_google_scholar' => 'Google Scholar', 'requires_hki_registration' => 'Pendaftaran HKI'] as $field => $label)
                                <span class="badge badge-{{ $pkg->$field ? 'primary' : 'light border' }} mr-1 mb-1">
                                    <i class="fas fa-{{ $pkg->$field ? 'check' : 'times' }} mr-1"></i>{{ $label }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    {{-- Paid info --}}
                    @if ($invoice->isPaid())
                        <div class="alert alert-success py-2">
                            <i class="fas fa-check-circle mr-1"></i>
                            Dibayar pada: <strong>{{ optional($invoice->paid_at)->format('d F Y H:i') ?? '-' }}</strong>
                        </div>
                    @endif

                    @php
                        $canAccessDeliveryLinks = $invoice->book && $invoice->book->canAuthorAccessDeliveryLinks();
                        $hasAnyDeliveryLink =
                            !empty($invoice->book->final_drive_link) || !empty($invoice->book->final_ebook_link);
                    @endphp
                    @if ($hasAnyDeliveryLink)
                        <div class="p-3 rounded border bg-light">
                            <div class="font-weight-bold mb-2"><i class="fas fa-link mr-1"></i> Link Hasil Produksi</div>
                            @if ($canAccessDeliveryLinks)
                                <div class="small text-success mb-2">Akses terbuka karena invoice paket telah lunas.</div>
                                @if (!empty($invoice->book->final_drive_link))
                                    <a href="{{ $invoice->book->final_drive_link }}" target="_blank"
                                        class="btn btn-sm btn-success mr-1 mb-1">
                                        <i class="fas fa-external-link-alt mr-1"></i> Buka Link Drive
                                    </a>
                                @endif
                                @if (!empty($invoice->book->final_ebook_link))
                                    <a href="{{ $invoice->book->final_ebook_link }}" target="_blank"
                                        class="btn btn-sm btn-success mb-1">
                                        <i class="fas fa-external-link-alt mr-1"></i> Buka Link Ebook
                                    </a>
                                @endif
                            @else
                                <div class="small text-warning">Link masih terkunci sampai pelunasan terverifikasi atau
                                    dibuka manual oleh finance.</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Panel aksi --}}
        <div class="col-lg-4">
            @if ($invoice->isPending())
                <div class="card shadow-sm" style="border-radius:14px;">
                    <div class="card-header bg-white border-0">
                        <h5 class="mb-0"><i class="fas fa-upload mr-1 text-primary"></i> Upload Bukti Pembayaran</h5>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info py-2 small mb-3">
                            Upload bukti transfer/pembayaran. Admin akan mengkonfirmasi dalam 1&times;24 jam.
                        </div>
                        <form method="POST" enctype="multipart/form-data"
                            action="{{ route('author.invoices.upload-proof', $invoice) }}">
                            @csrf
                            <div class="form-group">
                                <label class="small font-weight-bold">
                                    File Bukti Pembayaran <span class="text-danger">*</span>
                                </label>
                                <input type="file" name="payment_proof"
                                    class="form-control-file @error('payment_proof') is-invalid @enderror"
                                    accept=".jpg,.jpeg,.png,.pdf" required>
                                <div class="small text-muted mt-1">Format: JPG, PNG, PDF. Maks 4 MB.</div>
                                @error('payment_proof')
                                    <div class="text-danger small">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="form-group">
                                <label class="small font-weight-bold">Catatan (opsional)</label>
                                <textarea name="notes" class="form-control form-control-sm" rows="2"
                                    placeholder="Nomor ref transfer, bank, dll."></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary btn-block">
                                <i class="fas fa-paper-plane mr-1"></i> Kirim Bukti Pembayaran
                            </button>
                        </form>

                        <form method="POST" action="{{ route('author.invoices.pay-now', $invoice) }}" class="mt-2"
                            onsubmit="return confirm('Konfirmasi pembayaran invoice ini sekarang?')">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block">
                                <i class="fas fa-credit-card mr-1"></i> Bayar Sekarang (Online)
                            </button>
                        </form>

                        @if ($invoice->payment_proof)
                            <hr>
                            <div class="small text-muted">Bukti terakhir dikirim:</div>
                            <a href="{{ asset('storage/' . $invoice->payment_proof) }}" target="_blank"
                                class="btn btn-outline-secondary btn-sm btn-block mt-1">
                                <i class="fas fa-file mr-1"></i> Lihat Bukti Pembayaran
                            </a>
                            <div class="small text-warning mt-2">
                                <i class="fas fa-clock mr-1"></i> Menunggu konfirmasi admin.
                            </div>
                        @endif
                    </div>
                </div>
            @elseif ($invoice->isCancelled())
                <div class="card shadow-sm" style="border-radius:14px;">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-ban fa-2x text-muted mb-2 d-block"></i>
                        <p class="text-muted mb-0">Invoice ini telah dibatalkan.</p>
                    </div>
                </div>
            @else
                <div class="card shadow-sm" style="border-radius:14px;">
                    <div class="card-body text-center py-4">
                        <i class="fas fa-check-circle fa-2x text-success mb-2 d-block"></i>
                        <p class="font-weight-bold text-success mb-1">Invoice Lunas</p>
                        <p class="text-muted small mb-0">
                            Dibayar: {{ optional($invoice->paid_at)->format('d F Y') ?? '-' }}
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
