<?php

namespace App\Http\Controllers;

use App\Services\ManuscriptA4PageCounterService;
use Illuminate\Http\Request;

class ManuscriptPageCounterController extends Controller
{
    public function index(ManuscriptA4PageCounterService $pageCounter)
    {
        return view('tools.manuscript-page-counter.index', [
            'paperOptions' => $pageCounter->getPaperOptions(),
            'selectedPaper' => 'A4',
            'comparePaper' => null,
        ]);
    }

    public function calculate(Request $request, ManuscriptA4PageCounterService $pageCounter)
    {
        $paperOptions = $pageCounter->getPaperOptions();

        $validated = $request->validate([
            'manuscript_file' => ['required', 'file', 'mimes:docx', 'max:51200'],
            'paper_size' => ['required', 'string', 'in:' . implode(',', array_keys($paperOptions))],
            'compare_paper_size' => ['nullable', 'string', 'in:' . implode(',', array_keys($paperOptions))],
        ]);

        $selectedPaper = strtoupper((string) $validated['paper_size']);
        $comparePaper = !empty($validated['compare_paper_size'])
            ? strtoupper((string) $validated['compare_paper_size'])
            : null;

        $papersToCount = [$selectedPaper];

        if (!empty($comparePaper) && $comparePaper !== $selectedPaper) {
            $papersToCount[] = $comparePaper;
        }

        try {
            $countedPages = $pageCounter->countFromUploadedFileByPapers(
                $validated['manuscript_file'],
                $papersToCount,
                2
            );
        } catch (\Throwable $e) {
            return back()
                ->withInput()
                ->with('danger', 'Gagal menghitung halaman naskah: ' . $e->getMessage());
        }

        return view('tools.manuscript-page-counter.index', [
            'paperOptions' => $paperOptions,
            'selectedPaper' => $selectedPaper,
            'comparePaper' => $comparePaper,
            'result' => [
                'file_name' => $validated['manuscript_file']->getClientOriginalName(),
                'paper_label' => $paperOptions[$selectedPaper] ?? $selectedPaper,
                'pages' => (int) ($countedPages[$selectedPaper] ?? 1),
                'margin_cm' => 2,
                'compare' => (!empty($comparePaper) && $comparePaper !== $selectedPaper)
                    ? [
                        'paper' => $comparePaper,
                        'paper_label' => $paperOptions[$comparePaper] ?? $comparePaper,
                        'pages' => (int) ($countedPages[$comparePaper] ?? 1),
                        'diff' => (int) (($countedPages[$comparePaper] ?? 1) - ($countedPages[$selectedPaper] ?? 1)),
                    ]
                    : null,
            ],
        ]);
    }
}
