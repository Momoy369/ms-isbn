<?php

namespace App\Http\Controllers;

use App\Models\AuthorUpgradeRequest;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminAuthorUpgradeRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = trim((string) $request->query('status', 'pending'));

        $requests = AuthorUpgradeRequest::query()
            ->with(['user', 'reviewer'])
            ->when($status !== '', fn($query) => $query->where('status', $status))
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $stats = [
            'pending' => AuthorUpgradeRequest::where('status', 'pending')->count(),
            'approved' => AuthorUpgradeRequest::where('status', 'approved')->count(),
            'rejected' => AuthorUpgradeRequest::where('status', 'rejected')->count(),
        ];

        return view('admin.author-upgrades.index', compact('requests', 'stats', 'status'));
    }

    public function approve(Request $request, AuthorUpgradeRequest $upgradeRequest, NotificationService $notifications)
    {
        if ($upgradeRequest->status !== 'pending') {
            return back()->with('warning', 'Request ini sudah diproses sebelumnya.');
        }

        $upgradeRequest->update([
            'status' => 'approved',
            'reviewed_at' => now(),
            'reviewed_by_user_id' => auth()->id(),
            'review_notes' => trim((string) $request->input('review_notes', '')) ?: null,
        ]);

        $upgradeRequest->user()->update([
            'role' => 'author',
            'is_profile_complete' => true,
        ]);

        $notifications->send(
            (int) $upgradeRequest->user_id,
            'Pengajuan Upgrade Author Disetujui',
            'Selamat, pengajuan upgrade akun Anda ke Author telah disetujui. Silakan login ulang untuk melihat menu author.',
            null
        );

        return back()->with('success', 'Request upgrade author disetujui.');
    }

    public function reject(Request $request, AuthorUpgradeRequest $upgradeRequest, NotificationService $notifications)
    {
        if ($upgradeRequest->status !== 'pending') {
            return back()->with('warning', 'Request ini sudah diproses sebelumnya.');
        }

        $data = $request->validate([
            'review_notes' => ['required', 'string', 'max:2000'],
        ]);

        $upgradeRequest->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'reviewed_by_user_id' => auth()->id(),
            'review_notes' => $data['review_notes'],
        ]);

        $notifications->send(
            (int) $upgradeRequest->user_id,
            'Pengajuan Upgrade Author Ditolak',
            'Pengajuan upgrade akun Anda ditolak. Catatan: ' . $data['review_notes'] . '. Silakan perbaiki data dan ajukan ulang.',
            null
        );

        return back()->with('success', 'Request upgrade author ditolak dengan catatan.');
    }

    public function exportCsv(Request $request): StreamedResponse
    {
        $status = trim((string) $request->query('status', ''));

        $rows = AuthorUpgradeRequest::query()
            ->with(['user', 'reviewer'])
            ->when($status !== '', fn($query) => $query->where('status', $status))
            ->latest('id')
            ->get();

        $filename = 'author-upgrade-requests-' . now()->format('Ymd_His') . '.csv';

        return response()->streamDownload(function () use ($rows) {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'Request ID',
                'User Name',
                'User Email',
                'Status',
                'Submitted At',
                'Reviewed At',
                'Reviewer',
                'Request Note',
                'Review Notes',
                'Supporting Document',
            ]);

            foreach ($rows as $row) {
                fputcsv($handle, [
                    $row->id,
                    optional($row->user)->name,
                    optional($row->user)->email,
                    $row->status,
                    optional($row->submitted_at)?->format('Y-m-d H:i:s'),
                    optional($row->reviewed_at)?->format('Y-m-d H:i:s'),
                    optional($row->reviewer)->name,
                    $row->request_note,
                    $row->review_notes,
                    $row->supporting_document_path,
                ]);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function downloadAttachment(AuthorUpgradeRequest $upgradeRequest)
    {
        if (!$upgradeRequest->supporting_document_path) {
            return back()->with('warning', 'Lampiran dokumen tidak tersedia.');
        }

        if (!Storage::disk('public')->exists($upgradeRequest->supporting_document_path)) {
            return back()->with('warning', 'File lampiran tidak ditemukan di storage.');
        }

        $absolutePath = Storage::disk('public')->path($upgradeRequest->supporting_document_path);

        return response()->download($absolutePath);
    }

    public function previewAttachment(AuthorUpgradeRequest $upgradeRequest)
    {
        if (!$upgradeRequest->supporting_document_path) {
            return back()->with('warning', 'Lampiran dokumen tidak tersedia.');
        }

        if (!Storage::disk('public')->exists($upgradeRequest->supporting_document_path)) {
            return back()->with('warning', 'File lampiran tidak ditemukan di storage.');
        }

        $absolutePath = Storage::disk('public')->path($upgradeRequest->supporting_document_path);
        $mimeType = File::mimeType($absolutePath) ?: 'application/octet-stream';

        return response()->file($absolutePath, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="' . basename($absolutePath) . '"',
        ]);
    }
}
