<?php

namespace App\Services;

use thiagoalessio\TesseractOCR\TesseractOCR;

class OcrService
{
    public function extract(
        string $file
    ) {
        return (new TesseractOCR($file))
            ->lang('ind')
            ->run();
    }
}