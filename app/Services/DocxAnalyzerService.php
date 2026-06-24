<?php

namespace App\Services;

use PhpOffice\PhpWord\IOFactory;

class DocxAnalyzerService
{
    public function extractText(string $filePath)
    {
        $phpWord = IOFactory::load($filePath);

        $text = '';

        foreach ($phpWord->getSections() as $section) {

            $elements = $section->getElements();

            foreach ($elements as $element) {

                if (
                    method_exists(
                        $element,
                        'getText'
                    )
                ) {

                    $text .=
                        $element->getText();

                    $text .= "\n";
                }
            }
        }

        return $text;
    }

    public function extractKataPengantar(
        string $text
    ) {
        if (
            preg_match(
                '/KATA PENGANTAR(.*?)DAFTAR ISI/s',
                $text,
                $matches
            )
        ) {
            return trim(
                $matches[1]
            );
        }

        return null;
    }

    public function extractDaftarIsi(
        string $text
    ) {
        if (
            preg_match(
                '/DAFTAR ISI(.*?)BAB I/s',
                $text,
                $matches
            )
        ) {
            return trim(
                $matches[1]
            );
        }

        return null;
    }

    public function auditKataPengantar(
        string $kataPengantar
    ) {
        $errors = [];

        $forbidden = [

            'kritik dan saran',

            'banyak kekurangan',

            'mohon masukan'

        ];

        foreach (
            $forbidden as $word
        ) {

            if (
                str_contains(
                    strtolower(
                        $kataPengantar
                    ),
                    $word
                )
            ) {

                $errors[] =
                    $word;
            }
        }

        return $errors;
    }
}