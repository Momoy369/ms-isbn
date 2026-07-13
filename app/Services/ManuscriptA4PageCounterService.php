<?php

namespace App\Services;

use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpWord\IOFactory;

class ManuscriptA4PageCounterService
{
    /**
     * Paper presets used by manuscript page counter tools.
     *
     * @return array<string, string>
     */
    public function getPaperOptions(): array
    {
        return [
            'A4' => 'A4',
            'A5' => 'A5',
            'B5' => 'B5',
            'B6' => 'B6',
            'UNESCO' => 'UNESCO (15.5 x 23 cm)',
        ];
    }

    public function summarizeFromUploadedFile(UploadedFile $file): array
    {
        $ext = strtolower((string) $file->getClientOriginalExtension());

        if ($ext !== 'docx') {
            throw new \RuntimeException('Format naskah untuk perhitungan halaman saat ini hanya mendukung DOCX.');
        }

        $text = $this->extractTextFromDocx($file->getRealPath());

        return [
            'a4_pages' => $this->countPagesFromText($text, 'A4'),
            'a5_pages' => $this->countPagesFromText($text, 'A5'),
        ];
    }

    public function countFromUploadedFile(UploadedFile $file): int
    {
        $summary = $this->summarizeFromUploadedFile($file);

        return (int) ($summary['a4_pages'] ?? 1);
    }

    public function countFromUploadedFileByPaper(UploadedFile $file, string $paper, float $marginCm = 2): int
    {
        $ext = strtolower((string) $file->getClientOriginalExtension());

        if ($ext !== 'docx') {
            throw new \RuntimeException('Format naskah untuk perhitungan halaman saat ini hanya mendukung DOCX.');
        }

        $text = $this->extractTextFromDocx($file->getRealPath());

        return $this->countPagesFromText($text, $paper, $marginCm);
    }

    /**
     * @param array<int, string> $papers
     * @return array<string, int>
     */
    public function countFromUploadedFileByPapers(UploadedFile $file, array $papers, float $marginCm = 2): array
    {
        $ext = strtolower((string) $file->getClientOriginalExtension());

        if ($ext !== 'docx') {
            throw new \RuntimeException('Format naskah untuk perhitungan halaman saat ini hanya mendukung DOCX.');
        }

        $text = $this->extractTextFromDocx($file->getRealPath());

        $normalizedPapers = array_values(array_unique(array_map(
            static fn($paper) => strtoupper((string) $paper),
            $papers
        )));

        $results = [];

        foreach ($normalizedPapers as $paper) {
            $results[$paper] = $this->countPagesFromText($text, $paper, $marginCm);
        }

        return $results;
    }

    private function extractTextFromDocx(string $filePath): string
    {
        $phpWord = IOFactory::load($filePath);
        $chunks = [];

        foreach ($phpWord->getSections() as $section) {
            foreach ($section->getElements() as $element) {
                if ($element instanceof \PhpOffice\PhpWord\Element\Text) {
                    $chunks[] = (string) $element->getText();
                    continue;
                }

                if ($element instanceof \PhpOffice\PhpWord\Element\TextRun) {
                    $line = '';
                    foreach ($element->getElements() as $child) {
                        if (method_exists($child, 'getText')) {
                            $line .= (string) $child->getText();
                        }
                    }

                    if (trim($line) !== '') {
                        $chunks[] = $line;
                    }

                    continue;
                }

                if ($element instanceof \PhpOffice\PhpWord\Element\TextBreak) {
                    $chunks[] = '';
                }
            }
        }

        return implode("\n", $chunks);
    }

    private function countPagesFromText(string $text, string $paper, float $marginCm = 2): int
    {
        $plain = trim(str_replace(["\r\n", "\r"], "\n", $text));

        if ($plain === '') {
            return 1;
        }

        $paperMeta = $this->resolvePaperMeta($paper);
        $htmlText = nl2br(e($plain), false);
        $marginCss = rtrim(rtrim(number_format($marginCm, 2, '.', ''), '0'), '.');

        $html = <<<HTML
<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <style>
        @page { size: {$paperMeta['css_size']}; margin: {$marginCss}cm; }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12pt;
            line-height: 1.45;
            color: #111827;
            word-wrap: break-word;
        }
    </style>
</head>
<body>{$htmlText}</body>
</html>
HTML;

        $options = new Options();
        $options->set('isRemoteEnabled', false);
        $options->set('isHtml5ParserEnabled', true);

        $dompdf = new Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper($paperMeta['dompdf_paper'], 'portrait');
        $dompdf->render();

        $pages = (int) $dompdf->getCanvas()->get_page_count();

        return max(1, $pages);
    }

    /**
     * @return array{css_size:string, dompdf_paper:string|array<int, float|int>}
     */
    private function resolvePaperMeta(string $paper): array
    {
        $normalized = strtoupper(trim($paper));

        if ($normalized === 'A4') {
            return [
                'css_size' => 'A4',
                'dompdf_paper' => 'A4',
            ];
        }

        if ($normalized === 'A5') {
            return [
                'css_size' => 'A5',
                'dompdf_paper' => 'A5',
            ];
        }

        if ($normalized === 'B5') {
            return [
                'css_size' => '176mm 250mm',
                'dompdf_paper' => [0, 0, $this->mmToPt(176), $this->mmToPt(250)],
            ];
        }

        if ($normalized === 'B6') {
            return [
                'css_size' => '125mm 176mm',
                'dompdf_paper' => [0, 0, $this->mmToPt(125), $this->mmToPt(176)],
            ];
        }

        if ($normalized === 'UNESCO') {
            return [
                'css_size' => '155mm 230mm',
                'dompdf_paper' => [0, 0, $this->mmToPt(155), $this->mmToPt(230)],
            ];
        }

        throw new \InvalidArgumentException('Ukuran kertas tidak didukung untuk perhitungan halaman.');
    }

    private function mmToPt(float $mm): float
    {
        return ($mm * 72.0) / 25.4;
    }
}
