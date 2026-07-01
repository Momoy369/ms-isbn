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
            });

        $consultations = $query
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        $statsBase = StorePackageConsultation::query();

        $stats = [
            'total' => (clone $statsBase)->count(),
            'pending' => (clone $statsBase)->where('status', 'pending')->count(),
            'contacted' => (clone $statsBase)->where('status', 'contacted')->count(),
            'quoted' => (clone $statsBase)->where('status', 'quoted')->count(),
            'won' => (clone $statsBase)->where('status', 'won')->count(),
        ];

        $statusOptions = ['pending', 'contacted', 'quoted', 'won', 'lost'];

        return view('finance.store.package-consultations', compact(
            'consultations',
            'stats',
            'status',
            'keyword',
            'statusOptions'
        ));
    }

    public function updateStatus(Request $request, StorePackageConsultation $consultation)
    {
        $data = $request->validate([
            'status' => ['required', 'in:pending,contacted,quoted,won,lost'],
        ]);

        $consultation->update([
            'status' => $data['status'],
        ]);

        return back()->with('success', 'Status lead konsultasi berhasil diperbarui.');
    }
}
