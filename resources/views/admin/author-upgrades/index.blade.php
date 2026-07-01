@extends('adminlte::page')

@section('title', 'Review Upgrade Author')

@section('content_header')
    <h1 class="m-0 text-dark"><i class="fas fa-user-check mr-2"></i>Review Upgrade Author</h1>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('warning'))
        <div class="alert alert-warning">{{ session('warning') }}</div>
    @endif

    <div class="card card-outline card-info mb-3">
        <div class="card-body d-flex justify-content-between align-items-end flex-wrap">
            <form method="GET" action="{{ route('admin.author-upgrades.index') }}" class="form-inline mb-2">
                <label class="mr-2">Status</label>
                <select name="status" class="form-control mr-2">
                    @foreach (['pending', 'approved', 'rejected'] as $statusOption)
                        <option value="{{ $statusOption }}" {{ $status === $statusOption ? 'selected' : '' }}>
                            {{ strtoupper($statusOption) }}
                        </option>
                    @endforeach
                </select>
                <button type="submit" class="btn btn-info">Filter</button>
            </form>

            <a href="{{ route('admin.author-upgrades.export', ['status' => $status]) }}"
                class="btn btn-sm btn-outline-success mb-2">
                <i class="fas fa-file-csv mr-1"></i> Export CSV
            </a>

            <div class="mb-2">
                <span class="badge badge-warning mr-1">Pending: {{ $stats['pending'] }}</span>
                <span class="badge badge-success mr-1">Approved: {{ $stats['approved'] }}</span>
                <span class="badge badge-danger">Rejected: {{ $stats['rejected'] }}</span>
            </div>
        </div>
    </div>

    <div class="card card-outline card-primary">
        <div class="card-body p-0 table-responsive">
            <table class="table table-striped mb-0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Checklist</th>
                        <th>Catatan Pengajuan</th>
                        <th>Lampiran</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $requestItem)
                        <tr>
                            <td>
                                <strong>{{ $requestItem->user->name ?? '-' }}</strong><br>
                                <small class="text-muted">{{ $requestItem->user->email ?? '-' }}</small>
                            </td>
                            <td>
                                @php $checklist = $requestItem->checklist ?? []; @endphp
                                @foreach ($checklist as $key => $passed)
                                    <span class="badge badge-{{ $passed ? 'success' : 'danger' }} mr-1 mb-1">
                                        {{ str_replace('_', ' ', strtoupper($key)) }}
                                    </span>
                                @endforeach
                            </td>
                            <td>{{ $requestItem->request_note ?? '-' }}</td>
                            <td>
                                @if ($requestItem->supporting_document_path)
                                    <a href="{{ route('admin.author-upgrades.attachment.preview', $requestItem) }}"
                                        class="btn btn-xs btn-outline-info mb-1" target="_blank" rel="noopener">
                                        Preview
                                    </a>
                                    <a href="{{ route('admin.author-upgrades.attachment', $requestItem) }}"
                                        class="btn btn-xs btn-outline-primary">
                                        Unduh
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span
                                    class="badge badge-{{ $requestItem->status === 'approved' ? 'success' : ($requestItem->status === 'rejected' ? 'danger' : 'warning') }}">
                                    {{ strtoupper($requestItem->status) }}
                                </span>
                            </td>
                            <td>
                                @if ($requestItem->status === 'pending')
                                    <form method="POST"
                                        action="{{ route('admin.author-upgrades.approve', $requestItem) }}" class="mb-1">
                                        @csrf
                                        <input type="text" name="review_notes" class="form-control form-control-sm mb-1"
                                            placeholder="Catatan opsional approval">
                                        <button type="submit" class="btn btn-xs btn-success btn-block">Approve</button>
                                    </form>
                                    <form method="POST"
                                        action="{{ route('admin.author-upgrades.reject', $requestItem) }}">
                                        @csrf
                                        <input type="text" name="review_notes" class="form-control form-control-sm mb-1"
                                            placeholder="Alasan penolakan" required>
                                        <button type="submit" class="btn btn-xs btn-danger btn-block">Reject</button>
                                    </form>
                                @else
                                    <small class="text-muted">Reviewed by:
                                        {{ optional($requestItem->reviewer)->name ?? '-' }}</small>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-3">Belum ada request upgrade.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($requests->hasPages())
            <div class="card-footer">{{ $requests->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
@endsection
