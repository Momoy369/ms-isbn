<?php

namespace App\Http\Controllers;

use App\Models\StorePackageConsultation;
use Illuminate\Http\Request;

class AdminStorePackageConsultationController extends Controller
{
    public function index(Request $request)
    {
        $status = trim((string) $request->query('status', ''));
        $keyword = trim((string) $request->query('q', ''));
        $followUp = trim((string) $request->query('follow_up', ''));

        $query = StorePackageConsultation::query()
            ->when($status !== '', fn($q) => $q->where('status', $status))
            ->when($keyword !== '', function ($q) use ($keyword) {
                $q->where(function ($sub) use ($keyword) {
                    $sub->where('customer_name', 'like', '%' . $keyword . '%')
                        ->orWhere('customer_phone', 'like', '%' . $keyword . '%')
                        ->orWhere('customer_email', 'like', '%' . $keyword . '%')
                        ->orWhere('manuscript_title', 'like', '%' . $keyword . '%')
                        ->orWhere('package_name', 'like', '%' . $keyword . '%');
                });
            })
            ->when($followUp === 'due_today', fn($q) => $q->whereDate('next_action_at', now()->toDateString()))
            ->when($followUp === 'overdue', fn($q) => $q->whereDate('next_action_at', '<', now()->toDateString()))
            ->when($followUp === 'unplanned', fn($q) => $q->whereNull('next_action_at'));

        $todayDate = now()->toDateString();

        $statsBase = StorePackageConsultation::query();

        $stats = [
            'total' => (clone $statsBase)->count(),
            'pending' => (clone $statsBase)->where('status', 'pending')->count(),
            'contacted' => (clone $statsBase)->where('status', 'contacted')->count(),
            'quoted' => (clone $statsBase)->where('status', 'quoted')->count(),
            'won' => (clone $statsBase)->where('status', 'won')->count(),
            'due_today' => (clone $statsBase)->whereDate('next_action_at', $todayDate)->count(),
            'overdue' => (clone $statsBase)->whereDate('next_action_at', '<', $todayDate)->count(),
            'unplanned' => (clone $statsBase)->whereNull('next_action_at')->count(),
        ];

        $consultations = $query
            ->orderByRaw("CASE WHEN next_action_at IS NULL THEN 1 ELSE 0 END")
            ->orderBy('next_action_at')
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $statusOptions = ['pending', 'contacted', 'quoted', 'won', 'lost'];

        $followUpOptions = [
            '' => 'Semua Follow-up',
            'due_today' => 'Due Today',
            'overdue' => 'Overdue',
            'unplanned' => 'Belum Dijadwalkan',
        ];

        return view('finance.store.package-consultations', compact(
            'consultations',
            'stats',
            'status',
            'keyword',
            'followUp',
            'statusOptions',
            'followUpOptions'
        ));
    }

    public function updateStatus(Request $request, StorePackageConsultation $consultation)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,contacted,quoted,won,lost'],
            'finance_notes' => ['nullable', 'string', 'max:3000'],
            'next_action_at' => ['nullable', 'date'],
        ]);

        $consultation->update([
            'status' => $data['status'],
            'finance_notes' => isset($data['finance_notes']) ? trim((string) $data['finance_notes']) : null,
            'next_action_at' => $data['next_action_at'] ?? null,
        ]);

        return back()->with('success', 'Status dan follow-up lead berhasil diperbarui.');
    }
}
