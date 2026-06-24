<div class="card mb-3">
    <div class="card-header"><strong>Ringkasan Bisnis Naskah</strong></div>
    <div class="card-body">
        @php
            $packageInvoices = $book->authorInvoices->where('is_package_billing', true);
            $packagePaid = $packageInvoices->where('status', 'paid')->sum('amount');
            $packagePending = $packageInvoices->where('status', 'pending')->sum('amount');
            $reprintOrders = $book->orders->where('order_type', 'reprint');
            $reprintProcessing = $reprintOrders->where('status', 'processing')->count();
            $reprintCompleted = $reprintOrders->where('status', 'completed')->count();
        @endphp

        <div class="mb-2"><strong>Harga Jual Efektif:</strong> Rp
            {{ number_format($book->effectiveSellingPrice(), 0, ',', '.') }}</div>
        <div class="mb-2"><strong>Tagihan Paket Lunas:</strong> Rp {{ number_format($packagePaid, 0, ',', '.') }}</div>
        <div class="mb-2"><strong>Tagihan Paket Pending:</strong> Rp {{ number_format($packagePending, 0, ',', '.') }}
        </div>
        <div class="mb-2"><strong>Order Cetak Ulang:</strong> {{ $reprintOrders->count() }}
            <span class="small text-muted">(proses: {{ $reprintProcessing }}, selesai: {{ $reprintCompleted }})</span>
        </div>

        <div class="mt-3">
            <a href="{{ route('printing.workspace.index', ['book_id' => $book->id]) }}"
                class="btn btn-sm btn-outline-primary">
                <i class="fas fa-print mr-1"></i> Buka Workspace Percetakan
            </a>
        </div>
    </div>
</div>
