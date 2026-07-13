<?php

namespace App\Services;

use App\Models\PublishingPackage;

class PublishingOverageService
{
    public function __construct(private readonly SystemSettingService $settings)
    {
    }

    public function getA4Limit(): int
    {
        return max(1, (int) $this->settings->get('publishing.a4_layout_limit', 125));
    }

    public function getLayoutOveragePerPage(): float
    {
        return max(0, (float) $this->settings->get('publishing.a4_layout_overage_per_page', 2000));
    }

    public function getEditingOveragePerPage(): float
    {
        return max(0, (float) $this->settings->get('publishing.a4_editing_overage_per_page', 2000));
    }

    /**
     * @return array<int, array{paper:string,max_pages:int,overage_per_page:float}>
     */
    public function getPrintPaperRules(): array
    {
        $json = (string) $this->settings->get('publishing.print_overage_rules_json', '');
        $decoded = json_decode($json, true);

        if (!is_array($decoded) || empty($decoded)) {
            return $this->defaultPrintPaperRules();
        }

        $rules = [];
        foreach ($decoded as $row) {
            if (!is_array($row)) {
                continue;
            }

            $paper = strtoupper(trim((string) ($row['paper'] ?? '')));
            if ($paper === '') {
                continue;
            }

            $rules[] = [
                'paper' => $paper,
                'max_pages' => max(1, (int) ($row['max_pages'] ?? 100)),
                'overage_per_page' => max(0, (float) ($row['overage_per_page'] ?? 500)),
            ];
        }

        return empty($rules) ? $this->defaultPrintPaperRules() : $rules;
    }

    /**
     * @return array<int, string>
     */
    public function getTrackedPapers(): array
    {
        $papers = ['A4', 'A5'];

        foreach ($this->getPrintPaperRules() as $rule) {
            $paper = strtoupper((string) ($rule['paper'] ?? ''));
            if ($paper !== '') {
                $papers[] = $paper;
            }
        }

        return array_values(array_unique($papers));
    }

    /**
     * @param array<string, int|float|string|null> $paperPageCounts
     * @return array<string, int|float|string>
     */
    public function calculateForPackage(PublishingPackage $package, array $paperPageCounts, ?string $selectedPrintPaper = null): array
    {
        $selectedPaper = strtoupper(trim((string) ($selectedPrintPaper ?: 'A5')));
        $paperMap = [];

        foreach ($paperPageCounts as $paper => $count) {
            $paperMap[strtoupper((string) $paper)] = max(1, (int) $count);
        }

        $a4Pages = (int) ($paperMap['A4'] ?? 1);
        $selectedPages = (int) ($paperMap[$selectedPaper] ?? ($paperMap['A5'] ?? $a4Pages));

        $a4Limit = $this->getA4Limit();
        $a4OverPages = max(0, $a4Pages - $a4Limit);

        $layoutFee = $a4OverPages * $this->getLayoutOveragePerPage();
        $editingFee = $package->includes_editing ? ($a4OverPages * $this->getEditingOveragePerPage()) : 0;

        $paperRule = $this->resolvePrintPaperRule($selectedPaper);
        $printLimit = (int) ($paperRule['max_pages'] ?? 100);
        $printRate = (float) ($paperRule['overage_per_page'] ?? 500);
        $printOverPages = $package->supports_print ? max(0, $selectedPages - $printLimit) : 0;
        $printFee = $printOverPages * $printRate;

        return [
            'selected_print_paper' => $selectedPaper,
            'selected_print_pages' => $selectedPages,
            'a4_pages' => $a4Pages,
            'a4_limit' => $a4Limit,
            'a4_over_pages' => $a4OverPages,
            'layout_fee' => $layoutFee,
            'editing_fee' => $editingFee,
            'print_limit' => $printLimit,
            'print_overage_rate' => $printRate,
            'print_over_pages' => $printOverPages,
            'print_fee' => $printFee,
            'extra_fee' => $layoutFee + $editingFee + $printFee,
        ];
    }

    /**
     * @return array{paper:string,max_pages:int,overage_per_page:float}
     */
    private function resolvePrintPaperRule(string $paper): array
    {
        $paper = strtoupper($paper);
        foreach ($this->getPrintPaperRules() as $rule) {
            if (strtoupper((string) $rule['paper']) === $paper) {
                return $rule;
            }
        }

        foreach ($this->getPrintPaperRules() as $rule) {
            if (strtoupper((string) $rule['paper']) === 'A5') {
                return $rule;
            }
        }

        return [
            'paper' => 'A5',
            'max_pages' => 100,
            'overage_per_page' => 500,
        ];
    }

    /**
     * @return array<int, array{paper:string,max_pages:int,overage_per_page:float}>
     */
    private function defaultPrintPaperRules(): array
    {
        return [
            ['paper' => 'A5', 'max_pages' => 100, 'overage_per_page' => 500],
            ['paper' => 'B5', 'max_pages' => 120, 'overage_per_page' => 600],
            ['paper' => 'B6', 'max_pages' => 140, 'overage_per_page' => 700],
            ['paper' => 'UNESCO', 'max_pages' => 110, 'overage_per_page' => 550],
            ['paper' => 'A4', 'max_pages' => 90, 'overage_per_page' => 800],
        ];
    }
}
